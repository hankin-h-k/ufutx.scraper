<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Member;
use App\Models\Borrow;
use App\Models\Book;
use App\Models\Sort;
use App\Models\Library;
use App\Models\LibraryBook;

use Illuminate\Http\Request;

class LibraryController extends Controller
{
    protected $library_id = 1;

    //图书馆
    public function list(Request $request){
        $libraries = Library::withCount('libraryBooks', 'sorts', 'members', 'borrows');
        $keyword = $request->has('keyword');
        if ($keyword) {
            $keyword = trim($request->keyword);
            $libraries = $libraries->where('name','like', '%'.$keyword.'%');
        }
        $libraries = $libraries->paginate(18);
        foreach($libraries as &$library){
            $library->images = Book::whereIn('id', LibraryBook::where('library_id', $library->id)->pluck('book_id'))->limit(9)->pluck('image');
        }
        return $this->success('libraries', $libraries);
    }

    //图书馆
    public function item($id){
        $library = Library::withCount('libraryBooks', 'sorts', 'members', 'borrows')->with('sorts')->findorFail($id);
        $library->join_member_num = Member::where(['library_id'=>$id, 'status'=>'JOIN'])->count();
        $library->reserve_book_num = Borrow::where(['library_id'=>$id, 'status'=>'RESERVE'])->count();
        $library->books_num = LibraryBook::where('library_id', $id)->sum('num');
        $library->member = Member::where(['library_id'=>$id, 'user_id'=>auth()->id()])->first();
        $user = User::where('id', $library->user_id)->select('name', 'mobile')->first();
        $link_name = '';
        $link_mobile = '';
        if (!empty($user)) {
            $link_name = $user->name;
            $link_mobile = $user->mobile;
        }
        $library->link_mobile = $link_mobile;
        $library->link_name = $link_name;

        $library->library_sorts = $this->getLibrarySorts($id);
        $is_admin = $this->isAdmin($id);
        $library->is_admin = $is_admin;
        return $this->success('library', $library);
    }


    //更新图书馆
    public function update(Request $request, $id){
        $library = Library::find($id);

        if($request->name && $request->name != $library->name){
            $library->name = $request->name;
        }

        if($request->logo && $request->logo != $library->logo){
            $library->logo = $request->logo;
        }

        if($request->type && $request->type != $library->type){
            $library->type = $request->type;
        }


        if($request->intro && $request->intro != $library->intro) {
            $library->intro = $request->intro;
        }

        if ($request->count && $request->count != $library->count) {
            $library->count = $request->count;
        }

        if ($request->has('location_longitude') && $request->location_longitude != $library->location_longitude) {
            $library->location_longitude  = $request->location_longitude;
        }

        if ($request->has('location_latitude') && $request->location_latitude != $library->location_latitude) {
            $library->location_latitude = $request->location_latitude;
        }

        // if(Library::where('name', $library->name)->where('id', '<>', $library->id)->count()){
        //     return $this->failure('图书馆名称已经存在');
        // }

        $library->save();


        return $this->success('library update', $library);
    }

    //创建图书馆
    public function store(Request $request){
        $user_id = auth()->id();

        if(!$request->name){
            return $this->failure('名称不能为空');
        }
        $count = $request->input('count', 3);
        $library = new Library;
        $library->name = $request->name;
        $library->user_id = $user_id;
        $library->count = $count;

        if($request->type){
            $library->type = $request->input('type');
        }
        if($request->intro){
            $library->intro = $request->intro ;
        }
        if($request->logo){
            $library->logo = $request->logo;
        }

        if(Library::where('name', $library->name)->count()){
            return $this->failure('图书馆名称已经存在');
        }
        
        $library->save();
        $member = new Member;
        $member->user_id = $user_id;
        $member->library_id = $library->id;
        $member->group_id = 0;
        $member->status = 'ADMIN';
        $member->save();

        return $this->success('library store', $library);
    }

    //删除图书馆
    public function destory($id){
        $library = Library::find($id);
        if($library){
            //删除图书馆关联的图书
            LibraryBook::where(['library_id'=>$id])->delete();
            //删除图书馆成员
            Member::where(['library_id'=>$id])->delete();
            //删除图书馆分类
            Sort::where(['library_id'=>$id])->delete();
            //删除图书馆借阅信息
            Borrow::where(['library_id'=>$id])->delete();
            $library->delete();
            return $this->success('library');
        }else{
            return $this->failure('删除失败');
        }

    }


    /*
     * 管理员检查
     */
    public function checkAdmin($library_id = 0){
        //absolete: for default library_id=1
        if(!$library_id){
            $library_id = $this->library_id;
        }
        $id = auth()->id();
        $library = Library::findOrFail($library_id);
        if($library->user_id == $id){
            return;
        }

        if(Member::where(['library_id'=>$library->id, 'user_id'=>$id, 'status'=>'ADMIN'])->count()){
            return; 
        }

        return $this->failure('你不是管理员，不能进行下面的操作'); 
    }

    /**
     * 是否是管理员
     */
    public function isAdmin($library_id=0){
        if(!$library_id){
            $library_id = $this->library_id;
        }
        $user_id = auth()->id();
        $count = Member::where(['library_id'=>$library_id, 'user_id'=>$user_id, 'status'=>'ADMIN'])->count();
        $is_admin = $count?1:0;
        return $is_admin;
    }

    // init old library's books sort_id
    public function initLibrarySort(){
        $book_ids = [];
        $lbooks = LibraryBook::where('library_id', 1)->get();
        foreach($lbooks as $item){
            $element = LibraryBook::find($item->id);
            $class_id = Book::where('id', $item->book_id)->value('class_id');

            if(!$element->sort_id && $class_id){
                $element->sort_id = class_id;;
                $book_ids[] = $item->book_id;
                $element->save();
            }

        }

        return $this->success('init_books', $book_ids);

    }



    // 获取我的个人藏书library
    public function getMyLibrary($user_id=0) {
        if($user_id){
            $user = User::find($user_id);
        }else{
            $user = auth()->user();
        }
        $library = Library::where(['user_id'=>$user->id, 'type'=>'FAMILY'])/*->withCount('libraryBooks', 'sorts')*/->first(); 
        if(!$library){
            $library = new Library;
            $library->name = $user->name.'的书房';
            $library->intro = $user->name.'的藏书';
            $library->user_id = $user->id;
            $library->type = 'FAMILY';
            if (!empty($user->wechat->avatar)) {
                $library->logo = $user->wechat->avatar;
            }
            $library->save();

            $member = new Member;
            $member->user_id = $user->id;
            $member->library_id = $library->id;
            $member->group_id = 0;
            $member->status = 'ADMIN';
            $member->save();
        }
        return $library;
    }

    // 获取图书馆藏书分类信息
    public function getLibrarySorts($id){
        $library = Library::find($id);
        $books_count = LibraryBook::where('library_id', $id)->count();
        $titles = Book::whereIn('id', LibraryBook::where('library_id', $id)->pluck('book_id'))->limit(10)->pluck('title');
        $image = Book::where('id', LibraryBook::where('library_id', $id)->value('book_id'))->value('image');
        $library_sorts[] = [
                'id' => 0,
                'name' => '总藏书',
                'count' => $books_count,
                'book_titls' => $titles,
                'image' => $image 
            ];
        $sorts = Sort::where('library_id', $id)->get();
        foreach($sorts as $sort){
            $book_ids = LibraryBook::where(['library_id'=>$id, 'sort_id'=>$sort->id])->pluck('book_id');
            $titles = Book::whereIn('id', $book_ids)->limit(10)->pluck('title');
            $image = Book::where('id', LibraryBook::where(['library_id'=>$id, 'sort_id'=>$sort->id])->value('book_id'))->value('image');
            $library_sorts[] = [
                'id' => $sort->id,
                'name' => $sort->name,
                'count' => count($book_ids),
                'book_titls' => $titles,
                'image' => $image 
            ];
        }
        return $library_sorts;

    }
}
