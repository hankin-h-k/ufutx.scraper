<?php

namespace App\Repositories\Eloquent;

use App\Models\Message;

use App\Utils\Str;
use App\Utils\Messenger;

/**
 * Sms Repository
 *
 * Class SmsRepository
 * @package App\Repositories\Eloquent
 */
class SmsRepository extends Repository
{



    /**
     * 指定模型名称
     *
     * @return mixed
     */
    function model()
    {
        return 'App\Models\Message';
    }

    /*
     * //生成验证码短信记录并发送短信
     * todo:
     * 1. 检查是否重复发送
     * 2. 检查IP限制?
     * 3. 增加验证码类型
     */
    function sentCode(array $data){
		$data['code'] = Str::random(4, 1);
        list($key, $params)=$data['message'];
        $params['code'] = $data['code'];
        $data['confirmed'] = 0;
        $data['message'] = __('messenger.'.$key, $params);
        $this->model->create($data);

		Messenger::sendSMS($data['phone'], $data['message']);

        return true;
    }

    //老的发送短信接口
    function create(array $data){
        return $this->sentCode($data);
    }

    //发送普通消息
    function sentMessage($mobile, $message){
        $this->model->create([
            'phone'=>$mobile,
            'message'=>$message,
            'confirmed' => 1,
            'ip' => request()?request()->ip():'127.0.0.1',
        ]);
		Messenger::sendSMS($mobile, $message);
        return true;
    }


    /*
     * 检查是否合法
     * 1. 正常返回false
     * 2. 失败返回原因
     *
     * todo:
     * 1. 只有最近, 旧的失效
     */
    function check($mobile, $code){
        if(!$code){
            return '请填写验证码';
        }

        //测试用万能验证码
        if(config('app.testing') && $code=='999999999'){
            Message::where('phone', $mobile)->update(['confirmed'=>1]);
            return false;
        }

        $record = Message::where(['phone'=>$mobile, 'code'=>$code])/*->orderBy('id', 'desc')*/->first();

        if(empty($record)){
            return '验证码有误';
        }

        if($record->created_at->timestamp < (time()-10*60)){
            return '验证码过期';
        }

        if($record->confirmed){
            return '验证码已使用';
        }

        Message::where('id', $record->id)->update(['confirmed'=>1]);

        return false;
        
    }


    public function list($page_num = 15)
    {
        $query = new Message;
        $keyword = request()->input('keyword', null);
        if($keyword){
            $query = $query->where(function ($qr) use($keyword) {
                return $qr->where('phone', 'like', '%'.$keyword.'%')
                    ->orWhere('code', 'like', '%'.$keyword.'%')
                    ->orWhere('message', 'like', '%'.$keyword.'%');
            });
        }

        $start = request()->input('start', null);
        $end = request()->input('end', null);
        if($start && $end){
            $query = $query->whereBetween('created_at', [$start, $end]);
        }


        return $query->paginate($page_num);
    }



}
