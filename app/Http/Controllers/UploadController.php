<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    //
	public function upload(Request $request){ 
		$file = $_FILES['fileData'];
        return $this->uploadFile($file);
    }

    //上传文件　
    public function uploadFile($file){

        //生成新二维码云端全URI
        $object = date('Y').date('m')."/".date('d')."/".$file['name'];
        $file_url = 'http://'.config('alioss.picture_domain').'/'.$object;

        require_once base_path('vendor/aliyuncs/oss-sdk-php').'/autoload.php';

        //连接aliyun oss server
        try {
            $ossClient = new \OSS\OssClient(config('alioss.id'), config('alioss.secret'), config('alioss.host'));
        } catch(\OSS\Core\OssException $e) {
            return $this->failure('oss_connect_failure', $e->getMessage());
        }


        //上传图片到aliyun oss
        try {
            $result = $ossClient->uploadFile(config('alioss.buckets.picture'), $object, $file['tmp_name']);
        } catch(\OSS\Core\OssException $e) {
            return $this->failure('oss_put_failure', $e->getMessage());
        }

        return $this->success('upload_ok', $file_url);

    }

    //获取Web真传签名
    public function aliyunSignature(Request $request){

		$id= config('filesystems.disks.oss.access_id');
		$key= config('filesystems.disks.oss.access_key');
		$host = 'https://'.config('filesystems.disks.oss.bucket').'.'.config('filesystems.disks.oss.endpoint');
		$now = time();
		$expire = 60*30; //设置该policy超时时间是60s. 即这个policy过了这个有效时间，将不能访问
		$end = $now + $expire;
		$expiration = $this->gmt_iso8601($end);

		$dir = date('Y').date('m')."/".date('d')."/";

		//最大文件大小.用户可以自己设置
		$condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
		$conditions[] = $condition;

		//表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
		$start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
		$conditions[] = $start;


		//这里默认设置是２０２０年.注意了,可以根据自己的逻辑,设定expire 时间.达到让前端定时到后面取signature的逻辑
		$arr = array('expiration'=>$expiration,'conditions'=>$conditions);

		$policy = json_encode($arr);
		$base64_policy = base64_encode($policy);
		$string_to_sign = $base64_policy;
		$signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));
        $callback_url = $request->root().'/api/upload';
        $callback_param = array('callbackUrl'=>$callback_url, 
            'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType'=>"application/x-www-form-urlencoded"); 
        $callback_string = json_encode($callback_param);
        $base64_callback_body = base64_encode($callback_string);


		$response = array();
		$response['accessid'] = $id;
		$response['host'] = $host;
		$response['policy'] = $base64_policy;
		$response['signature'] = $signature;
		$response['expire'] = $end;
		//这个参数是设置用户上传指定的前缀
		$response['dir'] = $dir;
		$response['picture_domain'] = config('filesystems.disks.oss.cdnDomain');
        //$response['callback'] = $base64_callback_body;

		return $this->success('aliyun_signature', $response);
    }

    //aliyun get signature using
	private function gmt_iso8601($time) {
		$dtStr = date("c", $time);
		$mydatetime = new \DateTime($dtStr);
		$expiration = $mydatetime->format(\DateTime::ISO8601);
		$pos = strpos($expiration, '+');
		$expiration = substr($expiration, 0, $pos);
		return $expiration."Z";
	}
}
