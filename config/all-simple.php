<?php
/**
 * 简单的配置文件，使用前请修改为all.php 同时修改好对应的数据库配置
 */
return array(
	'sql' => [
		'write' => [
			'database_type' => 'mysql',
			//服务器类型 支持 mysql ,请勿修改
			'server' => 'localhost',
			//服务器地址
			'username' => 'root',
			//用户名
			'password' => '123456',
			//密码
			'database_file' => '',
			//数据库文件,	SqLite 专有文件
			'charset' => 'utf8',
			//编码
			'database_name' => 'linger',
			//数据库名
			'option' => [ //PDO选项
				PDO::ATTR_CASE => PDO::CASE_NATURAL,
				PDO::ATTR_TIMEOUT => 5
			],
		],
		//'read' => [
		//	'database_type' => 'mysql',
		//	'server' => 'localhost',
		//	'username' => 'root',
		//	'password' => '123456',
		//	'database_file' => '',
		//	'charset' => 'utf8',
		//	'database_name' => 'pitus',
		//	'option' => [ //PDO选项
		//				  PDO::ATTR_CASE => PDO::CASE_NATURAL,
		//				  PDO::ATTR_TIMEOUT => 5
		//	],
		//]
	],
	'cookie' => [
		//是否对COOKIE值进行加密，其中密钥在系统配置config.php中
		'encode' => true
	],
	'mail' => [
		//邮件配置设置，参考PHPailer的邮件配置
		'Mailer' => 'smtp',
		'Host' => 'smtp.exmail.qq.com',
		'SMTPAuth' => true,
		'Username' => 'linger@xxx.xxx',
		'Password' => 'xxx',
		'From' => 'linger@xxx.xxx',
		'FromName' => 'linger',
		'Sender' => 'linger@xxx.xxx',
		'XMailer' => 'Loveyu Mailer',
		'CharSet' => 'utf-8',
		"Encoding" => 'base64',
	],
	//默认设置队列，如果需要将所有情况禁止队列则设置为false，当设置为true时需要启用queue.php才能进行邮件发送
	'mail_queue' => false,
	//用于存放系统设置
	'system' => [
		'error_log' => [
			//'type'=>'',
			//'destination'=>'',
			//'headers'=>''
		]
	],
	'mail_template' => _ViewPath_ . "/MailTemplate",
	//邮件模板路径
	'comment_view_path' => "Comment",
	//评论视图路径

	//页面缓存设置
	'pcache' => [
		'status' => true,
		//当为false时忽略缓存设置
		'drive' => 'File'
		//缓存驱动
	],

	'option' => [
		'register_captcha' => 'no',//yes显示验证码，no不显示
		'elastic_server' => "http://127.0.0.1:9200/",//搜索服务器
		'elastic_index_prefix' => "",//搜索索引的前缀，用于测速或快速切换
		'elastic_index' => "picture",//搜索索引的库
		'elastic_status' => false,//搜索索引的库
	]
	//该处选项由数据库添加，请勿随意改动
	//数据库调用的选项
);