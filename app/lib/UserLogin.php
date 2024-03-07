<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-8
 * Time: 下午6:35
 */

namespace ULib;
c_lib()->load('ip');
use CLib\Ip;
use Core\Log;

/**
 * Class UserLogin
 * @package ULib
 */
class UserLogin{
	/**
	 * 登录的用户对象
	 * @var \ULib\User
	 */
	private $user;

	/**
	 * @var int
	 */
	private $code;

	/**
	 * 获取错误状态
	 * @return int
	 */
	public function getCode(){
		return $this->code;
	}

	/**
	 * 根据账户名获取用户
	 * @param string $account
	 */
	private function GetAccountUser($account){
		$ar = [];
		$account = trim($account);
		if(is_numeric($account)){
			$ar['id'] = abs(intval($account));
		} else if(filter_var($account, FILTER_VALIDATE_EMAIL)){
			$ar['user_email'] = $account;
		} else{
			lib()->load('UserCheck');
			if(!UserCheck::CheckUsernameChar($account)){
				$this->throwMsg(-1);
			} else{
				$ar['user_name'] = $account;
			}
		}
		lib()->load('User');
		try{
			$this->user = new User($ar);
		} catch(\Exception $ex){
			$this->throwMsg(-2);
		}
	}

	/**
	 * 抛出一个异常
	 * @param int $id
	 * @throws \Exception
	 */
	private function throwMsg($id){
		$this->code = $id;
		throw new \Exception($this->getCodeMsg($id));
	}


	/**
	 * 错误状态表
	 * @param int $id
	 * @return string
	 */
	public function getCodeMsg($id){
		switch($id){
			case -1:
				return ___("Account check name error");
			case -2:
				return ___("Account not found");
			case -3:
				return ___("Hash Password does not match rule");
			case -4:
				return ___("Account password does not match");
			case -5:
				return ___("Captcha Error");
			case -6:
				return ___("Cookie Set Error");
			case -7:
				return ___("Can't set last login info");
			case -8:
				return ___("Error login count to much");
			case -9:
				return $this->user->getStatusInfo($this->user->getStatus());
			case -10:
				return ___("Post login param error");
		}
		return ___("unknown error");
	}

	/**
	 * 登录成功的返回信息
	 * @return array
	 */
	public function LoginContent(){
		return [
			'name' => $this->user->getName(),
			'url' => $this->user->getUrl(),
			'id' => $this->user->getId(),
			'aliases' => $this->user->getAliases(),
			'status' => $this->user->getStatus(),
			'last_login_time' => $this->user->getLastLoginTime(),
			'last_login_ip' => $this->user->getLastLoginIp(),
			'avatar' => $this->user->getAvatar(),
			'status_info' => $this->user->getStatusInfo($this->user->getStatus()),
		];
	}

	/**
	 * POST登录
	 * @param string $account
	 * @param string $password
	 * @param string $captcha
	 * @param bool   $save_status
	 */
	public function PostLogin($account, $password, $captcha, $save_status){
		if(empty($account) || empty($password)){
			$this->throwMsg(-10);
		}
		$save_status = !empty($save_status);
		if(!$this->Captcha($captcha)){
			//验证码检测
			$this->throwMsg(-5);
		}
		$account = strtolower($account);
		$password = strtolower($password);
		$this->GetAccountUser($account);
		lib()->load('UserCheck');
		if(!UserCheck::CheckPasswordChar($password)){
			$this->throwMsg(-3);
		}
		$ip = Ip::getInstance();
		$max_error_count = hook()->apply("UserLogin_max_error_count", 6);
		$now_ip = $ip->realip();
		if($max_error_count <= $this->user->getErrorLoginCount() && $ip->fill($now_ip) === $ip->fill($this->user->getErrorLoginIp()) && explode(" ", $this->user->getErrorLoginTime())[0] == date("Y-m-d")){
			//登录被限制
			$this->throwMsg(-8);
		} else{
			if(UserCheck::CreatePassword($password, $this->user->getSalt()) !== $this->user->getPassword()){
				//错误登录记录
				$this->user->set(array(
					"error_login_count" => 1 + $this->user->getErrorLoginCount(),
					'error_login_time' => date("Y-m-d H:i:s"),
					'error_login_ip' => $now_ip
				));
				if($this->user->getErrorLoginCount() >= $max_error_count){
					hook()->apply("UserLogin_PostLogin_restrictions", NULL, $this->user);
				}
				$this->throwMsg(-4);
			} else{
				if(in_array($this->user->getStatus(), [
					0,
					1,
					2
				])){
					if($this->user->getErrorLoginCount() > 0){
						//错误登录清零
						$this->user->set(array(
							"error_login_count" => 0,
						));
					}
				} else{
					//登录受限制，无法登录
					$this->throwMsg(-9);
				}
			}
		}

		try{
			//登录成功后的COOKIE设置
			if(strlen((string)$this->user->getCookieLogin()) < 10){
				$this->user->set(array("cookie_login" => salt_hash(NOW_TIME . $this->user->getEmail(), salt(20))));
			}
			if($save_status){
				cookie()->set("UserLogin", $this->user->getId() . "\t" . $this->user->getCookieLogin(), hook()->apply("UserLogin_PostLogin_CookieTime", NOW_TIME + 60 * 60 * 24 * 7));
			} else{
				cookie()->set("UserLogin", $this->user->getId() . "\t" . $this->user->getCookieLogin());
			}
		} catch(\Exception $ex){
			$this->throwMsg(-6);
		}
		try{
			//最后登录信息
			self::setLastLoginInfo($this->user);
		} catch(\Exception $ex){
			$this->code = -7;
		}
		hook()->apply('UserLogin_PostLogin_Success', NULL, $this->user);
	}

	/**
	 * 登录验证码检测
	 * @param $code
	 * @return bool
	 */
	private function Captcha($code){
		lib()->load('Captcha');
		$c = new Captcha();
		return hook()->apply("UserLogin_Captcha", $c->verify($code, true), $code);
	}

	/**
	 * 设置最后登录信息
	 * @param User $user
	 * @throws \Exception
	 */
	public static function setLastLoginInfo($user){
		cookie()->set("LoginFlag", date("Y-m-d"));
		$user->set(array(
			'last_login_ip' => Ip::getInstance()->realip(),
			'last_login_time' => date("Y-m-d H:i:s")
		));
	}

	/**
	 * 使用Header信息登录系统
	 * @return bool|User
	 */
	private static function HeaderLogin(){
		if(!defined('HEADER_LOGIN_TOKEN') || !HEADER_LOGIN_TOKEN){
			return false;
		}
		$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : NULL;
		$pwd = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : NULL;
		$token = isset($_SERVER['HTTP_TOKEN']) ? $_SERVER['HTTP_TOKEN'] : NULL;
		if(empty($user) || empty($pwd) || empty($token) || $token != HEADER_LOGIN_TOKEN){
			return false;
		}
		$user_login = new UserLogin();
		$user = $user_login->getUserByPwd($user, $pwd);
		if(is_object($user)){
			return $user;
		}
		return false;
	}

	/**
	 * 依据用户名和密码获取用户信息
	 * @param string $user
	 * @param string $pwd
	 * @return User|false
	 */
	public function getUserByPwd($user, $pwd){
		try{
			$this->GetAccountUser($user);
			$now_pwd = UserCheck::CreatePassword($pwd, $this->user->getSalt());
			$user_pwd = $this->user->getPassword();
			if($now_pwd === $user_pwd){
				return $this->user;
			}
		} catch(\Exception $ex){
		}
		return false;
	}

	/**
	 * 使用COOKIE登录系统
	 * @return bool|User
	 */
	public static function CookieLogin(){
		$cookie = trim((string)req()->cookie('UserLogin'));
		if(!empty($cookie)){
			$cookie = explode("\t", $cookie);
		}else{
            $cookie = [];
        }
		if(count($cookie) != 2){
			//检测header登录
			return self::HeaderLogin();
		}
		$cookie[0] = intval($cookie[0]);
		$cookie[1] = trim($cookie[1]);
		if($cookie[0] > 0){
			try{
				$user = new User($cookie[0]);
				if($user->getCookieLogin() == $cookie[1]){
					if(in_array($user->getStatus(), [
						0,
						1,
						2
					])){
						if(trim(req()->cookie('LoginFlag')) != date("Y-m-d")){
							//如果COOKIE中的日期和当前日期不相符就设置
							try{
								self::setLastLoginInfo($user);
							} catch(\Exception $ex){
								Log::write(___("User last login info set error.ID: ") . $user->getId() . ___(".Exception:") . $ex->getMessage(), Log::SQL);
							}
						}
						return $user;
					}
				}
			} catch(\Exception $ex){
				return false;
			}
		}
		return false;
	}

	public static function Logout(){
		cookie()->del("UserLogin");
	}


}