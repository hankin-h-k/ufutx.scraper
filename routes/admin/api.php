<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/**
 * 短信接口
 */
Route::group(['prefix' => 'sms'], function () {
	//通用验证码
    Route::group([ 'middleware' => 'auth:api'], function(){
	    Route::post('/code', 'SmsController@sendGeneralCode');
    });
	//重置密码验证码
	Route::post('/reset', 'SmsController@sendResetCode');
	//注册验证码
	Route::post('/register', 'SmsController@sendRegisterCode');

});


//获取上传签名
Route::get('/upload/signature', 'UploadController@aliyunSignature');

/*
 * 友福图书馆首页
 */
Route::get('/recommend/books', 'HomeController@bookRecommend');


//登录 & 注册
Route::post('/users', 'Auth\RegisterController@store');
Route::post('/login','Auth\LoginController@login');
Route::post('/login/wechat','Auth\LoginController@loginWechat');
Route::post('/wechat/register','Auth\LoginController@wechatRegister');


Route::middleware('auth:api')->group(function () {

    //登录 & 注册
    Route::post('/logout','Auth\LoginController@logout');

    /*
     * 图书分类
     */
    Route::get('/sorts', 'SortController@list');
    Route::get('/sorts/{id}', 'SortController@item');
    Route::post('/sorts', 'SortController@store');
    Route::put('/sorts/{id}', 'SortController@update');
    Route::delete('/sorts/{id}', 'SortController@destory');


    /*
    * 图书馆管理
    */
    Route::get('/libraries', 'LibraryController@list');
    Route::get('/libraries/{id}', 'LibraryController@item');
    Route::put('/libraries/{id}', 'LibraryController@update');
    Route::delete('/libraries/{id}', 'LibraryController@destory');
    Route::post('/libraries', 'LibraryController@store');

    /*
     * 图书管理
     */
    Route::get('/books/{id}', 'BookController@item');
    Route::get('/books/{id}/comments', 'BookController@comments');
    Route::post('/books/{id}/comments', 'BookController@commentStore');
    Route::get('/books/{id}/comments/{comment_id}', 'BookController@comment');
    Route::delete('/books/{id}/comments/{comments_id}', 'BookController@commentDestory');
    //todo:老接口,待更新
    Route::get('/books', 'HomeController@books');
    Route::post('/books', 'HomeController@bookStore');
    Route::put('/books/{id}', 'HomeController@bookUpdate');
    Route::delete('/books/{id}', 'HomeController@bookDestory');
    

    /*
     * 图书馆会员管理 
     */
    Route::post('/libraries/{id}/join', 'MemberController@join');
    Route::get('/libraries/{id}/members', 'MemberController@members');
    Route::get('/libraries/{id}/members/{member_id}', 'MemberController@member');
    Route::put('/libraries/{id}/members/{member_id}/{status}', 'MemberController@memberUpdate');

    /*
     * 图书借阅
     */
    Route::get('/libraries/{id}/borrows', 'BorrowController@borrows');
    //预约
    Route::post('/libraries/{library_id}/books/{id}/reserve', 'BorrowController@reserve');
    //借阅:todo:  直接借，用不上
    Route::post('/libraries/{library_id}/books/{id}/borrow', 'BorrowController@borrow');
    //归还
    Route::put('/libraries/{library_id}/borrows/{id}/borrow', 'BorrowController@bookBorrow');
    Route::put('/libraries/{library_id}/borrows/{id}/return', 'BorrowController@bookReturn');
    Route::put('/libraries/{library_id}/borrows/{id}/renew', 'BorrowController@bookRenew');
    Route::get('/libraries/{library_id}/borrows/{id}', 'BorrowController@borrowItem');
    Route::get('/libraries/{library_id}/borrow/isbn/{isbn}', 'BorrowController@borrowIsbn');

    /*
     * 我的
     */
    Route::get('/user', 'UserController@user');
    Route::get('/user/borrows', 'UserController@borrows');
});

