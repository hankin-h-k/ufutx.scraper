<?php

return array(
		'enable' => env('MESSENGER_ENABLE', false),
		'debug' => env('MESSENGER_DEBUG', true),
		'debug_mobile'=> env('MESSENGER_DEBUG_MOBILE', '15872844805'),
		'debug_email' => 'zglore@163.com',
		'mainland_vender' => 'luosimao',
        'signature' => '【友福图书馆】',
		'proxy_enable' => false,
		'proxy_host' => '127.0.0.1',
		'proxy_port' => '3128',
		'mainland' => array(
			'Uid' => 'ufutx',
			'Key' => 'fb701e02cfdfd98e1b2e',
		),
		'webchinese' => array(
			'Uid' => 'ufutx',
			'Key' => 'fb701e02cfdfd98e1b2e',
		),
		'luosimao' => array(
            'key' => '77b31f67c1a30e9d5f50c3975fa29650',
		),
		'gausstel' => array(
            'username' => '70802:admin',
            'password' => '32636299',
            //'isvoice' => '3|2|2|0',//是否语音|重听次数|重打次数|是否回复
		),
		'hongkong' => array(
			'accountno' => '11011405',
			'pwd' => '5678065'
		),
		'mailer' => array(
			'api_user_invite' => 'ufutx_invite',
			'api_user_notice' => 'ufutx_notice',
			'api_key' => '5Uupbj8HQOs5WXc6',
			'from' => 'info@ufutx.com',
			'fromname' => '友福同享',
		),
		'ucpaas' => array(
			'accountsid' => 'cdb5c4d629bd19bc7e01bddad58eec4a',
			'token' => 'f3444ebcedcaf09a826eab697b87eef1',
			'appid' => '9f529885f5b44eeba30e3d634ef8d564'
		),
);

