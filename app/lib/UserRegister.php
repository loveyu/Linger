<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-6
 * Time: 上午11:21
 */

namespace ULib;

if(!class_exists('\ULib\UserCheck')){
	lib()->load('UserCheck');
}
if(!class_exists('\CLib\Ip')){
	c_lib()->load('ip');
}

use Core\Log;
use \ULib\UserCheck;
use \CLib\Ip;

/**
 * 用户注册类
 * Class UserRegister
 * @package ULib
 */
class UserRegister{
	/**
	 * 对应的激活信息
	 * @var string
	 */
	private $activation_msg = "Unknown";

	/**
	 * 验证码检测，首先判断钩子函数
	 * @param $captcha
	 * @return bool
	 */
	private function Captcha($captcha){
		if(hook()->apply('UserRegister_Captcha', false, $captcha)){
			return true;
		}
		if(empty($captcha)){
			return false;
		}
		lib()->load('Captcha');
		$c = new Captcha();
		return $c->verify($captcha, true);
	}

	/**
	 * 用户注册
	 * @param $email     string 邮箱
	 * @param $password  string   hash密码
	 * @param $name      string 用户名
	 * @param $captcha   string 验证码
	 * @return int    错误代码或者用户成功注册ID
	 */
	public function Register($email, $password, $name, $captcha){
		$email = strtolower(trim($email));
		$password = strtolower(trim($password));
		$name = strtolower(trim($name));
		$captcha = trim($captcha);
		if(($code = hook()->apply("UserRegister_Register_before", 0, $email, $password, $name, $captcha)) < 0){
			return $code;
		}
		if(!$this->Captcha($captcha)){
			return -1;
		}
		if(!UserCheck::CheckPassword($password)){
			return -2;
		}
		if(UserCheck::CheckName($name) !== true){
			return -4;
		}
		if(UserCheck::CheckEmail($email) !== true){
			return -5;
		}
		$ip = new Ip();
		$register_array = [
			'user_name' => $name,
			'user_email' => $email,
			'user_aliases' => $name,
			'user_password' => '',
			'user_salt' => salt(64),
			'user_registered_time' => date("Y-m-d H:i:s"),
			'user_registered_ip' => $ip->ip2bin($ip->realip()),
			'user_cookie_salt' => salt(64),
			'user_avatar' => UserCheck::DefaultAvatar(),
			'user_status' => 0
		];

		$register_array['user_password'] = UserCheck::CreatePassword($password, $register_array['user_salt']);

		$reg_code = db()->insert("users", $register_array);
		if($reg_code <= 0){
			Log::write(___("User register insert sql error."), Log::SQL);
			return -3;
		}
		try{
			//关于注册成功的提醒
			hook()->apply("UserRegister_Register_success", $reg_code, $register_array);
			if(hook()->apply("UserRegister_Register_success_send_mail", true)){
				//判断是否注册过程中需要发送注册邮件
				$u = new User($reg_code);
				$this->SendActivationMail($u);
			}
		} catch(\Exception $ex){
			Log::write(___("User register success exception notice"), Log::NOTICE);
		}
		return $reg_code;
	}

	/**
	 * 向对应的用户发送激活邮件
	 * @param $user \ULib\User
	 * @return bool
	 */
	public function SendActivationMail(&$user){
		lib()->load('MailTemplate');
		try{
			$mt = new MailTemplate("activation.html");
			$mt->setUserInfo($user->getInfo());
			$mt->setValues(["activation_url" => $this->CreateActivationUrl($user)]);
			$mt->mailSend($user->getName(), $user->getEmail());
			return true;
		} catch(\Exception $ex){
			$this->activation_msg = $ex->getMessage();
		}
		return false;
	}

	/**
	 * @param $user \ULib\User
	 * @throws \Exception
	 * @return mixed
	 */
	private function CreateActivationUrl(&$user){
		$code = md5(salt(64) . $user->getId());
		$user->getMeta()->set([
			"activation_code" => $code,
			"activation_time" => date("Y-m-d H:i:s")
		]);
		return hook()->apply("UserRegister_CreateActivationUrl", get_url("User", "activation", $code), $code, $user);
	}

	/**
	 * @param User   $user
	 * @param string $code
	 * @throws \Exception
	 * @return int 返回用户状态码
	 */
	public static function UserActivation($user, $code){
		if($user->is_active()){
			throw new \Exception(___("User is already activation"));
		}
		$meta = $user->getMeta()->get([
			"activation_code",
			"activation_time"
		], '');
		if(empty($meta['activation_time']) || empty($meta['activation_code'])){
			throw new \Exception(___("Activation code is invalid"));
		}
		if(time() - strtotime($meta['activation_time']) > hook()->apply('UserRegister_UserActivation_time', 3 * 24 * 60 * 60)){
			throw new \Exception(___("Activation code is time out"));
		}
		if($meta['activation_code'] != $code){
			throw new \Exception(___("Activation code is error"));
		} else{
			$user->set(['status' => 1]);
		}
		return $user->getStatus();
	}

	/**
	 * 获取激活邮件发送的状态
	 * @return string
	 */
	public function SendActivationMsg(){
		return $this->activation_msg;
	}

	/**
	 * 根据错误状态返回相应消息
	 * @param $code int
	 * @return string
	 */
	public function CodeMsg($code){
		switch($code){
			case -1:
				return ___("Captcha code Error");
			case -2:
				return ___("Password char error");
			case -4:
				return ___("Name already exists or does not comply with the rules");
			case -5:
				return ___("Email is exists");
			case -3:
				return ___("Sql Insert Error") . debug(" :" . join(", ", db()->error()['write']));
		}
		return hook()->apply("UserRegister_CodeMsg", "Unknown Error", $code);
	}
}