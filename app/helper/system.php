<?php
if(!defined('_CorePath_')){
	exit;
}
/**
 * 数据库
 * @return \CLib\Sql
 */
function db(){
	return c_lib()->using('sql');
}

/**
 * 返回提醒功能类
 * 登录的前提下使用
 * @return \ULib\Notice
 */
function notice(){
	static $notice = NULL;
	if($notice !== NULL){
		return $notice;
	}
	$lib = lib();
	$notice = $lib->using('notice');
	if(!is_object($notice)){
		$lib->load('Notice');
		$notice = new \ULib\Notice(login_user()->getId());
		$lib->add('notice', $notice);
	}
	return $notice;
}

/**
 * 返回COOKIE对象
 * @return \CLib\Cookie
 */
function cookie(){
	return c_lib()->using('cookie');
}


/**
 * 返回设置选项
 * @return \ULib\Option
 */
function option(){
	return lib()->using('option');
}

/**
 * 获取SESSION对象实例
 * @return \CLib\Session
 */
function session(){
	static $session = NULL;
	if($session !== NULL){
		return $session;
	}
	$lib = c_lib();
	$session = $lib->using('session');
	if($session === false){
		$lib->load('session')->add("session", new \CLib\Session());
		$session = $lib->using("session");
	}
	return $session;
}

/**
 * 取得缓存对象的实例
 * @return \CLib\PCache
 */
function pcache(){
	static $cache = NULL;
	if($cache !== NULL){
		return $cache;
	}
	$lib = c_lib();
	$cache = $lib->using('cache');
	if($cache === false){
		$lib->load('pcache')->add("pcache", new \CLib\PCache(cfg()->get('pcache', 'drive')));
		$cache = $lib->using('pcache');
	}
	return $cache;
}

/**
 * 生成随机字符
 * @param int $len
 * @return string
 */
function salt($len = 40){
	$output = '';
	for($a = 0; $a < $len; $a++){
		$output .= chr(mt_rand(33, 126)); //生成php随机数
	}
	return $output;
}

/**
 * 通过加盐生成hash值
 * @param $hash
 * @param $salt
 * @return string
 */
function salt_hash($hash, $salt){
	$count = strlen($salt);
	return _hash(substr($salt, 0, (int)ceil($count / 3)) . $hash . $salt);
}

/**
 * 字符串明文转换为要提交的哈希值
 * @param string $str
 * @return string
 */
function str_to_pwd_hash($str){
	$arr = mb_split("/(?<!^)(?!$)/u", $str);
	sort($arr);
	return sha1($str . md5(implode('', $arr)));
}

/**
 * 单独封装hash函数
 * @param      $str
 * @param bool $raw_output 为true时返回二进制数据
 * @return string
 */
function _hash($str, $raw_output = false){
	return sha1($str, $raw_output);
}

/**
 * 返回已登录的用户对象
 * @return bool|\ULib\User
 */
function login_user(){
	static $user = NULL;
	if(!is_object($user)){
		$user = lib()->using('login_user');
	}
	return $user;
}

/**
 * 判断用户是否已经登录
 * @return bool
 */
function is_login(){
	return is_object(login_user());
}

/**
 * 跳转到登录页面
 * @var bool $echo 是否输出数据，还是作为跳转
 * @return string|null
 */
function redirect_to_login($echo = false){
	$page = login_page() . "?redirect=" . urlencode(URL_NOW);
	if($echo){
		return $page;
	}
	redirect($page);
	return NULL;
}

/**
 * 判断是否允许修改邮箱
 * @return bool
 */
function edit_email_action(){
	return is_login() && hook()->apply("edit_email_action", login_user()->getStatus() == 0, login_user());
}

/**
 * 登录页面
 * @return string
 */
function login_page(){
	return get_url(hook()->apply("Helper_login_page", [
		"Home",
		"login"
	]));
}

/**
 * 网站标题
 * @return string
 */
function site_title(){
	return cfg()->get('option', 'site_title');
}

/**
 * 网站描述
 * @return string
 */
function site_desc(){
	return cfg()->get('option', 'site_desc');
}

/**
 * 网站URL
 * @return string
 */
function site_url(){
	$c = cfg()->get("option", "site_url");
	if(empty($c)){
		return URL_WEB;
	} else{
		return $c;
	}
}

/**
 * 获取静态资源地址
 * @return string
 */
function site_static_url(){
	$c = cfg()->get("option", "site_static_url");
	if(empty($c)){
		return URL_FILE;
	} else{
		return $c;
	}
}

/**
 * 网站HTTPS URL
 * @return string
 */
function site_url_ssl(){
	$c = cfg()->get("option", "site_url_ssl");
	if(empty($c)){
		return URL_WEB;
	} else{
		return $c;
	}
}

function site_mode(){
	$c = cfg()->get("option", "site_mode");
	if(empty($c)){
		return "http";
	} else{
		return $c;
	}
}

/**
 * 获取静态HTTPS资源地址
 * @return string
 */
function site_static_url_ssl(){
	$c = cfg()->get("option", "site_static_url_ssl");
	if(empty($c)){
		return URL_FILE;
	} else{
		return $c;
	}
}

/**
 * 管理员邮箱
 * @return string
 */
function admin_email(){
	return cfg()->get('option', 'admin_email');
}

/**
 * 允许注册
 * @return bool
 */
function allowed_register(){
	return cfg()->get('option', 'allowed_register') == "yes";
}

/**
 * 邮件提示
 * @return bool
 */
function email_notice(){
	return cfg()->get('option', 'email_notice') == "yes";
}

/**
 * 允许评论
 * @return bool
 */
function allowed_comment(){
	return cfg()->get('option', 'allowed_comment') == "yes";
}

/**
 * 是否采用倒序方式排列评论
 * @return bool
 */
function comment_order_desc(){
	return cfg()->get('option', 'comment_order_desc') == "yes";
}

/**
 * 嵌套评论最大层数
 * @return int
 */
function comment_deep(){
	$c = intval(cfg()->get('option', 'comment_deep'));
	return $c ? $c : 5;
}

/**
 * 默认用户头像配置
 * @return string
 */
function default_avatar_config(){
	return cfg()->get('option', 'default_avatar');
}

/**
 * 是否开启登录验证码
 * @return bool
 */
function login_captcha(){
	return cfg()->get('option', 'login_captcha') == "yes";
}


/**
 * 获取网站样式
 * @param null|string $path 参数为null时返回样式名，否则返回对应的路径
 * @return string
 */
function get_style($path = NULL){
	$style = cfg()->get('option', 'site_style');
	if($path === NULL){
		return $style;
	}
	return get_style_url($style, $path);
}

/**
 * @return int 每页显示评论数量
 */
function comment_one_page(){
	$c = intval(cfg()->get('option', 'comment_one_page'));
	return $c > 0 ? $c : 10;
}

/**
 * 返回主题操作
 * @return \ULib\Theme
 */
function theme(){
	$lib = lib();
	$theme = $lib->using('theme');
	if($theme === false){
		$lib->load('Theme')->add("theme", new \ULib\Theme());
		$theme = $lib->using("theme");
	}
	return $theme;
}

/**
 * 获取登录后要跳转的地址
 * @param string $param
 * @return string
 */
function get_login_redirect($param = 'redirect'){
	$url = req()->_plain()->req($param);
	if(empty($url)){
		if(isset($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) === u()->getUriInfo()->getHttpHost()){
			$url = $_SERVER['HTTP_REFERER'];
			if(trim(strtolower(explode(ROUTER_SPLIT_CHAR, parse_url($url, PHP_URL_PATH))[1])) === "home"){
				$url = get_url("User");
			}
		} else{
			$url = get_url();
		}
	}
	if(empty($url)){
		return get_url('User');
	} else{
		return urldecode($url);
	}
}

/**
 * 获取当前设置的服务器地址
 * @return string
 */
function picture_server(){
	return strtolower(cfg()->get('option', 'picture_server'));
}

/**
 * 缩略图宽度
 * @return int
 */
function image_thumbnail_width(){
	$i = intval(cfg()->get('option', 'image_thumbnail_width'));
	return $i > 0 ? $i : 400;
}

/**
 * 缩略图高度
 * @return int
 */
function image_thumbnail_height(){
	$i = intval(cfg()->get('option', 'image_thumbnail_height'));
	return $i > 0 ? $i : 300;
}

/**
 * 高清图宽度
 * @return int
 */
function image_hd_width(){
	$i = intval(cfg()->get('option', 'image_hd_width'));
	return $i > 0 ? $i : 1600;
}

/**
 * 显示图宽度
 * @return int
 */
function image_display_width(){
	$i = intval(cfg()->get('option', 'image_display_width'));
	return $i > 0 ? $i : 900;
}

/**
 * markdown 语法转换
 * @param string $data
 * @return string
 */
function get_markdown($data){
	static $md = NULL;
	if($md === NULL){
		lib()->load('Markdown');
		$md = new \ULib\Markdown();
	}
	return $md->transform($data);
}

/**
 * 获取CDN信息
 * @param string $type 类型
 * @param string $name 字段名称
 * @return array|bool|string
 */
function cdn_info($type = 'all', $name = ''){
	static $cdn_info = NULL;
	if($cdn_info === NULL){
		$cdn_info = @unserialize(cfg()->get('option', 'cdn'));
		if(!isset($cdn_info['status']) || !isset($cdn_info['list'])){
			$cdn_info['status'] = false;
			$cdn_info['list'] = [];
		}
	}
	switch($type){
		case 'all':
			return $cdn_info['list'];
		case 'status':
			return $cdn_info['status'] === true;
		case 'filed':
			if(isset($cdn_info['list'][$name])){
				$x = explode("|", $cdn_info['list'][$name]);
				if(isset($x[1])){
					if(is_ssl()){
						return $x[1];
					} else{
						return $x[0];
					}
				} else{
					return trim($x[0]);
				}
			}
			return false;
	}
	return false;
}

/**
 * 搜索功能是否被开启
 * @return bool
 */
function search_func_is_open(){
	$cfg = cfg();
	$search_open = $cfg->get('option', 'elastic_status');
	$search_open = $search_open === true || $search_open === "1" || $search_open == "open" || $search_open === 1;
	return $search_open;
}