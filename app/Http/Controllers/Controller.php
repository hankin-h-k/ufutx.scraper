<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\FormId;
use Carbon;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	//接口返回成功
	public function success($msg, $data=[], $cookie = null, $jsonp = false){
		$result = [
			'code'=> 0,
			'message'=> $msg,
			'data'=> $data,
		];
        if($jsonp){
		    return Response()->jsonp('callback', $result);
        }else{
            $response = Response()->json($result);
            if($cookie){
                $response = $response->cookie($cookie);
            }
		    return $response;
        }
	}

	//接口返回失败
	public function failure($msg, $data=[], $jsonp=false){
		$result = [
			'code'=> 1,
			'message'=> $msg,
			'data'=> $data,
		];
        if($jsonp){
		    return Response()->jsonp('callback', $result);
        }else{
		    return Response()->json($result);
        }
	}

	/**
	 * 获取对应的formid
	 */
	public function formId($openid)
	{
		$now_time = time();
		$end_time = $now_time - 7 * 24 * 60 * 60;
		$time = date('Y-m-d H:i:s', $end_time);
        $form_id = FormId::where('openid', $openid)->where('status', 0)->where('form_id', '<>','the formId is a mock one')->where('created_at', '>', $time)->orderBy('id', 'asc')->first();
        return $form_id;
	}
}
