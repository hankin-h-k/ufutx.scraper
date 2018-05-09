<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Member;
use App\Models\Library;
use App\Models\LibraryBook;

use CronService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\SmsRepository as Sms;

class MemberController extends LibraryController
{
    protected $sms;
    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    //加入图书馆
    public function join($id){
        $user = auth()->user();
        $user_id = $user->id;
        $library = Library::find($id);
        $member = Member::where(['user_id'=>$user_id, 'library_id'=>$id])->first();
        if(!$member){
            $member =  new Member;
            $member->user_id = $user_id;
            $member->library_id = $library->id;
            $member->group_id = 0;
            $member->save();
            $admins = Member::with('user', 'library')->where('library_id', $id)->where('status', 'ADMIN')->get();
            foreach($admins as $admin){
                $message =  $admin->user->name.'你好，'.$user->name.'申请加入'.$admin->library->name.'，请登录友福图书馆小程序处理';
                CronService::sentMessage($admin->user->mobile, $message);
            }
        }
        return $this->success('library', $member);
    }

    //图书馆成员列表
    public function members(Request $request, $id){
        // if($rs = $this->checkAdmin($id)){
        //     return $rs;
        // }

        $library = Library::findOrFail($id);
        $is_admin = $this->isAdmin($id);
        $library->is_admin = $is_admin;
        $query = Member::with('user.wechat')->where('library_id', $id);
        if($request->status){
            $query = $query->where('status', $request->status);
        }

        $members = $query->orderBy(\DB::raw('status="JOIN"'), 'DESC')->get();
        return $this->success('library members', compact('members', 'library'));
    }

    //图书馆成员
    public function member($id, $member_id){
        $member = Member::with('user', 'library')->where('library_id', $id)->find($member_id);
        if($member){
            return $this->success('library member', $member);
        }else{
            return $this->failure('图书馆成员不存在!');
        }
    }

    //图书成员转正
    public function memberUpdate($id, $member_id, $status){
        if($rs = $this->checkAdmin($id)){
            return $rs;
        }
        $member = Member::with('user', 'library')->where('library_id', $id)->find($member_id);
        switch($status){
        case 'reject':
            //通知用户
            $mobile = $member->user->mobile;
            $library_name = $member->library->name;
            $message = '您好！您申请加入'.$library_name.'已被拒绝';
            $this->sms->sentMessage($mobile, $message);
            $member->delete();
            break;
        case 'member':
        case 'admin':
            if ($member->user_id === $member->library->user_id) {
                return $this->failure('该操作不允许');
            }
            $member->status = strtoupper($status);
            $member->save();
            break;
        default:
            return $this->failure('不支持的图书馆成员操作');
            break;
        }

        return $this->success('ok',$member);
    }

}
