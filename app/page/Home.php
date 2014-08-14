<?php
namespace UView;

use Core\Log;
use \Core\Page;
use ULib\UserControl;
use ULib\UserLogin;

class Home extends Page{
	/**
	 * @var \ULib\Theme
	 */
	private $theme;

	function __construct(){
		$this->theme = theme();
	}

	public function register(){
		if(req()->is_ajax()){
			$this->__view("Home/register.php");
			return;
		}
		$this->theme->setTitle("用户注册");
		$this->__view("Home/header.php");
		$this->__view("Home/register.php");
		$this->__view("Home/footer.php");
	}

	public function login(){
		if(req()->is_ajax()){
			$this->__view("Home/login.php", ['account' => req()->_plain()->get('account')]);
			return;
		}
		$this->theme->setTitle("用户登录");
		$this->__view("Home/header.php");
		$this->__view("Home/login.php", ['account' => req()->_plain()->get('account')]);
		$this->__view("Home/footer.php");
	}

	public function logout(){
		try{
			lib()->load('UserLogin');
			UserLogin::Logout();
			redirect();
		} catch(\Exception $ex){
			$this->theme->setTitle("登出失败");
			$this->__view("Home/header.php");
			$this->__view("Home/logout_error.php");
			$this->__view("Home/footer.php");
		}
	}

	public function forget_password(){
		$this->theme->setTitle("找回密码");
		$this->__view("Home/header.php");
		$this->__view("Home/forget_password.php");
		$this->__view("Home/footer.php");
	}

	public function reset_password($user = NULL, $code = NULL){
		lib()->load('UserControl');
		$uc = new UserControl();
		$this->theme->setTitle("密码重置");
		$this->__view("Home/header.php");
		$this->__view("Home/reset_password.php", [
			'user' => $user,
			'code' => $code,
			'status' => $uc->reset_password_check($user, $code)
		]);
		$this->__view("Home/footer.php");
	}

	public function sql_error(){
		if(db()->status()){
			$this->not_found();
		} else{
			$this->__view("Home/sql_error.php", [
				'msg' => db()->ex_message(),
				'email' => cfg()->get('mail', 'From')
			]);
		}
	}
	public function init_error(){
		if(!defined('INIT_ERROR') || INIT_ERROR!==true){
			$this->not_found();
		} else{
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			$this->__view("Home/init_error.php");
		}
	}

	public function permission(){
		send_http_status(403);
		$this->theme->setTitle("权限不足");
		header("Content-Type: text/html; charset=utf-8");
		$this->__view("Home/header.php");
		$this->__view("Home/permission.php");
		$this->__view("Home/footer.php");
	}

	public function not_found(){
		send_http_status(404);
		$this->theme->setTitle("404 Not Found!");
		$this->__view("Home/header.php");
		$this->__view("Home/404.php");
		$this->__view("Home/footer.php");
	}
}