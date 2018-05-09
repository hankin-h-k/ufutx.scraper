<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Book;
use App\Models\Library;
use App\Models\LibraryBook;
use App\Models\Member;
use App\Models\Borrow;

use Illuminate\Http\Request;

class UserController extends LibraryController
{

    /*
     * 用户信息
     * - user 
     * - libraries 
     */
    public function user($user_id=0)
    {
        $libraries = [];
        if(!$user_id){
            $user_id = auth()->id();
        }
        $user = User::with('wechat')->find($user_id);
        $borrows_count = Borrow::where(['user_id'=>$user_id, 'status'=>'BORROW'])->count();
        $user->borrows_count = $borrows_count;
        $mylibrary = $this->getMyLibrary($user->id);
        $mylibrary->library_sorts = $this->getLibrarySorts($mylibrary->id);
        $members = Member::with('library')->where('user_id', $user->id)->whereIn('status', ['ADMIN'])->get(); 
        $is_news = 0;
        foreach($members as $member){
            $join_number_count = Member::where(['library_id'=>$member->library->id, 'status'=>'JOIN'])->count();
            if ($join_number_count) {
                $is_news = 1;
            }
            $member->library->join_number_count = $join_number_count;
            $reserve_book_num = Borrow::where(['library_id'=>$member->library->id, 'status'=>'RESERVE'])->count();
            if ($reserve_book_num) {
                $is_news = 1;
            }
            $member->library->reserve_book_num = $reserve_book_num;
            $libraries[] = $member->library;
        }
        $user->is_news = $is_news;
        $user->followers = $user->followers()->count();
        $user->followings = $user->followings()->count();
        if($user->id != auth()->id()){
            $user->is_following = auth()->user()->isFollowing($user);
        }

        return $this->success('user', compact('user', 'libraries', 'mylibrary')); 
    }

    /**
     * 修改信息
     */
    public function updateUser(Request $request)
    {
        $user_id = auth()->id();
        $user = User::find($user_id);
        if ($request->has('name') && !empty($request->name) && $request->name != $user->name) {
            $user->name = $request->name;
        }
        $user->save();
        return $this->success('ok');
    }

    //toggle follow target user
    public function userFollow($user_id)
    {
        $target = User::find($user_id);
        $user = auth()->user();
        $user->toggleFollow($target);
        $user->is_following = $user->isFollowing($target);
        return $this->success('user toggle follow', $user);
    }

    //user's followings
    public function userFollowers($user_id){
        $user = User::find($user_id);
        $followers = $user->followers()->with('wechat')->paginate();
        $cur_user = auth()->user();
        foreach($followers as &$item){
            $item->is_following = $cur_user->isFollowing($item);
        }
        return $this->success('user followers', $followers);
    }

    //user's followings
    public function userFollowings($user_id){
        $user = User::find($user_id);
        $followings = $user->followings()->with('wechat')->paginate();
        $cur_user = auth()->user();
        foreach($followings as &$item){
            $item->is_following = $cur_user->isFollowing($item);
        }
        return $this->success('user followings', $followings);
    }

    public function borrows(Request $request)
    {
        $status = $request->input('status', 'RESERVE');
        $borrows = Borrow::where('user_id', auth()->id())->with('library', 'book')->where('status', $status)->paginate();

        return $this->success('user_borrow', $borrows); 
    }

    // 我的图书馆
    public function libraries() {
        $libraries = [];
        $members = Member::with('library')->where('user_id', auth()->id())/*->whereIn('status', ['ADMIN', 'MEMBER'])*/->get(); 
        foreach($members as $member){
            $library = Library::withCount('libraryBooks', 'sorts', 'members', 'borrows')->find($member->library->id);
            $library->status = $member->status;
            $library->images = Book::whereIn('id', LibraryBook::where('library_id', $library->id)->pluck('book_id'))->limit(9)->pluck('image');

            $libraries[] = $library;
        }
        return $this->success('user_library', $libraries); 

    }
}
