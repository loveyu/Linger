<?php
/**
 * 通过传入一个参数判断是否开启了评论
 * @param int $status
 * @return bool
 */
function image_comment_status($status){
	return allowed_comment() && 0 + $status > 0;
}

/**
 * 显示评论
 * @param ULib\CommentData $comment_data 评论的对象
 * @param bool             $read_only    是否显示评论框
 */
function display_comment($comment_data, $read_only = false){
	$comment_data->show($read_only);
}

/**
 * @param ULib\Comment[] $list 评论对象
 * @param int            $deep 深度
 */
function comment_display($list, $deep){
	foreach($list as $v){
		echo "<div>{$deep}\n";
		echo $v->getCommentContent();
		comment_display($v->getSubNode(), 1 + $deep);
		echo "\n</div>\n";
	}
}

/**
 * 生成图片的访问地址
 * @param int $id 图片ID
 * @return string 固定连接
 */
function picture_link($id){
	/**
	 * @var ULib\Router $router
	 */
	static $router = NULL;
	if($router === NULL){
		$router = lib()->using('router');
	}
	return get_url($router->getLink('picture', $id));
}

/**
 * 生成图集的访问地址
 * @param int $id 图集ID
 * @return string 固定连接
 */
function gallery_link($id){
	/**
	 * @var ULib\Router $router
	 */
	static $router = NULL;
	if($router === NULL){
		$router = lib()->using('router');
	}
	return get_url($router->getLink('gallery', $id));
}

/**
 * 获取用户主页地址
 * @param string|int $name 用户名或ID
 * @return string
 */
function user_link($name){
	static $router = NULL;
	if($router === NULL){
		$router = lib()->using('router');
	}
	if(is_numeric($name)){
		try{
			$name = \ULib\User::getUser(intval($name))->getName();
		} catch(Exception $ex){
			//nothing
		}
	}
	return get_url($router->getLink('user', $name));
}

/**
 * 获取文章的连接
 * @param string $name 不允许使用ID访问
 * @return string
 */
function post_link($name){
	/**
	 * @var \ULib\Router $router
	 */
	static $router = NULL;
	if($router === NULL){
		$router = lib()->using('router');
	}
	return get_url($router->getLink('post', $name));
}

/**
 * 获取文章列表页面
 * @return string
 */
function post_list_link(){
	/**
	 * @var \ULib\Router $router
	 */
	static $router = NULL;
	if($router === NULL){
		$router = lib()->using('router');
	}
	return get_url($router->getLink('post_list'));
}

/**
 * 获取文章列表分页页面
 * @param int $number
 * @return string
 */
function post_list_pager_link($number){
	/**
	 * @var \ULib\Router $router
	 */
	static $router = NULL;
	if($router === NULL){
		$router = lib()->using('router');
	}
	return get_url($router->getLink('post_list_pager', $number));
}

/**
 * 获取时间线页面
 * @return string
 */
function time_line_link(){
	/**
	 * @var \ULib\Router $router
	 */
	static $router = NULL;
	if($router === NULL){
		$router = lib()->using('router');
	}
	return get_url($router->getLink('time_line'));
}

function gallery_list_link(){
	/**
	 * @var \ULib\Router $router
	 */
	static $router = NULL;
	if($router === NULL){
		$router = lib()->using('router');
	}
	return get_url($router->getLink('gallery_list'));
}

function user_gallery_list_link($user_name){
	/**
	 * @var \ULib\Router $router
	 */
	static $router = NULL;
	if($router === NULL){
		$router = lib()->using('router');
	}
	return get_url($router->getLink('user_gallery_list', $user_name));
}


function create_menu_link($link, $name, $title = NULL, $uri = NULL, $class_name = "active", $external = false){
	static $s_uri = NULL;
	if($title !== NULL){
		$title = " title=\"$title\"";
	}
	if($s_uri === NULL){
		$s_uri = u()->getUriInfo()->getUrlList();
	}
	$class_v = "";
	if(is_array($s_uri) && is_array($uri)){
		$c1 = count($s_uri);
		$c2 = count($uri);
		if($c1 === 0 && $c1 === $c2){
			$class_v = " class=\"" . htmlspecialchars($class_name, ENT_QUOTES) . "\"";
		} else if($c1 >= $c2 && $c2 > 0){
			$flag = true;
			for($i = 0, $a = reset($s_uri), $b = reset($uri); $i < $c2; $i++){
				if($a !== $b){
					$flag = false;
					break;
				} else{
					$a = next($s_uri);
					$b = next($uri);
				}
			}
			unset($a, $b);
			if($flag){
				$class_v = " class=\"" . htmlspecialchars($class_name, ENT_QUOTES) . "\"";
			}
		}
		unset($i, $c2, $c1);
	}
	if($external){
		$external = " rel=\"external\"";
	} else{
		$external = '';
	}
	return "<li{$class_v}><a{$external} href=\"{$link}\"{$title}>{$name}</a></li>";
}


/**
 * 页面头部加载钩子
 */
function header_hook(){
	hook()->apply("header_hook", NULL);
}

/**
 * 页面尾部加载钩子
 */
function footer_hook(){
	hook()->apply("footer_hook", NULL);
}

/**
 * 转换时间
 * @param int|string $time
 * @param int        $deep 深度
 * @return string
 */
function convert_time($time, $deep = 2){
	$t = $time;
	if(is_numeric($time)){
		$time = intval($time);
	} else{
		$time = strtotime($time);
		if($time === -1){
			return $t;
		}
	}
	$deep = intval($deep);
	if($deep < 1){
		$deep = 1;
	}
	$rt = convert_time_deep(time() - $time, $deep);
	if($rt === ''){
		return _("at now");
	} else if($rt === NULL){
		return $t;
	} else{
		return $rt . _(" ago.");
	}
}

/**
 * 对时间差值进行计算
 * @param int $time
 * @param int $deep
 * @return string
 */
function convert_time_deep($time, $deep){
	if($time < 0 || $deep < 1){
		return NULL;
	}
	if($time < 30){
		return '';
	} else if($time < 60){
		return '30' . _("seconds");
	} else if($time < 3600){
		return floor($time / 60) . _("minutes");
	} else if($time < 86400){
		$f = floor($time / 3600);
		return $f . _("hours") . convert_time_deep($time - $f * 3600, $deep - 1);
	} else if($time < 2592000){
		$f = floor($time / 86400);
		return $f . _("days") . convert_time_deep($time - $f * 86400, $deep - 1);
	} else if($time < 31536000){
		$f = floor($time / 2592000);
		return $f . _("months") . convert_time_deep($time - $f * 2592000, $deep - 1);
	} else{
		$f = floor($time / 31536000);
		return $f . _("years") . convert_time_deep($time - $f * 31536000, $deep - 1);
	}
}

/**
 * 获取Bootstrap网址
 * @return string
 */
function get_bootstrap_url(){
	static $url = NULL;
	if($url === NULL){
		$url = hook()->apply("get_bootstrap_url", get_file_url() . path_of_bootstrap());
	}
	return $url . "/" . implode("/", func_get_args());
}

/**
 * 获取Bootstrap插件网址
 * @return string
 */
function get_bootstrap_plugin_url(){
	static $url = NULL;
	if($url === NULL){
		$url = hook()->apply("get_bootstrap_plugin_url", get_file_url() . path_of_bootstrap_plugin());
	}
	return $url . "/" . implode("/", func_get_args());
}

/**
 * 获取Js目录网址
 * @return string
 */
function get_js_url(){
	static $url = NULL;
	if($url === NULL){
		$url = hook()->apply("get_js_url", get_file_url() . path_of_js());
	}
	return $url . "/" . implode("/", func_get_args());
}

/**
 * 获取样式分类网址
 * @return string
 */
function get_style_url(){
	static $url = NULL;
	if($url === NULL){
		$url = hook()->apply("get_style_url", get_file_url() . path_of_style());
	}
	return $url . "/" . implode("/", func_get_args());
}

/**
 * 获取一个用于存储当前主题的文件夹可设置外部信息
 * 推荐加载主题静态资源时使用
 * @return string
 */
function get_static_style_url(){
	static $url = NULL;
	if($url === NULL){
		$url = hook()->apply("get_static_style_url", get_style_url() . get_style());
	}
	return $url . "/" . implode("/", func_get_args());
}

/**
 * 获取bootstrap插件相对路径
 * @return string
 */
function path_of_bootstrap_plugin(){
	return "bootstrap/plugins";
}

/**
 * 获取bootstrap相对路径
 * @return string
 */
function path_of_bootstrap(){
	return "bootstrap";
}

/**
 * 获取js相对路径
 * @return string
 */
function path_of_js(){
	return "js";
}

/**
 * 获取样式相对路径
 * @return string
 */
function path_of_style(){
	return "style";
}

/**
 * 转换视频代码信息为一个数组
 * @param string $data
 * @return array
 */
function convert_video_code($data){
	$data = implode("", array_flip(array_flip(explode("\n", str_replace("][", "]\n[", preg_replace("/[\s]+/", "", $data))))));
	preg_match_all("/\\[([a-zA-Z0-9-]+?)\\|([\s\S]+?)\\]/", $data, $matches, PREG_SET_ORDER);
	if(is_array($matches)){
		$rt = [];
		foreach($matches as $v){
			$t = [
				'name' => strtolower($v[1]),
				'param' => $v[2]
			];
			$rt[] = $t;
		}
		return $rt;
	} else{
		return [];
	}
}

/**
 * 转换视频参数
 * @param $name
 * @param $param
 * @return bool|null|string
 */
function convert_video_param($name, $param){
	$name = strtolower($name);
	$param = explode("|", $param);
	switch($name){
		case 'youku':
			if(empty($param[0])){
				return false;
			}
			return "http://player.youku.com/player.php/sid/{$param[0]}/v.swf";
		case 'tudou':
			if(count($param) !== 4){
				return false;
			}
			return "http://www.tudou.com/{$param[0]}/{$param[1]}/&iid={$param[2]}&resourceId={$param[3]}/v.swf";
		case 'iqiyi':
			if(empty($param[0])){
				return false;
			}
			return "http://player.video.qiyi.com/{$param[0]}";
	}
	return NULL;
}

/**
 * 获取当前用户未读的消息数量
 * @return string
 */
function get_unread_message_count(){
	if(!is_login()){
		return "";
	}
	$obj = lib()->using('CountMessage');
	if(!is_object($obj)){
		lib()->load('CountMessage');
		$obj = new \ULib\CountMessage();
	}
	$n = $obj->getUnreadMessage(login_user()->getId());
	return $n ? : "";
}