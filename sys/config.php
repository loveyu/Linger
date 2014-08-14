<?php
if(PHP_VERSION < 5.4){
	die(_("PHP Version must be greater than 5.4"));
}

//设置时区
date_default_timezone_set("PRC");

/**
 * 程序版本
 */
define("_VERSION_","1.0.0");

/**
 * 定义唯一名称
 * 当修改语言标示后需要修改才名称
 */
define("_AppName_", "linger");

/**
 * 调试模式，基本未使用
 */
define('_Debug_', false);

/**
 * 转义是否开启标志
 */
define('MAGIC_QUOTES_GPC', PHP_VERSION < 6 && get_magic_quotes_gpc());

/**
 * 路由器分割字符
 */
define('ROUTER_SPLIT_CHAR', '/');

/**
 * COOKIE加密密钥
 */
define('COOKIE_KEY', 'xS/087N*+O:JTd%3z8+YTrkjrz<\'$K<^No@@L`wh');

/**
 * COOKIE前缀
 */
define('COOKIE_PREFIX', 'LC_');

/**
 * 基本路径
 */
define("_BasePath_", dirname(__DIR__)."/web");

/**
 * App应用文件路径
 */
define("_AppPath_", dirname(__DIR__)."/app");

/**
 * 系统路径
 */
define("_SysPath_", __DIR__);

/**
 * 核心路径
 */
define("_CorePath_", _SysPath_ . "/core");

/**
 * 日志文件路径
 */
define("_LogPath_", _AppPath_ . "/log");

/**
 * 语言文件包路径
 */
define("_Language_", _AppPath_ . "/language");

/**
 * 页面路径
 */
define("_PagePath_", _AppPath_ . "/page");

/**
 * 功能函数路径
 */
define("_HelperPath_",_AppPath_ . "/helper");

/**
 * 视图路径
 */
define("_ViewPath_", _AppPath_ . "/view");

/**
 * 类库路径
 */
define("_LibPath_", _AppPath_ . "/lib");

//设置运行错误信息
if(_Debug_){
	ini_set('display_errors','on');
	error_reporting(E_ALL | E_STRICT);
} else{
	ini_set('display_errors','off');
	error_reporting(0);
}

//加载接口文件
require(_CorePath_ . "/interface.php");