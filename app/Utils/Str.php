<?php
namespace App\Utils;

class Str
{
	/**
	 * @param $number
	 * @return 
	 * 	0: not support moible;
	 * 	1: chinese mailland mobile
	 * 	2: Hongkong mobile
	 *  3: Macau mobile
	 *  4: Taiwan mobile
	 */
	public static function isMobile($mobile)
	{
		if(strlen($mobile) < 7)
			return 0;
		if(preg_match("/^1[3-578]\d{9}$/", $mobile))
			return 1;
		elseif(preg_match("/^[6|9]\d{7}$/", $mobile))
			return 2;
		elseif(preg_match("/^[6]([8|6])\d{5}$/", $mobile))
			return 3;
		elseif(preg_match("/^[9]\d{8}$/", $mobile))
			return 4;
		else
			return 0;
	}

	/**
	 * @param $number
	 * @return bool
	 */
	public static function isEmail($email)
	{
		return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);

	}

	/**
	 * 检测QQ号
	 */
	public static function isQQ($qq){
		return preg_match("/^[1-9]\d{4,10}$/",$qq) ;
	}

	/**
	 * 是否为中文名字
	 * @param $name
	 * @return int
	 */
	public static function isChineseName($name)
	{
		return preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,15}$/', $name);	
	}

	/***
	 * 产生随机数
	 * @param $length
	 * @param int $numeric
	 * @return string
	 */
	public static function random($length, $numeric = 0)
	{
		PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
		$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
		$hash = '';
		$max = strlen($seed) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $seed[mt_rand(0, $max)];
		}
		return $hash;
	}

	public static function bbcode($message)
	{
		$__face_title = array('大笑','难过','示爱','闭嘴','傻了','得意','眨眼','呲牙','大哭','古板','发呆','微笑','调皮','女孩','困惑','发怒','酷','嘘','想到了','差劲','听音乐',
					'天使','出虚汗','打瞌睡','睡','松了口气','哈欠','发烧了','吐','快哭了','宴会','熟睡','拥抱','打瞌睡2','哈欠2','疑问','晕','流口水','再见','吓','抽烟','流汗','下雨了','呆滞',
					'怪物','发抖','魔鬼','小丑','狗','猫','爱心','心碎','吻','奖杯','炸弹','蛋糕','波动','太阳','月亮','星星','礼物','电话中','玫瑰','便便','饮料','气球','闹钟','邮件','彩虹',
					'幽灵','忍者','猪头');
		foreach($__face_title as $key=>$value){
			if(file_exists(public_path().'/image/face2/'.($key+1).'.png')){
				$face_index[$key] = '<img title='.$__face_title[$key].' width="25px" src="/image/face2/'.($key+1).'.png" />';
				$__face_title[$key] = '['.$__face_title[$key].']';
			}
		}
    
		if(isset($face_index))
			$message = str_replace($__face_title, $face_index, $message);
    
		return (str_replace(array("\n", "\t", '   ', '  '), array('<br />', '&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
	}

	/**
	 * 检查字符串格式为JSON
	 * @param $input
	 * @return bool
	 */
	public static function isJson($input)
	{
		json_decode($input);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	/**
	 * 格式化数字,如： 1024->1k
	 * @param $size
	 * @return string
	 *todo:  Using fileSize
	 function formatsize($size)
	 {
	 $prec=3;
	 $size = round(abs($size));
	 $units = array(0=>" B ", 1=>" KB", 2=>" MB", 3=>" GB", 4=>" TB");
	 if ($size==0) return str_repeat(" ", $prec)."0$units[0]";
	 $unit = min(4, floor(log($size)/log(2)/10));
	 $size = $size * pow(2, -10*$unit);
	 $digi = $prec - 1 - floor(log($size)/log(10));
	 $size = round($size * pow(10, $digi)) * pow(10, -$digi);
	 return $size.$units[$unit];
	}*/


	//获取字符串
	static public function getstr($string, $length, $in_slashes=0, $out_slashes=0, $censor=0, $bbcode=0, $html=0) {
		return self::strLen($string, $length);
	}

	//取消HTML代码
	static public function shtmlspecialchars($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = Str::shtmlspecialchars($val);
			}
		} else {
			$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
				str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
		}
		return $string;
	}

	//去掉slassh
	static public function sstripslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = Str::sstripslashes($val);
			}
		} else {
			$string = stripslashes($string);
		}
		return $string;
	}

	//SQL ADDSLASHES
	static public function saddslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = Str::saddslashes($val);
			}
		} else {
			$string = addslashes($string);
		}
		return $string;
	}


	//连接字符
	static public  function simplode($ids) {
		return "'".implode("','", $ids)."'";
	}


	static public  function fileSize($size)
	{
		$size = max(0, (int)$size);
		$units = array( 'b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
		$power = $size > 0 ? floor(log($size, 1024)) : 0;
		return number_format($size / pow(1024, $power), 2, '.', ',') . $units[$power];
	}

	static  public function strlenSpace($str){
		return (strlen($str) + mb_strlen($str,'UTF8')) / 2;
	}

	static public function strLen($str, $length, $ellipsis = true)
	{
		if($ellipsis)
			$end = '...';
		else
			$end = null;
		if(mb_strwidth($str, "utf8") > $length) {
			$str = mb_strimwidth($str, 0, $length, $end, "utf8");
		}
		return $str;
	}

	static public function  utf8Encode($data)
	{
            if(is_array($data)){
		foreach($data as &$item){
			if(is_array($item)){
				$item = self::utf8Encode($item);
			}else{
				if($item && !json_encode($item))
					$item = utf8_encode($item);
			}
		}
            }else{
                $data = utf8_encode($item);
            }
		return $data;
	}
	
	//传说截取字符串是等长的，但是发现结果并不是那样的。
	static function spaceSubStr($str,$len,$ellipsis = true,$encode='utf8'){
		if($encode!='utf8'){
			$str = mb_convert_encoding($str,'utf8',$encode);
		}
		$osLen = mb_strlen($str);
		if($osLen<=$len){
			return $str;
		}
		$string = mb_substr($str,0,$len,'utf8');
		$sLen = mb_strlen($string,'utf8');
		$bLen = strlen($string);
		$sCharCount = (3*$sLen-$bLen)/2;
		if($osLen<=$sCharCount+$len){
			$arr = preg_split('/(?<!^)(?!$)/u',mb_substr($str,$len+1,$osLen,'utf8'));//将中英混合字符串分割成数组（UTF8下有效）
		}else {
			$arr = preg_split('/(?<!^)(?!$)/u',mb_substr($str,$len+1,$sCharCount,'utf8'));
		}
		foreach($arr as $value){
			if(ord($value)<128 && ord($value)>0){
				$sCharCount = $sCharCount-1;
			}else {
				$sCharCount = $sCharCount-2;
			}
			if($sCharCount<=0){
				break;
			}
			$string.=$value;
		}
		return $string;
	}


	static function addLink($text) {
		$pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
		return preg_replace_callback($pattern, 'self::auto_link_text_callback', $text);
	}

	private static function auto_link_text_callback($matches) {
		$max_url_length = 50;
		$max_depth_if_over_length = 2;
		$ellipsis = '&hellip;';

		$url_full = $matches[0];
		$url_short = '';

		if (false && strlen($url_full) > $max_url_length) {
			$parts = parse_url($url_full);
			$url_short = $parts['scheme'] . '://' . preg_replace('/^www\./', '', $parts['host']) . '/';

			$path_components = explode('/', trim($parts['path'], '/'));
			foreach ($path_components as $dir) {
				$url_string_components[] = $dir . '/';
			}

			if (!empty($parts['query'])) {
				$url_string_components[] = '?' . $parts['query'];
			}

			if (!empty($parts['fragment'])) {
				$url_string_components[] = '#' . $parts['fragment'];
			}
			for ($k = 0; $k < count($url_string_components); $k++) {
				$curr_component = $url_string_components[$k];
				if ($k >= $max_depth_if_over_length || strlen($url_short) + strlen($curr_component) > $max_url_length) {
					if ($k == 0 && strlen($url_short) < $max_url_length) {
						// Always show a portion of first directory
						$url_short .= substr($curr_component, 0, $max_url_length - strlen($url_short));
					}
					$url_short .= $ellipsis;
					break;
				}
				$url_short .= $curr_component;
			}

		} else {
			//$urls = parse_url($url_full);
			$url_encode = $url_full;//$urls['scheme'].$urls['host'].$urls['path'].'?'.urlencode($urls['query']);
			if(strpos($url_full, 'http') === false)
				$url_full = 'http://'.$url_full;
		}

		return "<a rel=\"nofollow\" href=\"$url_full\">$url_encode</a>";
	}

	/*
	 * 二维数组转换一维数组
	 * $data--二维数组
	 * $key--数组中的某个值
	 */
	static function array_column($data,$key){
		$result=array();
		foreach($data as $val)
		{
			if(is_array($key))
			{
				foreach($key as $keyval)
				{
					$res[$keyval]=$val[$keyval];
				}
				$result[]=$res;
			}else
				$result[]=$val[$key];
		}
		return $result;
	}
        

	/**
	 * 根据某个key  去除重复值
	 * @param $arr
	 * @param $key
	 * @return mixed
	 * author Fox
	 */
	static function assoc_unique($arr, $key)
	{
		$tmp_arr = array();
		foreach($arr as $k => $v)
		{
			if(in_array($v[$key], $tmp_arr))//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
			{
				unset($arr[$k]);
			}
			else {
				$tmp_arr[] = $v[$key];
			}
		}
		sort($arr); //sort函数对数组进行排序
		return $arr;
	}

	/**
	 *
	 * @param $message
	 * @param $count
	 * author Fox
	 */
	static function Change_message($message,$count){
		$message = preg_replace("/&#?[a-z0-9]+;/i", '', $message);
		if($count){
			$message = self::shtmlspecialchars(strip_tags($message));
		}else{
			$message = self::shtmlspecialchars(strip_tags($message));
		}
		return $message;
	}
    /* 类似于 lang::get 的简易模板 */
    static function template($str,$replace){
        foreach ($replace as $key => $value){
			$str = str_replace(':'.$key, $value, $str);
		}
        return $str;
    }
    
    /**
     * 给指定的二维数组添加元素数组的指定元素值为 二维数组的 Key 值
     * @param type $arr
     * @param type $key
     * @return type
     */
    static function array_add_key($arr, $key)
    {
        $new_arr = array();
        foreach($arr as $element)
        {
            $new_arr[$element[$key]] = $element;
        }
        return $new_arr;
    }
} 
