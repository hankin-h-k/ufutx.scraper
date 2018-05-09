<?php namespace App\Utils;

use App\Utils\Str;
use App\Utils\IP;
use App\Utils\Common;

class Messenger
{

    /* 
     * 发送邮件
     */
	static public function sendMail($email, $subject, $message, $type='invite')
	{
		\Log::info("Mail result: To($email)- Subject($subject) - Body($message)");
		if (\Config::get('messenger.debug')){
			//$email = \Auth::user()->email;
			$email = \Config::get('messenger.debug_email');
		}

		$disable = !\Config::get('messenger.enable');
		if ($disable) {
			return;
		}

		$url = 'http://sendcloud.sohu.com/webapi/mail.send.json';
		//不同于登录SendCloud站点的帐号，您需要登录后台创建发信子帐号，使用子帐号和密码才可以进行邮件的发送。
		$param = array('api_user' => \Config::get('messenger.mailer.api_user_'.$type, 'ufutx_invite'),
			'api_key' => \Config::get('messenger.mailer.api_key'),
			'from' => \Config::get('messenger.mailer.from', 'info@ufutx.com'),
			'fromname' => \Config::get('messenger.mailer.fromname', '友福同享'),
			'to' => $email,
			'subject' => $subject,
			'html' => $message);

		$query = http_build_query($param);
		$options = array('http' => array(
			'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
				"Content-Length: " . strlen($query) . "\r\n" .
				"User-Agent:Ufutx/1.0\r\n",
			'method' => 'POST', 'content' => $query));
		if(\Config::get('messenger.proxy_enable')){
		    $options['http']['proxy'] = 'tcp://'.\Config::get('messenger.proxy_host').':'.\Config::get('messenger.proxy_port');
			$options['http']['request_fulluri'] = true;
        }
		$context = stream_context_create($options);
		$return = file_get_contents($url, false, $context);
		
		$result = json_decode($return);
		
		if($result->message == 'success'){
			return true;
		}else{
			\Log::error('Mailer Failed:'.$return);
			return false;
		}
	}

    /*
     * 发送短信
     * opt["yunzhixun"]=1,则$message="陈彬,曾荣耀"("接收人，发送人"/"接收人")，云之讯的消息内容，这里不体现。所以message就当作接收人和发送人来用。
     */
	static public function sendSMS($mobile, $message, $opt = [])
	{	
		\Log::info('Send Message To: (' . $mobile . ')' . $message);
		if (\Config::get('messenger.debug'))
			$mobile = \Config::get('messenger.debug_mobile');

		$disable = !\Config::get('messenger.enable');
		if ($disable) {
			return ['code'=>true, 
				'result'=>'disable'];
		}

		$type = Str::isMobile($mobile);


		if ($type == 2) {
			$mobile = '852' . $mobile;
		} elseif ($type == 4) {
			$mobile = '886' . $mobile;
		} elseif ($type == 3) {
			$mobile = '853' . $mobile;
		}
        
        // 默认都用 luosimao 发送，除非设定 luosimao=>0
        $luosimao = !(isset($opt['luosimao']) && $opt['luosimao'] === 0);

		if ($type == 1){
			$config = \Config::get('messenger.mainland');
            $url = "http://utf8.sms.webchinese.cn/?smsMob=$mobile&smsText=".urlencode($message);
		}elseif ($type > 1){
			$config = \Config::get('messenger.hongkong');

			$url = "http://api.accessyou.com/sms/sendsms-utf8.php?phone=$mobile&msg=".urlencode($message);
		}else{
			\Log::error('Unavailable Phone number');
			return ['code'=>false, 
				'result'=>'invalid phone'];
		}

		foreach ($config as $key => $value) {
			$url .= '&'.$key.'='.urlencode($value);
		}


        // 大陆

        if($type == 1){ 
            if(config('messenger.mainland_vender') == 'gausstel'){ 
                $gausstel = config('messenger.gausstel');
                $gausstel['to'] =  $mobile;
                //自动添加签名。
                $gausstel['content'] = iconv('UTF-8', 'GBK', $message.config('messenger.signature')) ;
                $url = 'http://gateway.iems.net.cn/GsmsHttp?'.http_build_query($gausstel);
                $ch = curl_init();
                $timeout = 5;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		        if(\Config::get('messenger.proxy_enable')){
                    curl_setopt($ch, CURLOPT_PROXY, 'http://'.\Config::get('messenger.proxy_host'));
                    curl_setopt($ch, CURLOPT_PROXYPORT, \Config::get('messenger.proxy_port')); 
                }

                $file_contents = curl_exec($ch);
                curl_close($ch);
            }else{ //默认使用luosimao
                $message .= config('messenger.signature');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://sms-api.luosimao.com/v1/send.json");
                
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                
                curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD  , 'api:key-'.config('messenger.luosimao.key'));
                
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobile,'message' => $message));
		        if(\Config::get('messenger.proxy_enable')){
                    curl_setopt($ch, CURLOPT_PROXY, 'http://'.\Config::get('messenger.proxy_host'));
                    curl_setopt($ch, CURLOPT_PROXYPORT, \Config::get('messenger.proxy_port')); 
                }
                
                $res = curl_exec( $ch );
                curl_close( $ch );
                $result = json_decode($res);
                if($result->error == 0){
					return ['code'=>true, 
						'result'=>$result];
                }else{
                    \Log::info('SMS Failed: '.$result->msg);
					return ['code'=>false, 
						'result'=>$result];
                }
            }
        }else{//未指定根据手机区域
            if (function_exists('file_get_contents')) {
				if(\Config::get('messenger.proxy_enable')){
					$opt= array(
						'http' => array(
							'proxy' => 'tcp://'.\Config::get('messenger.proxy_host').':'.\Config::get('messenger.proxy_port'),
							'request_fulluri' => true,
						)
					);
					$context = stream_context_create($opt);
                    $file_contents = file_get_contents($url, false, $context);
				}else{
                	$file_contents = file_get_contents($url);
				}
            } else {
                $ch = curl_init();
                $timeout = 5;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		        if(\Config::get('messenger.proxy_enable')){
                    curl_setopt($ch, CURLOPT_PROXY, 'http://'.\Config::get('messenger.proxy_host'));
                    curl_setopt($ch, CURLOPT_PROXYPORT, \Config::get('messenger.proxy_port')); 
                }

                $file_contents = curl_exec($ch);
                curl_close($ch);
            }
            if (intval($file_contents) > 0){
				return ['code'=>true, 
					'result'=>$file_contents];
            }else{
                \Log::info('SMS Failed: '.$file_contents);
				return ['code'=>false, 
					'result'=>$file_contents];
            }
        }

	}

}

