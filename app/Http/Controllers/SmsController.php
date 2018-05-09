<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Utils\Str;
use App\Utils\Messenger;
use App\Http\Requests;

use App\Repositories\Eloquent\SmsRepository as Sms;

class SmsController extends Controller
{

    /**
     * @var Sms
     */
    protected $sms;

    /**
     * SmsController constructor.
     * @param Sms $sms
     */
    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }


	//发送注册验证码
	public function sendResetCode(Request $request){
		$mobile = $request->input('mobile');

		if(false){
			//todo 是否为友福用户
			return $this->failure('手机号不未注册友福!');
		}

		$result = $this->sendCode($mobile, 'reset');
		return $result;
	}

	/** 发送重置验证码 */
	public function sendRegisterCode(Request $request){
		$mobile = $request->input('mobile');
		if(false){
			//todo 是否已注册友福用户
			return $this->failure('手机号不未注册友福!');
		}

		$result = $this->sendCode($mobile, 'register');
		return $result;
	}


	//发送通用验证码
	public function sendGeneralCode(Request $request){
		$mobile = $request->input('mobile');
		if(empty($mobile)){
            if(!\Auth::user()){
                return $this->failure('未登录');
            }
			$mobile = auth()->user()->mobile;
		}
		$result = $this->sendCode($mobile, 'general');
		return $result;
	}


	/**
	 * do send code
	 * @param  [type] $mobile mobile number
	 * @param  [type] $key    [description]
	 * @param  array  $params [description]
	 * @return [type]         [description]
	 */

	private function sendCode($mobile, $key, $params=[]){
		if(!Str::isMobile($mobile)){
			return $this->failure('手机号无效');
		}

        $this->sms->create([
			'phone' => $mobile,
			'message' => [$key, $params],
			'ip' => request()->ip(),
			'confirmed' => 0
        ]);


		return $this->success('短信已发送');
	}

    /**
     * 所有短信的发送记录
     */
    public function messages()
    {
        
        $list =  $this->sms->list();
        return $this->success('message lists', $list);
    }
}
