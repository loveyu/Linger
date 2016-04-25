<?php
namespace ULib;
if(!defined('_CorePath_')){
	exit;
}
use CLib\Sql;
use CLib\Cookie;

/**
 * 自定义Hook类
 */
class Hook{
	/**
	 * 构造函数
	 */
	public function __construct(){
		set_language();
		spl_autoload_register(__NAMESPACE__ . '\\Hook::auto_load');
		\c_lib()->load('sql', 'ip')->add('sql', new Sql(\cfg()->get('sql', 'write'), \cfg()->get('sql', 'read')));
		l_h("system.php", 'theme.php');
	}

	/**
	 * 添加钩子,并初始化部分信息
	 */
	public function add(){
		$hook = \hook();
		if(!\db()->status()){
			$hook->add('UriInfo_process', function (){
				return [
					'Home',
					'sql_error'
				];
			});
		} else{
			$lib = \lib();
			\c_lib()->load('cookie')->add('cookie', new Cookie(\cfg()->get('cookie', 'encode')));
			$lib->load('AppException', 'UserLogin', 'Option', 'Router', 'User', 'NoticeApply', 'Feed')->add('option', new Option());
			if(count(\cfg()->get('option')) <= 0){
				//系统初始化失败，要求进行系统安装
				define('INIT_ERROR', true);
				$hook->add('UriInfo_process', function (){
					return [
						'Home',
						'init_error'
					];
				});
				return;
			}
			if(!\cfg()->get('mail_queue')){
				$hook->add('MailTemplate_mailSend_noQueue', function (){
					return true;
				});
			}
			\option()->register_hook();
			//添加动态钩子
			Feed::getInstance()->addHook();
			$hook->add("Router_createRouter", function ($list){
				$list['/^avatar\/([1-9]{1}[0-9]*)\/([1-9]{1}[0-9]*\.[a-z]{2,5})$/'] = "/Tool/avatar/[1]/[2]";
				$list['/^avatar\/([1-9]{1}[0-9]*)\/default.jpg/'] = "/Tool/avatar/[1]/default.jpg";
				return $list;
			});
			//读取配置文件之后加载路由类
			$r = new Router();
			//生成路由信息
			$r->createRouter();
			$lib->add("router", $r);
			//添加提醒服务
			$lib->add("notice_apply", new NoticeApply());
			$lib->add("login_user", UserLogin::CookieLogin());
			//添加头像处理钩子
			//开始根据路由信息处理
			$hook->add('UriInfo_process', function ($list){
				return \lib()->using('router')->process($list);
			});
			$hook->add('Markdown_encodeUrlAttribute', function ($url){
				return get_url([
					'Tool',
					'redirect'
				], "?go=" . urlencode($url));
			});
			$hook->add("header_hook", function (){
				echo "<script>var SITE_URL='" . get_url() . "';var IS_LOGIN=" . (is_login() ? "true" : "false") . ";</script>\n";
			});
			$this->publish_cdn();
			if(!\cfg()->get('cache', 'status')){
				//启用页面缓存设置
				\hook()->add("Cache_set", function (){
					//关闭缓存输出
					return false;
				});
			} else{
				pcache();//允许缓存时初始化信息，并加载钩子机制
			}
			$hook->add('footer_hook', function (){
				//输出数据库中的设置
				echo \cfg()->get('option', 'footer');
			});

			//移除验证码
			if(\cfg()->get('option', 'register_captcha') === 'no'){
				$hook->add('UserRegister_Captcha', function (){
					return true;
				});
			}
		}
	}

	private function publish_cdn(){
		if(cdn_info('status')){
			$hook = \hook();
			if(cdn_info('filed', 'get_static_style_url')){
				$hook->add('get_static_style_url', function (){
					return cdn_info('filed', 'get_static_style_url') . path_of_style() . "/" . get_style();
				});
			}
			if(cdn_info('filed', 'get_bootstrap_url')){
				$hook->add('get_bootstrap_url', function (){
					return cdn_info('filed', 'get_bootstrap_url');
				});
			}
			if(cdn_info('filed', 'get_bootstrap_plugin_url')){
				$hook->add('get_bootstrap_plugin_url', function (){
					return cdn_info('filed', 'get_bootstrap_plugin_url') . path_of_bootstrap_plugin();
				});
			}
			if(cdn_info('filed', 'get_js_url')){
				$hook->add('get_js_url', function (){
					return cdn_info('filed', 'get_js_url') . path_of_js();
				});
			}
		}
	}

	/**
	 * @param string $class 自动化类库加载方法
	 * @return bool
	 */
	public static function auto_load($class){
		$map = array(
			'QueueCallback' => 'Queue',
			'ULib\Picture' => 'Picture'
		);
		if(isset($map[$class])){
			$class = $map[$class];
		}
		$path = __DIR__ . "/{$class}.php";
		if(is_file($path)){
			require_once $path;
			return true;
		}
		return false;
	}
}