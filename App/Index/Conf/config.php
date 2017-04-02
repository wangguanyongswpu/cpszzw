<?php
/**
 *
 * 版权所有：恰维网络<Ln.qiawei.com>
 * 作    者：寒川<hanchuan@qiawei.com>
 * 日    期：2015-09-15
 * 版    本：1.0.0
 * 功能说明：配置文件。
 *
 **/
return array(
		//'URL' =>'http://www.test.com', //网站根URL
		//数据库链接配置
		'DB_TYPE'   => 'mysql', // 数据库类型
                'DB_HOST'   => '10.66.153.150', // 服务器地址
                'DB_NAME'   => 'cps', // 数据库名
                'DB_USER'   => 'root', // 用户名
                'DB_PWD'    => '57bS-67a3-4fba6', // 密码
		'DB_PORT'   => 3306, // 端口
		'DB_PREFIX' => 'qw_', // 数据库表前缀
		'DB_CHARSET'=>  'utf8',      // 数据库编码默认采用utf8
		//备份配置
		'DB_PATH_NAME'=> 'db',        //备份目录名称,主要是为了创建备份目录
		'DB_PATH'     => './db/',     //数据库备份路径必须以 / 结尾；
		'DB_PART'     => '20971520',  //该值用于限制压缩后的分卷最大长度。单位：B；建议设置20M
		'DB_COMPRESS' => '1',         //压缩备份文件需要PHP环境支持gzopen,gzwrite函数        0:不压缩 1:启用压缩
		'DB_LEVEL'    => '9',         //压缩级别   1:普通   4:一般   9:最高
		'LOG_RECORD' => true, // 开启日志记录
		'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR,DEBUG', // 只记录EMERG ALERT CRIT ERR 错误
		'DB_FIELDS_CACHE'=>false, // 关闭字段缓存
		
		'SESSION_OPTIONS'         =>  array(

			'name'                =>  'BJYSESSION',                    //设置session名

			'expire'              =>  24*3600*15,                      //SESSION保存15天

			'use_trans_sid'       =>  1,                               //跨页传递

			'use_only_cookies'    =>  0,                               //是否只开启基于cookies的session的会话方式

		),

		//支付回调地址
		'paymentCallbackURl'=> 'http://p161-133.jslcwdq.com/PayApi/callback',
		//bank回调地址
		'paybankCallbackURl'=> 'http://p161-133.jslcwdq.com/PayApi/bankcallback',
		//支付宝回调地址
		'alipayCallbackURl'=> 'http://p161-133.jslcwdq.com/PayApi/alipayback',
		//支付完成回调密钥
		"PAY_API_CALL_KEY" => '18361130555',

		'cps_api' => [
			'1000000001' =>  [
				'app_id' => '1000000001',
				'app_key'=> 'SKDJSKDJK123SDAS90SDDSA',
				'web_id' => '1'
			],
			'1000000002' =>  [
				'app_id' => '1000000002',
				'app_key'=> 'R07L4ZXQBNWN1PA4YH1WRHJD9S0PCSH6',
				'web_id' => '2'
			],
			'ad_1000001' =>  [
				'app_id' => 'ad_1000001',
				'app_key'=> 'R07L4ZXQBNWN1PA4YH1WRHJD9S0PCSH6',
				'ref_id' => '450',
				'g_zid' => 'chunfengzhuan',
			],
			'ad_1000002' =>  [
				'app_id' => 'ad_1000002',
				'app_key'=> '1R17ES5ITKR15H3FDJNWGGL4YWUFC2TZ',
				'ref_id' => '731',
				'g_zid' => 'weichuan',
			],
			'ad_1000003' =>  [
				'app_id' => 'ad_1000003',
				'app_key'=> 'QA4U5AC0CFEM87KCNCV32IIR52DN8P84',
				'ref_id' => '732',
				'g_zid' => 'zhuandianqian',
			],
			'ad_1000004' =>  [
				'app_id' => 'ad_1000004',
				'app_key'=> 'PB61L8W04UKS3UG9TB83A9UO5I6OVIWK',
				'ref_id' => '733',
				'g_zid' => 'youdezhuan',
			],
			'ad_1000005' =>  [
				'app_id' => 'ad_1000005',
				'app_key'=> 'ZTRRE11200CCED3MDHBI4JMFO1ZXPY2A',
				'ref_id' => '734',
				'g_zid' => 'kuangzhuan',
			],
			'ad_1000006' =>  [
				'app_id' => 'ad_1000006',
				'app_key'=> 'IXF9IG142IDPE253CO6CN6X8BXD68D90',
				'ref_id' => '735',
				'g_zid' => 'laidianqian',
			],
			'ad_1000007' =>  [
				'app_id' => 'ad_1000007',
				'app_key'=> 'E83CD7268J6UXZ3MU2VXZY3FXJFCQ2K4',
				'ref_id' => '736',
				'g_zid' => 'yifenxiao',
			],
			'ad_1000008' =>  [
				'app_id' => 'ad_1000008',
				'app_key'=> 'EO9TT47P1YHNN0MANPJGJOUZSK7HM5KL',
				'ref_id' => '737',
				'g_zid' => 'fenxiaozhijia',
			],
			'ad_1000009' =>  [
				'app_id' => 'ad_1000009',
				'app_key'=> 'ZNK8U7LEU1EH05VQBHP8LH8RJ0Y80PJP',
				'ref_id' => '738',
				'g_zid' => 'aifenxiao',
			],
			'ad_1000010' =>  [
				'app_id' => 'ad_1000010',
				'app_key'=> 'ETZMKC8H9I734DO553UG83CVX7OLMRYL',
				'ref_id' => '739',
				'g_zid' => 'zhuanfa',
			],
			'ad_1000011' =>  [
				'app_id' => 'ad_1000011',
				'app_key'=> '8X1CVI8FD9FHRVNB9R4ZWWBXE6YWDZY2',
				'ref_id' => '740',
				'g_zid' => 'zhenxiangzhuan',
			]
		],

		'cps_ad' => [
			'450' => [
				'ref_id' => '450',
				'callbank_url' => 'http://www.weiyutz.com/',//尾部带上“/”
			],
			'731' => [
				'ref_id' => '731',
				'callbank_url' => 'http://www.oaguanli.com/',
			],
			'732' => [
				'ref_id' => '732',
				'callbank_url' => 'http://www.aboluoart.com/',
			],
			'733' => [
				'ref_id' => '733',
				'callbank_url' => 'http://www.shouyaoren.com/',
			],
			'734' => [
				'ref_id' => '734',
				'callbank_url' => 'http://www.baileht.com/',
			],
			'735' => [
				'ref_id' => '735',
				'callbank_url' => 'http://online.jpypdb.cn/',
			],
			'736' => [
				'ref_id' => '736',
				'callbank_url' => 'http://online.dipzts.cn/',
			],
			'737' => [
				'ref_id' => '737',
				'callbank_url' => 'http://online.dgxfdo.cn/',
			],
			'738' => [
				'ref_id' => '738',
				'callbank_url' => 'http://online.pwuygx.cn/',
			],
			'739' => [
				'ref_id' => '739',
				'callbank_url' => 'http://online.uwhntr.cn/',
			],
			'740' => [
				'ref_id' => '740',
				'callbank_url' => 'http://online.juwjda.cn/',
			]
		],
		
		'BANK_PAY_URL'=> 'https://pay.Heepay.com/Payment/Index.aspx', //网银支付接口地址
		'QUERY_URL'=> 'https://query.heepay.com/Payment/Query.aspx',
		'AGENT_ID'=> '2072012',
		'SIGN_KEY'=>'3C7B0C67C5A64AE8AEAE7886',
		
		'MOIVE_API_HOST' => 'http://moive.webwiregroup.com/',
		'TMPL_ACTION_SUCCESS'=>'Public:dispatch_jump',
    	'TMPL_ACTION_ERROR'=>'Public:dispatch_jump',
		
		'FINANCE_IDS' => array(413,964),
		"EXCEL_REF1_ID"=>'428,952,1931,1933,1946,1947,1887',
		
		
    "ENTRANCE_API_URL" => "http://1.haooda.com/index.php/index/api/GetDomain?code=18361130555",
    "MOIVE_API_URL" => "http://moive.webwiregroup.com:99/index.php/index/api/GetDomain?code=18361130555&layer=",
    "SHARE_URL" => "http://www.hmkrj.com/fx/uk.html",
    "PACKET_TYPE" => 2,
    "70_CARD" => array(
        'pay_url' => 'http://yy.yzch.net/pay.aspx',
        'userid' => 6113,
        'keyvalue' => '9cc4332a0f5defe695caabd2435d1824',
        'notify_url' => 'http://www.qkftq.com/PayApi/callback_70',
        'return_url' => '',
    ),

    //研发人员电话,用于系统报警
    "DEV_TELS" => '13540639499,18780126426,15198004372,15608074459,13708039483',
	
	//威富通支付用公众号被封在这里修改配置， 并发邮件让威富通改微信配置
    'WECHAT_CONFIG' => [
        'appid' => 'wx83064fe377c3aaa2',
        'secret' => '22579e42ad591492bc987db00dea3ade',
        'url' => 'www.hqqwb.com',
    ],
	'PORT' => '',
);

