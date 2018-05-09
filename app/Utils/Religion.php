<?php namespace App\Utils;


/**
 * 基本公共方法
 */

class Religion
{
	/*
	 * 获取省市地区
	 */
	static public function showRegion($provid,$cityid,$strid=-1,$zoneid=-1, $location=''){
		global $__province,$__city,$__district,$__street;
		require_once(__DIR__.'/region.php');

		$residenow=array('province'=>'','city'=>'','street'=>'','zone'=>'');
		if(isset($provid) && isset($__province[$provid])){
			$residenow['province'] = $__province[$provid];

			$residenow['city'] = isset($__city[$provid][$cityid])?$__city[$provid][$cityid]:'';

			$residenow['street'] = isset($__district[$provid.$cityid][$strid])?$__district[$provid.$cityid][$strid]:'';

			$residenow['zone'] = isset($__street[$provid.$cityid.$strid][$zoneid])?$__street[$provid.$cityid.$strid][$zoneid]:'';
		}
		if($location){
			$residenow['location'] = $location;
		}

		return $residenow;
	}

        
	/*
	 * 获取省市地区
	 */
	static public function showRegionStr($provid,$cityid='',$strid=-1,$zoneid=-1){
		global $__province,$__city,$__district,$__street;
		require_once(__DIR__.'/region.php');

		$residenow='';
		if(isset($provid) && isset($__province[$provid])){
			$residenow=$__province[$provid];
			if(isset($__city[$provid][$cityid]))
				$residenow.=''.$__city[$provid][$cityid];
			if(isset($__district[$provid.$cityid][$strid])){
				$residenow.=''.$__district[$provid.$cityid][$strid];
			}
			if(isset($__street[$provid.$cityid.$strid][$zoneid])){
				$residenow.=''.$__street[$provid.$cityid.$strid][$zoneid];
			}
		}
		return $residenow;
	}

	static public function showArea($area, $location =''){
		if(!is_array($area))
			return ;
		foreach($area as $item){
			$areas[] = intval($item);
		}
		//处理特别的深圳的三级地域
		if($areas[0] != 88 && empty($areas[2]))
			$areas[2] = -1;

		return self::showReside($areas[0], $areas[1], $areas[2],$area[3], $location);
	}
        
        /**
         * 根据用户的地址，返回省份及市的下标
         * @global type $__province
         * @global type $__city
         * @param type $region_str
         * @return type
         */
	static public function getRegionKey($region_str)
        {
            global $__province,$__city,$__district;
            require_once(__DIR__.'/region.php');
            if(str_contains($region_str, '省') && str_contains($region_str, '市') && str_contains($region_str, '区') || str_contains($region_str, '县')){
                $region_prov = explode('省', $region_str);
                //dd($region_prov);
                $region_city = explode('市', $region_prov[1]);
                $prov = $region_prov[0];
                $city = $region_city[0];
                $dist = $region_dist[0];

                $detail = substr($region_str, strpos($region_str, '区') + 3);
           		
                $prov_arr = array_flip($__province);
                $prov_key = $prov_arr[$prov.'省'];

                $city_arr = array_flip($__city[$prov_key]);
                $city_key = $city_arr[$city.'市'];
                
                $dist_arr = array_flip($__district[$prov_key.$city_key]);
                $dist_key = $dist_arr[$dist.'区'];
                
                $region = array(
                    'prov' => $prov_key,
                    'city' => $city_key,
                    'dist' => $dist_key,
                    'detail' => $detail,
                    'original' => $region_str
                );
                return $region;
                
            }else{
                $region = array(
                    'prov' => '00',
                    'city' => '00',
                    'dist' => '00',
                    'detail' => $region_str,
                    'original' => $region_str
                );
                return $region;
            }
        }

        static public function getRegionKeyV2($province, $city, $district){
            global $__province,$__city,$__district;
            $prov_key =  null;
            $city_key =  null;
            $dist_key =  null;

            require_once(__DIR__.'/region.php');
            $prov_arr = array_flip($__province);        
            if($province){
    		    $prov_key = $prov_arr[$province];
            }

            if($prov_key && $city){
	            $city_arr = array_flip($__city[$prov_key]);
	            $city_key = $city_arr[$city];
            }

            if($city_key && $district){
                $dist_arr = array_flip($__district[$prov_key.$city_key]);
                $dist_key = $dist_arr[$district];
            }
            
            $region = array(
                    'prov' => $prov_key,
                    'city' => $city_key,
                    'dist' => $dist_key,
            );
            return $region;

        }

        static public function getRegionValueV2($provid, $cityid, $distid, $detail='')
        {
            global $__province,$__city,$__district;
            require_once(__DIR__.'/region.php');
            $provid = str_pad($provid, 2,'0', STR_PAD_LEFT);
            $cityid = str_pad($cityid, 2,'0', STR_PAD_LEFT);
            $distid = str_pad($distid, 2,'0', STR_PAD_LEFT);
            $prov = isset($__province[$provid])?($__province[$provid]):'';
            $city = isset($__city[$provid][$cityid])?($__city[$provid][$cityid]):'';
            $dist = isset($__district[$provid.$cityid][$distid])?($__district[$provid.$cityid][$distid]):'';

            // return $prov.$city.$dist.$detail;
            $data = [
            	'prov'=>$prov,
            	'city'=>$city,
            	'dist'=>$dist,
            ];
            return $data;
            
        }
        
        static public function getRegionValue($provid, $cityid, $detail='')
        {
            global $__province,$__city;
            require_once(__DIR__.'/region.php');
            $provid = str_pad($provid, 2,'0', STR_PAD_LEFT);
            $cityid = str_pad($cityid, 2,'0', STR_PAD_LEFT);

            $prov = isset($__province[$provid])?($__province[$provid]):'';
            $city = isset($__city[$provid][$cityid])?($__city[$provid][$cityid]):'';
            return $prov.$city.$detail;
            
        }
	static public function getBaiduMapXY($placestr)
	{
		$url = 'http://api.map.baidu.com/geocoder/v2/?address='.urlencode($placestr).'&output=json&ak=U75XszFR3f9IRqoiPZPYRZqX';
		$jsonreply = file_get_contents($url);
		$reply = json_decode($jsonreply);
		$point = array(
			'X' => $reply->result->location->lng,
			'Y' => $reply->result->location->lat
		);
		return $point;
	}

	static public function  getGaodeMapXY($placestr)
	{
		$placestr=str_replace(' ','',$placestr);
		$url = 'http://restapi.amap.com/v3/place/text?key=f71f005288cd01ff536101b5b9e3a44a&keywords='.$placestr;

		$jsonreply = file_get_contents($url);
		$reply = json_decode($jsonreply);

		$pois=$reply->pois;

		if($pois){
			$point=$pois[0]->location;

			$point=explode(',',$point);

			$point['x']=$point[0];
			$point['y']=$point[1];
		}else{
			$point = array(
				'X' => '',
				'Y' => ''
			);
		}
		return $point;
	}

    //所有所选城市
    static public function provincesCitys($id,$city_id){
        global $__province, $__city;
        require_once(__DIR__.'/region.php');
        return $__city[$id][$city_id];
    }

    //单个所选城市
    static public function provincesCity($id,$city_id){
        global $__province, $__city;
        require_once(__DIR__.'/region.php');
        return $__city[$id][$city_id];
    }

}
?>
