<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Eloquent\SmsRepository as Sms;

use Log;
// use EasyWechat;
use App\Utils\Messenger;
use WechatService;
use App\Models\Wechat;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat;
class WechatController extends Controller
{
    protected $sms;
    protected $app;
    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    //
	/**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
		Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        // $mp = app('wechat');
        // $app = app('wechat.official_account');
        $app = app('wechat.mini_program');
        $app->server->push(function($message) use($app){
            $openid = $message['FromUserName'];
            $wechat = Wechat::where('openid', $openid)->with('user')->first();
            if($wechat && $wechat->user){
                $name = $wechat->user->name;
            }else if($wechat ){
                $name = $wechat->nickname;
            }else{
                $name = $openid;
            }
            \CronService::sentMessage('18682191714', $message['Content']);
            $message_v2 = "留言已收到, 请留微信号, 或加微信号:13243797303，继续交流，谢谢！";
            $app->customer_service->message($message_v2)->to($openid)->send();
            return 'overtrue!';
        });
        Log::info('request arrived3.');
        return $app->server->serve();
    }
}
