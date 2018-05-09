<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
});

Route::any('/wechat', 'WechatController@serve');
Route::get('/cron/{name}', 'CronController@cronTransfer');
Route::post('/cront/{name}', 'CronController@cronTransfer');



Route::get('/auth/sns/ufutx', 'Auth\LoginController@snsLoginUfutx')->name('login.ufutx');
Route::get('/auth/ufutx/callback', 'Auth\LoginController@snsUfutxCallback')->name('ufutx.callback');
Route::get('/auth/sns/github', 'Auth\LoginController@snsLoginGithub')->name('login.github');
Route::get('/auth/github/callback', 'Auth\LoginController@snsGithubCallback')->name('github.callback');

Auth::routes();

Route::group([ 'middleware' => 'auth'], function(){
    Route::get('/home', 'HomeController@index')->name('home');
});
