<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-11
 * Time: 下午4:48
 */

namespace ULib;


/**
 * Class UserControl
 * @package ULib
 */
use CLib\Upload;

/**
 * Class UserControl
 * @package ULib
 */
class UserControl{
	/**
	 * @var int
	 */
	private $code = 0;

	/**
	 * 用来存储用户密码重置的临时对象
	 * @var User null
	 */
	private $reset_password_user = NULL;

	/**
	 * @param $user_id
	 * @param $permission
	 */
	public function PermissionAdd($user_id, $permission){
		$permission = trim($permission);
		$user = User::getUser($user_id);
		$p = $user->Permission();
		if(in_array($permission, $p)){
			$this->throwMsg(-1);
		} else{
			$p[] = $permission;
			$user->getMeta()->set(['Permission' => implode("\n", $p)]);
		}
	}

	/**
	 * @param $user_id
	 * @param $permission
	 */
	public function PermissionSet($user_id, $permission){
		$user = User::getUser($user_id);
		$ps = [];
		foreach(explode("\n", $permission) as $v){
			$v = trim($v);
			if(!empty($v) && !in_array($v, $ps)){
				$ps[] = $v;
			}
		}
		$ps = implode("\n", $ps);
		if($ps === implode("\n", $user->Permission())){
			$this->throwMsg(-2);
		}
		$user->getMeta()->set(['Permission' => $ps]);
	}

	/**
	 * @param $code
	 * @throws \Exception
	 */
	public function throwMsg($code){
		$this->code = 0 + $code;
		throw new \Exception($this->getMsg(0 + $code), 0 + $code);
	}

	/**
	 * @param $email
	 * @param $captcha
	 */
	public function reset_password($email, $captcha){
		lib()->load('Captcha', 'MailTemplate', 'User');
		$c = new Captcha();
		if(!$c->verify($captcha, true)){
			$this->throwMsg(-3);
		}
		$user = new User(['user_email' => trim($email)]);
		if(!in_array($user->getStatus(), [
			0,
			1,
			2
		])
		){
			$this->throwMsg(-4);
		}
		$mt = new MailTemplate("reset_password.html");
		$mt->setUserInfo($user->getInfo());
		$mt->setValues(["reset_password_url" => $this->create_reset_password_url($user)]);
		$mt->mailSend($user->getName(), $user->getEmail());
	}

	/**
	 * @param User $user
	 * @return string
	 */
	private function create_reset_password_url($user){
		$code = salt_hash($user->getId(), time() . salt());
		$time = date("Y-m-d H:i:s");
		$user->getMeta()->set([
			'Reset_password_code' => $code,
			'Reset_password_time' => $time
		]);
		return get_url("Home", "reset_password", $user->getName(), $code);
	}

	/**
	 * @param $user_name
	 * @param $code
	 * @param $password
	 * @throws \Exception
	 */
	public function reset_password_finish($user_name, $code, $password){
		$password = trim($password);
		$status = $this->reset_password_check($user_name, $code);
		if($status !== true){
			throw new \Exception($status);
		}
		if($this->reset_password_user !== NULL){
			lib()->load('UserCheck');
			if(UserCheck::CheckPassword($password) !== true){
				$this->throwMsg(-6);
			}
			$this->reset_password_user->getMeta()->delete([
				'Reset_password_code',
				'Reset_password_time'
			]);
			$s_p = UserCheck::CreatePassword($password, $this->reset_password_user->getSalt());
			$this->reset_password_user->set([
				'password' => $s_p,
				//密码
				'cookie_login' => '',
				//登录Cookie
				'error_login_count' => 0
				//错误登录次数
			]);
		} else{
			$this->throwMsg(-5);
		}
	}

	/**
	 * 删除密码重置请求
	 * @param User $user
	 */
	public function delete_reset_password_request($user){
		$user->getMeta()->delete([
			'Reset_password_code',
			'Reset_password_time'
		]);
	}

	/**
	 * 修改用户密码
	 * @param User   $user
	 * @param string $old
	 * @param string $new
	 */
	public function edit_user_password($user, $old, $new){
		$l = strlen(_hash(""));
		if($l !== strlen($old) || $l !== strlen($new)){
			$this->throwMsg(-7);
		}
		lib()->load("UserCheck");
		if($user->getPassword() !== UserCheck::CreatePassword($old, $user->getSalt())){
			$this->throwMsg(-8);
		}
		$list = ['salt' => salt(64)];
		$list['password'] = UserCheck::CreatePassword($new, $list['salt']);
		$user->set($list);
	}

	/**
	 * @param string $user_name
	 * @param string $code
	 * @return bool|string
	 */
	public function reset_password_check($user_name, $code){
		try{
			lib()->load('User');
			$user = new User(['user_name' => trim($user_name)]);
			$meta = $user->getMeta()->get([
				'Reset_password_code',
				'Reset_password_time'
			], '');
			if(empty($meta['Reset_password_code']) || empty($meta['Reset_password_time'])){
				return _("reset param error.");
			}
			if(time() - strtotime($meta['Reset_password_time']) > hook()->apply('UserControl_reset_password_check_time', 3 * 24 * 60 * 60)){
				return _("Code time out.");
			}
			if($meta['Reset_password_code'] !== trim($code)){
				return _("Code error.");
			}
			$this->reset_password_user = $user;
			return true;
		} catch(\Exception $ex){
			return $ex->getMessage();
		}
	}

	/**
	 * @param User   $user
	 * @param string $type
	 */
	public function reset_cookie($user, $type){
		$type = trim($type);
		$list = [
			'cookie_salt' => salt(),
			'cookie_login' => salt_hash($user->getSalt(), salt())
		];
		switch($type){
			case "salt":
				unset($list['cookie_login']);
				break;
			case "login":
				unset($list['cookie_salt']);
				break;
			default:
				$this->throwMsg(-9);
		}
		$user->set($list);
	}

	/**
	 * 发送邮件给新的邮箱地址
	 * @param User   $user
	 * @param string $email
	 * @param string $password
	 * @throws \Exception
	 */
	public function edit_email_send_mail($user, $email, $password){
		lib()->load('UserCheck', 'MailTemplate');
		$email = strtolower(trim($email));
		if($user->getPassword() !== UserCheck::CreatePassword($password, $user->getSalt())){
			$this->throwMsg(-10);
		}
		$email_check = UserCheck::CheckEmail($email);
		if($email_check !== true){
			throw new \Exception($email_check);
		}
		$meta = [
			'edit_email_add' => $email,
			'edit_email_time' => date("Y-m-d H:i:s"),
			'edit_email_code' => salt_hash($email . $user->getEmail(), salt())
		];
		$user->getMeta()->set($meta);
		$mt = new MailTemplate("edit_email.html");
		$mt->setUserInfo($user->getInfo());
		$mt->setValues(['verify_code' => $meta['edit_email_code']]);
		$mt->mailSend($user->getName(), $email);
	}

	/**
	 * 修改用户邮箱
	 * @param User   $user
	 * @param string $email
	 * @param string $password
	 * @param string $code
	 * @throws \Exception
	 */
	public function edit_email($user, $email, $password, $code){
		lib()->load('UserCheck');
		$email = strtolower(trim($email));
		$code = strtolower(trim($code));
		if($user->getPassword() !== UserCheck::CreatePassword($password, $user->getSalt())){
			$this->throwMsg(-10);
		}
		$email_check = UserCheck::CheckEmail($email);
		if($email_check !== true){
			throw new \Exception($email_check);
		}
		$meta = $user->getMeta()->get([
			'edit_email_add',
			'edit_email_time',
			'edit_email_code'
		], '');
		if($meta['edit_email_add'] !== $email){
			$this->throwMsg(-13);
		}
		if($meta['edit_email_code'] !== $code){
			$this->throwMsg(-12);
		}
		if(time() - strtotime($meta['edit_email_time']) > hook()->apply('UserControl_edit_email_time', 3 * 24 * 60 * 60)){
			$this->throwMsg(-11);
		}
		$user->getMeta()->delete([
			'edit_email_add',
			'edit_email_time',
			'edit_email_code'
		]);
		$user->set(['email' => $email]);
	}

	/**
	 * 修改头像类型
	 * @param User   $user
	 * @param string $type
	 */
	public function edit_avatar($user, $type){
		$list = [
			'{default}',
			'{gravatar}',
			'{site_avatar}',
			'{user_upload}'
		];
		$type = strtolower($type);
		if(!in_array($type, $list)){
			$this->throwMsg(-14);
		}
		if($type === "{user_upload}"){
			lib()->load('Avatar');
			$avatar = Avatar::upload_avatar($user);
			if(empty($avatar)){
				$this->throwMsg(-15);
			}
		}
		$user->set(['avatar' => $type]);
	}

	/**
	 * @param User $user
	 */
	public function upload_avatar($user){
		c_lib()->load('upload');
		$upload = new Upload([
			'root_path' => 'avatar',
			'exts' => [
				'jpg',
				'png'
			],
			'sub_status' => false,
			'name_callback' => [
				function ($param){
					return $param;
				},
				$user->getId()
			],
			'replace' => true,
			'max_size' => 500 * 1024,
			'save_ext' => 'jpg'
		], 'Local');
		if(!isset($_FILES['avatar'])){
			$this->throwMsg(-16);
		}
		$info = $upload->uploadOne($_FILES['avatar']);
		lib()->load('Avatar');
		$avatar = new Avatar();
		$avatar->process_avatar($info['save_name'], 400, 400);
	}

	/**
	 * @param $code
	 * @return string
	 */
	public function getMsg($code){
		switch($code){
			case -1:
				return _("Permission is exists");
			case -2:
				return _("Permission is not change.");
			case -3:
				return _("Captcha code error.");
			case -4:
				return _("User is locked, can't reset password.");
			case -5:
				return _("Reset user ex.");
			case -6:
				return _("Reset Password check error.");
			case -7:
				return _("The password param is error format.");
			case -8:
				return _("Old password is not match.");
			case -9:
				return _("Cookie reset type is not in match list");
			case -10:
				return _("User password error.");
			case -11:
				return _("Time out, please send mail again.");
			case -12:
				return _("Code error.");
			case -13:
				return _("email address is change, please send mail again.");
			case -14:
				return _("avatar type is not defined.");
			case -15:
				return _("user upload avatar error or not found.");
			case -16:
				return _("upload form data is not defined");
			default:
				return _("Unknown");
		}
	}

} 