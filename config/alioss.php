<?php
return array(
	'id'=> env('ALIOSS_ID', 'HhMAugGujLx0VHwv'),
	'secret' => env('ALIOSS_SECRET', '7MCgNj0fuGprDSb8SxgOgcSv8w8wWR'),
	'host' => env('ALIOSS_HOST', 'oss-cn-shenzhen.aliyuncs.com'),
	'webhost' => env('ALIOSS_WEBHOST', 'http://local-pictures.oss-cn-shenzhen.aliyuncs.com'),
	'host_public' => env('ALIOSS_HOST_PUBLIC', 'oss-cn-shenzhen.aliyuncs.com'),
	'buckets' => [
		'file' => 'local-files',
		'chat' => 'local-files',
		'picture' => env('ALIOSS_BLOCKS_PICTURE', 'local-pictures'),
		'avatar' => 'local-pictures',
	],
	'picture_domain' => env('ALIOSS_PICTURE_DOMAIN', 'local-pictures.img-cn-shenzhen.aliyuncs.com'),
	'picture_thumb' => '@1e_200w_200h_1c_0i_1o_90Q_1x.jpg',
	'picture_thumbm' => '@1e_400w_400h_1c_0i_1o_90Q_1x.jpg',
	'picture_scale' => '@1e_400w_400h_0c_0i_1o_90Q_1x.jpg',

	'picture_post' => '@0e_0o_1l_300h_700w_90q.src',
	'picture_scale1' => '@0e_0o_0l_560h_560w_90q',
	'picture_scale2' => '@1e_1c_0o_0l_225h_225w_90q',
	'picture_scale3' => '@1e_1c_0o_0l_180h_180w_90q',
	'avatar_big' => '@0e_0o_0l_180h_180w_90q',
	'avatar_middle' => '@0e_0o_0l_180h_180w_90q',
	'avatar_small' => '@0e_0o_0l_48h_48w_90q',
	'tuan_list' => '@1e_1c_2o_1l_430h_800w_90q.src',
	'shop_list' => '@1e_1c_2o_1l_400h_400w_90q.src',
	'shop_carousel' => '@1e_1c_2o_1l_430h_800w_90q.src',
	'shop_carousel_list' => 'image/resize,m_fill,w_600,h_340,limit_0/auto-orient,0/quality,q_90',
	'shop_menu_list' => 'image/resize,m_fill,w_40,h_40,limit_0/auto-orient,0/quality,q_90',
	'shop_recommend_list' =>  'image/resize,m_fill,w_300,h_300,limit_0/auto-orient,0/quality,q_90',
	'shop_recommend' =>  'image/resize,m_fill,w_600,h_340,limit_0/auto-orient,0/quality,q_90',
);
