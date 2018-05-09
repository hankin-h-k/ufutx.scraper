<?php namespace App\Utils;

class Encrypt
{
	/* 
		核心，加密解密用的key，加密和解密时的key必须相同
		如果通过其他方法使key为动态可提高破解复杂度 
	*/
	static private $key 		= null;
	static private $keyLen 	= 0;
	/* 
		亦或次数，亦或由于是位运算，故速度非常快，且不会增加数据长度
		增加此数值以提高复杂度，但同时需要更多的性能开销
	*/
	static private $xorCount 	= 2;
	static private $errno = 0;
	static private $errmsg = '';
	

	/* 加密 */
	static public function encrypt($content)
	{
		$content = (string)$content;
		self::$key = env('UU_KEY', 'V2V0Q9xcS7Uebem6s48d8fN4Afj331pbz2Y9J695q8h3k2k4bfk1n08cu8Sb44W5'); 
		self::$keyLen = strlen(self::$key);
		if(self::iscrypted($content))
			return $content;
		$pcontent = rawurlencode(base64_encode( self::loopXor($content)));
		if($pcontent)
			$pcontent = '^+'.$pcontent;
		return $pcontent;
	}

	/* 是否加密 
	 * return:
	 */
	static public function iscrypted($content)
	{
		if(substr($content, 0, 2) === '^+' && strlen($content)>2){
				return true;
		}
		return false;

	}

	/* 解密 
	 * return:
	 */
	static public function decrypt($content)
	{
		self::$key = env('UU_KEY', 'V2V0Q9xcS7Uebem6s48d8fN4Afj331pbz2Y9J695q8h3k2k4bfk1n08cu8Sb44W5'); 
		self::$keyLen = strlen(self::$key);
		if(substr($content, 0, 2) === '^+'){
			$content = substr($content, 2);
		}else{
			return $content;
		}

		return htmlspecialchars(self::loopXor( base64_decode( rawurldecode($content) ) ));
	}

	static public function checkStatus()
	{
		$return = array('errno'=>self::$errno, 'errmsg'=>self::$errmsg);	
		if(self::$errno){
			self::$errno = 0;
			self::$errmsg = '';
		}
		return $return;
	}

	static private function loopXor($data)
	{
		$len = strlen($data);
		$xc  = self::$xorCount;
		
		do{
			for( $i=0; $i<$len; $i++ ){
				$data[$i] = $data[$i]^self::$key[$i%self::$keyLen];
			}
		}
		while($xc--);
		
		return $data;
	}
}
?>
