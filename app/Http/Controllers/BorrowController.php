<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Borrow;
use App\Models\Library;
use App\Models\LibraryBook;
use App\Models\FormId;
use App\Models\Wechat;
use WechatService;

use Carbon\Carbon;
use Illuminate\Http\Request;

class BorrowController extends LibraryController
{

    //图书馆借阅列表
    public function borrows(Request $request, $id){
        // if($rs = $this->checkAdmin($id)){
        //     return $rs;
        // }

        $library = Library::findOrFail($id);
        $is_admin = $this->isAdmin($id);
        $library->is_admin = $is_admin;
        $query = Borrow::with('user.wechat', 'book')->where('library_id', $id)->whereIN('status', ['RESERVE','BORROW', 'RETURN']);
        if($request->status){
            $query = $query->where('status', $request->status);
        }

        $borrows = $query->orderBy(\DB::raw('status="RESERVE"'), 'DESC')->orderBy(\DB::raw('status="RETURN"'), 'ASC')->get(); 
        return $this->success('library borrows', compact('borrows', 'library'));
    }

    //图书预订
    public function reserve($library_id, $id)
    {
        $user = \Auth::user();
        $user_id= $user->id;
        $borrow = Borrow::where(['library_id'=>$library_id, 
            'user_id'=>$user_id, 'book_id'=>$id])->where('status', '<>', 'RETURN')->first();
        if($borrow){
            return $this->failure('图书已经借阅或约借:'.$borrow->status, $borrow);
        }

        $result = $this->checkBorrow($library_id, $id);
        if($result){
            return $this->failure($result);
        }
        $borrow = new Borrow;
        $borrow->user_id = $user_id;
        $borrow->library_id = $library_id;
        $borrow->book_id = $id;
        $borrow->save();
        //通知管理员
        $book_name = Book::where('id', $id)->value('title');
        $name = $user->name;
        $param = [
            'reserve_time'=>$borrow->created_at->toDateString(),
            'book_name'=>$book_name,
            'library_id'=>$library_id,
            'nickname'=>$name,
        ];
        $admin_user_id_arr = Member::where(['library_id'=> $library_id, 'status'=>'ADMIN'])->pluck('user_id');
        foreach ($admin_user_id_arr as $admin_user_id) {
            $admin_wechat = Wechat::where('user_id', $admin_user_id)->select('openid', 'nickname')->first();
            if ($admin_wechat) {
                $param['openid'] = $admin_wechat->openid;
            }else{
                $param['openid'] = '';
            }
            $form_id = $this->formId($param['openid']);
            if(!empty($form_id)){
                $form_id->status = 1;
                $form_id->save();
                $param['form_id'] = $form_id->form_id;
                WechatService::subscibedSuccess($param);
            }
        }
        return $this->success('预约成功', $borrow);
    
    }

    //图书借阅
    public function borrow($library_id, $id)
    {
        $user_id= auth()->id();
        $borrow = Borrow::where(['library_id'=>$library_id, 
            'user_id'=>$user_id, 'book_id'=>$id])->where('status', '<>', 'RETURN')->first();

        //已经借阅
        if($borrow){
            if($borrow->status == 'RESERVE'){ 
                $borrow->status = 'BORROW';
                Borrow::where('id', $borrow->id)->update([
                    'status'=>'BORROW',
                    'return_time' => Carbon::now()->addMonth()
                ]);
                return $this->success('图书借阅成功', $borrow);
            }else{
                return $this->failure('图书已经借阅中', $borrow);
            }
        }

        //新的借阅
        $result = $this->checkBorrow($library_id, $id);
        if($result){
            return $this->failure($result);
        }
        $borrow = new Borrow;
        $borrow->user_id = $user_id;
        $borrow->library_id = $library_id;
        $borrow->book_id = $id;
        $borrow->status = 'BORROW';
        $borrow->return_time = Carbon::now()->addMonth();
        $borrow->save();

        return $this->success('借阅成功', $borrow);
    
    }


    /*
     * 申请图书借阅
     */

    private function checkBorrow($library_id, $book_id){
        $user_id= auth()->id();
        $library = Library::find($library_id);
        $member = Member::where(['library_id'=>$library_id, 'user_id'=>$user_id])->first();
        if(!$member){
            return '你还没有申请加入['.$library->name.']';
        }

        if($member->status ==  'JOIN'){
            return '请等待['.$library->name.']管理员审核';
        }

        $borrow_num = Borrow::where(['user_id'=>$user_id, 'library_id'=>$library_id])->whereIn('status',['Borrow', 'RESERVE'])->count();
        if($borrow_num >= $library->count){
            return '您在该图书馆预约与借阅图书已达'.$library->count.'本,请归还部分再借!';
        }

        $library_book =  LibraryBook::where(['library_id'=>$library_id, 'book_id'=>$book_id])->first();
        if(!$library_book){
            return '图书馆不存在此图书';
        }

        if($library_book->lent_num<$library_book->num){
            LibraryBook::where('id', $library_book->id)->increment('lent_num');
            return false;
        }else{
            return '图书馆些书已借完';
        }
    }

    /*
     * 图书借阅
     * 用户已经预约，转借状态为代阅中
     */
    public function bookBorrow($library_id, $id)
    {
        if($rs = $this->checkAdmin($library_id)){
            return $rs;
        }
        $borrow = Borrow::find($id);
        if($borrow->status ==  'RESERVE'){
            $borrow->status = 'BORROW';
            $borrow->return_time = Carbon::now()->addMonth();
        }else{
            return $this->failure('此书状态异常:'.$borrow->status);
        }
        $borrow->save();
        return $this->success('borrow success');
    }

    //图书归还
    public function bookReturn($library_id, $id)
    {
        if($rs = $this->checkAdmin($library_id)){
            return $rs;
        }
        $borrow = Borrow::find($id);
        if($borrow->status ==  'BORROW'){
            LibraryBook::where(['book_id'=>$borrow->book_id, 'library_id'=>$borrow->library_id])->decrement('lent_num');
            $borrow->status = 'RETURN';
        }else{
            return $this->failure('此书状态异常:'.$borrow->status);
        }
        $borrow->save();
        return $this->success('returned');
    }

    //图书继借
    public function bookRenew($library_id, $id)
    {
        $borrow = Borrow::find($id);
        if($borrow->status ==  'BORROW' && !$borrow->renew){
            $borrow->renew = true;
            $borrow->return_time = Carbon::parse($borrow->return_time)->addMonth();
        }else{
            return $this->failure('此书不能继借:'.$borrow->status.$borrow->renew?'已续借':'');
        }
        $borrow->save();
        return $this->success('book renew', $borrow);
    }

    
    public function borrowItem($library_id, $id)
    {
        $borrow = Borrow::with('book.sort', 'library', 'user')->where('library_id', $library_id)->findOrFail($id);
        return $this->success('borrow detail', $borrow);;
    }

    // 获取图书借阅记录
    public function borrowIsbn($library_id, $isbn)
    {
        $book_id = Book::where('isbn', $isbn)->value('id');
        if(!$book_id){
            $this->failure('未录入图书!');
        }
        $borrows = Borrow::where(['book_id'=>$book_id, 'library_id'=>$library_id])->with('book', 'user', 'library')->get();
        if($borrows->count()<1){
            return $this->failure('图书没有借阅记录!');
        }

        return $this->success('borrow list', $borrows);
    }

    //isbn查书
    public function bookIsbn($library_id, $isbn)
    {
        $book_id = Book::where('isbn', $isbn)->value('id');
        if(!$book_id){
            $this->failure('未录入图书!');
        }
        $library = Library::find($library_id);
        if(!$library){
            $this->failure('图书馆不存在!');
        }

        $library_book = LibraryBook::where(['book_id'=>$book_id, 'library_id'=>$library_id])->with('book', 'library')->first();
        if($library_book){
            return $this->success('borrow list', $library_book);
        }else{
            return $this->failure($library->name.'不存在该书('.$isbn.')');
        }
    }

    /**
     * 图书预借删除
     */
    public function deleteBorrow(Request $request, $library_id, $id)
    {
        $borrow = Borrow::where(['library_id'=>$library_id, 'id'=>$id, 'status'=>'RESERVE'])->first();
        if (!empty($borrow)) {
            $borrow->delete();
            LibraryBook::where(['library_id'=>$library_id, 'book_id'=>$borrow->book_id])->decrement('lent_num');
            return $this->success('ok');
        }else{
            return $this->failure('移除失败');
        }
    }

}
