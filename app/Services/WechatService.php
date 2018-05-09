<?php namespace App\Services;
 
use Log;
use EasyWeChat\Factory;
use App\Models\Wechat;
use App\Repositories\Eloquent\SmsRepository as Sms;

 class WechatService
 {
 
     /*
      * 构造函数
      */
     protected $app;
     protected $sms;
     public function __construct(Sms $sms)
     {
         $config = [
            'app_id' => config('wechat.mini_program.app_id'),
            'secret' => config('wechat.mini_program.secret'),

            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            // 'response_type' => 'array',

            // 'log' => [
            //     'level' => 'debug',
            //     'file' => __DIR__.'/wechat.log',
            // ],
        ];

        $app = Factory::miniProgram($config);
        $this->app = $app;
        $this->sms = $sms;
     }
 	
 	public function app(){
 		return $this->app;
 	}

    /**
     * 发送模板消息
     */
    public function send($openid, $template_id, $page, $form_id,$data=[]){
        Log::info($data);
        $this->app->template_message->send([
            'touser' => $openid,
            'template_id' => $template_id,
            'page' => $page,
            'form_id' => $form_id,
            'data' =>$data,
        ]);
    }

    /**
     * 用户订阅图书成功(通知管理员)
     */
    public function subscibedSuccess($param=[])
    {
        $template_id = config('wechat.tpls.subscribe_success');
        $page = 'pages/library/borrows?library_id='.$param['library_id'];
        $openid = $param['openid'];
        $form_id = $param['form_id'];
        $data = [
            'keyword1'=>$param['nickname'],
            'keyword2'=>$param['reserve_time'],
            'keyword3'=>$param['book_name'],
        ];
        return $this->send($param['openid'], $template_id, $page, $form_id, $data);
    }

    /**
     * 错误信息
     */
    public function errorMessage($param=[])
    {
        $template_id = config('wechat.tpls.error_message');
        $page = '';
        $openid = $param['openid'];
        $form_id = $param['form_id'];
        $data = [
            'keyword1'=>$param['message'],
            'keyword2'=>$param['error_time'],
        ];
        return $this->send($param['openid'], $template_id, $page, $form_id, $data);
    }
 }