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
                'DB_HOST'   => '10.66.210.137', // 服务器地址
                'DB_NAME'   => 'cps', // 数据库名
                'DB_USER'   => 'zhuanf_admin', // 用户名
                'DB_PWD'    => 'L755hKxMmm', // 密码
		'DB_PORT'   => 3306, // 端口
		'DB_PREFIX' => 'qw_', // 数据库表前缀
		'DB_CHARSET'=>  'utf8',      // 数据库编码默认采用utf8
		//备份配置
		'DB_PATH_NAME'=> 'db',        //备份目录名称,主要是为了创建备份目录
		'DB_PATH'     => './db/',     //数据库备份路径必须以 / 结尾；
		'DB_PART'     => '20971520',  //该值用于限制压缩后的分卷最大长度。单位：B；建议设置20M
		'DB_COMPRESS' => '1',         //压缩备份文件需要PHP环境支持gzopen,gzwrite函数        0:不压缩 1:启用压缩
		'DB_LEVEL'    => '9',         //压缩级别   1:普通   4:一般   9:最高
		'URL_MODEL' => '0',
		'SESSION_OPTIONS'         =>  array(
			'name'                =>  'BJYSESSION',                    //设置session名
			'expire'              =>  24*3600*15,                      //SESSION保存15天
			'use_trans_sid'       =>  1,                               //跨页传递
			'use_only_cookies'    =>  0,                               //是否只开启基于cookies的session的会话方式
		),
	    'MODULE_ALLOW_LIST'    =>    array('Index','Ln'),
	    'DEFAULT_MODULE'       =>    'Ln',
		'DEFAULT_CONTROLLER'    =>  'Login',
);
