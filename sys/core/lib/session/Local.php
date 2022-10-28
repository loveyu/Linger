<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-8
 * Time: 下午10:07
 */

namespace CLib\Session;

c_lib()->load('session');

use CLib\Cookie;
use CLib\SessionInterface;
use Core\Log;

/**
 * 使用自带的本地存储Session
 * Class Local
 * @package CLib\Session
 */
class Local implements SessionInterface{

	private $flag = false;

	/**
	 * 启动Session
	 * @param array $cfg
	 */
	public function __construct($cfg = []){
		$cookie = new Cookie();
		$config = [
			'lifetime' => defined('SESSION_LIFE_TIME') ? SESSION_LIFE_TIME : 0,
			'path' => $cookie->path(''),
			'domain' => $cookie->domain(''),
			'secure' => false,
			'httponly' => true
		];
		$config = array_merge($config, $cfg);
		session_set_cookie_params($config['lifetime'], $config['path'], $config['domain'], $config['secure'], $config['httponly']);
		if(session_status() === PHP_SESSION_DISABLED || session_id() === ""){
			//Session未启用
			if(!$this->flag){
				session_start();
				$this->flag = true;
			}
		}
	}

	/**
	 * GET操作
	 * @param $name string 数组键名
	 * @return mixed
	 */
	public function get($name){
		if(isset($_SESSION[$name])){
			return $_SESSION[$name];
		} else{
			if($name === NULL){
				return $_SESSION;
			}
			return NULL;
		}
	}

	/**
	 * 设置操作
	 * @param $name  string 数组键名
	 * @param $value string 对应的值
	 * @return bool
	 */
	public function set($name, $value){
		$_SESSION[$name] = $value;
		return true;
	}

	/**
	 * 删除操作
	 * @param $name string 数组键名
	 * @return bool
	 */
	public function delete($name){
		if(isset($_SESSION[$name])){
			unset($_SESSION[$name]);
		}
		return isset($_SESSION[$name]);
	}

	/**
	 * 彻底删除SESSION
	 * @return void
	 */
	public function destroy(){
		if(session_status() === PHP_SESSION_ACTIVE){
			if($this->flag){
				session_unset();
				$this->flag = true;
			}
		}
	}

}