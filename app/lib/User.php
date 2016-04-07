<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-6
 * Time: 上午10:50
 */

namespace ULib;

use CLib\Ip;
use Core\Log;

/**
 * Class User
 * @package ULib
 */
class User{
	/**
	 * @var int
	 */
	private $id;
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $aliases;
	/**
	 * @var string
	 */
	private $url;
	/**
	 * @var string
	 */
	private $password;
	/**
	 * @var int
	 */
	private $salt;
	/**
	 * @var int
	 */
	private $status;
	/**
	 * @var string
	 */
	private $email;

	/**
	 * @var string 存储转换后的头像信息
	 */
	private $avatar;


	/**
	 * 存储原始的头像详细
	 * @var string
	 */
	private $avatar_sql;

	/**
	 * @var string
	 */
	private $cookie_salt;

	/**
	 * @var string
	 */
	private $cookie_login;
	/**
	 * @var string
	 */
	private $registered_time;
	/**
	 * @var string
	 */
	private $registered_ip;

	/**
	 * @var string
	 */
	private $last_login_time;
	/**
	 * @var string
	 */
	private $last_login_ip;

	/**
	 * @var string
	 */
	private $error_login_ip;
	/**
	 * @var string
	 */
	private $error_login_time;

	/**
	 * @var Meta
	 */
	private $meta;

	/**
	 * @var int
	 */
	private $error_login_count;

	/**
	 * @var array
	 */
	private $status_info = [];

	/**
	 * 存储用户列表
	 * @var array
	 */
	private static $user_stack = [];

	/**
	 * @var array 用户名堆栈
	 */
	private static $user_stack_name = [];

	/**
	 * 个人附加信息存储列表
	 * @var array
	 */
	private $profile_list = [];

	private static $column_list = [
		'id',
		'name',
		'aliases',
		'url',
		'password',
		'salt',
		'status',
		'email',
		'avatar',
		'cookie_salt',
		'cookie_login',
		'registered_time',
		'registered_ip',
		'last_login_time',
		'last_login_ip',
		'error_login_ip',
		'error_login_time',
		'error_login_count',
	];

	/**
	 * @param int|'+'|'-' $operate
	 * @param null|User|int $user
	 * @return bool|User
	 */
	public static function UserStack($operate, $user = NULL){
		if(is_int($operate) && isset(self::$user_stack[$operate])){
			return self::$user_stack[$operate];
		} else{
			switch($operate){
				case '+':
					if(is_object($user) && get_class($user) == "ULib\\User" && $user->getId() > 0){
						self::$user_stack[$user->getId()] = $user;
						self::$user_stack_name[$user->getName()] = $user->getId();
						return true;
					} else{
						Log::write(___("User add stack error.") . print_r($user, true), Log::ALERT);
					}
					break;
				case '-':
					if(is_int($user) && isset(self::$user_stack[$user])){
						unset(self::$user_stack[$user]);
						$tmp = array_flip(self::$user_stack_name);
						unset($tmp[$user]);
						self::$user_stack_name = array_flip($tmp);
						return true;
					}
					break;
			}
		}
		return false;
	}

	/**
	 * 从堆栈中获取一个用户
	 * @param int|string $id
	 * @return User
	 */
	public static function getUser($id){
		if(is_numeric($id) && isset(self::$user_stack[$id])){
			$user = self::$user_stack[$id];;
		} else{
			if(isset(self::$user_stack_name[$id]) && isset(self::$user_stack[self::$user_stack_name[$id]])){
				$user = self::$user_stack[self::$user_stack_name[$id]];
			} else{
				$user = NULL;
			}
		}
		if(!is_object($user)){
			try{
				$user = new User($id);
			} catch(\Exception $ex){
				$user = NULL;
			}
		}
		return $user;
	}

	/**
	 * 判断用户是否激活
	 * @return bool
	 */
	public function is_active(){
		return $this->status == 1;
	}

	/**
	 * @param int|array $user_id      用户ID或者对应的查询条件
	 * @param bool      $exists_array 是否使用数组来创建一个用户
	 * @throws \Exception
	 */
	public function __construct($user_id, $exists_array = false){
		$this->status_info = [
			-1 => ___('Unknown'),
			0 => ___('Unverified'),
			//用户未经过验证
			1 => ___('Normal'),
			//正常用户
			2 => ___('Restrict Login'),
			//限制用户，取消限制后成为正常用户
			3 => ___('Lockout Login'),
			//被锁用户,取消后成为未验证用户
		];
		if($exists_array === true && is_array($user_id)){
			$user = $user_id;
		} else{
			if(!is_array($user_id)){
				if(is_numeric($user_id)){
					$user_id = ['id' => abs(intval($user_id))];
				} else{
					$user_id = ['user_name' => trim($user_id)];
				}
			} else if(count($user_id) != 1 || is_object($user_id)){
				throw new \Exception(___("User ID param error."));
			} else if(!isset($user_id['id']) && !isset($user_id['user_name']) && !isset($user_id['user_email'])){
				throw new \Exception(___("User ID array param col error."));
			}
			$user = db()->get("users", [
				'id',
				'user_name' => 'name',
				'user_aliases' => 'aliases',
				'user_email' => 'email',
				'user_url' => 'url',
				'user_password' => 'password',
				'user_salt' => 'salt',
				'user_status' => 'status',
				'user_registered_time' => 'registered_time',
				'user_registered_ip' => 'registered_ip',
				'user_last_login_time' => 'last_login_time',
				'user_last_login_ip' => 'last_login_ip',
				'user_error_login_count' => 'error_login_count',
				'user_error_login_ip' => 'error_login_ip',
				'user_error_login_time' => 'error_login_time',
				'user_cookie_salt' => 'cookie_salt',
				'user_cookie_login' => 'cookie_login',
				'user_avatar' => 'avatar'
			], $user_id);
		}
		if(!is_array($user)){
			throw(new \Exception(___("Unknown User")));
		} else{
			foreach($user as $key => $value){
				if(in_array($key, self::$column_list)){
					$this->$key = $value;
				}
			}
			c_lib()->load('ip');
			$ip = new Ip();
			$this->error_login_ip = $ip->bin2ip($this->error_login_ip);
			$this->registered_ip = $ip->bin2ip($this->registered_ip);
			$this->last_login_ip = $ip->bin2ip($this->last_login_ip);
			$this->avatar_sql = $this->avatar;
			$this->avatar = $this->avatar_convert($this->avatar);
			self::UserStack('+', $this);
		}
	}

	/**
	 * @return string
	 */
	public function getAvatarSql(){
		return $this->avatar_sql;
	}

	/**
	 * @return string
	 */
	public function getCookieLogin(){
		return $this->cookie_login;
	}

	/**
	 * @param $avatar
	 * @return string
	 */
	private function avatar_convert($avatar){
		lib()->load("Avatar");
		return Avatar::get($avatar, $this);
	}

	/**
	 * @return array
	 */
	public function getInfo(){
		return [
			'id' => $this->id,
			'name' => $this->name,
			'aliases' => $this->aliases,
			'url' => $this->url,
			'password' => $this->password,
			'salt' => $this->salt,
			'status' => $this->status,
			'email' => $this->email,
			'avatar' => $this->avatar,
			'cookie_salt' => $this->cookie_salt,
			'cookie_login' => $this->cookie_login,
			'registered_time' => $this->registered_time,
			'registered_ip' => $this->registered_ip,
			'last_login_time' => $this->last_login_time,
			'last_login_ip' => $this->last_login_ip,
			'error_login_ip' => $this->error_login_ip,
			'error_login_time' => $this->error_login_time,
			'error_login_count' => $this->error_login_count,
		];
	}

	/**
	 * 获取简要信息
	 * @return array
	 */
	public function getSimpleInfo(){
		return [
			'id' => $this->id,
			'name' => $this->name,
			'aliases' => $this->aliases,
			'url' => $this->url,
			'avatar' => $this->avatar,
		];
	}

	/**
	 * @return string
	 */
	public function getRegisteredIp(){
		return $this->registered_ip;
	}

	/**
	 * @return string
	 */
	public function getAliases(){
		return $this->aliases;
	}

	/**
	 * 获取头像
	 * @param int $size
	 * @return string
	 */
	public function getAvatar($size = NULL){
		if($size !== NULL && is_numeric($size) && $size > 0 && $size < 400){
			return Avatar::getSizeOfAvatar($this->avatar, $this->avatar_sql, $size);
		}
		return $this->avatar;
	}

	/**
	 * @return string
	 */
	public function getCookieSalt(){
		return $this->cookie_salt;
	}

	/**
	 * @return string
	 */
	public function getEmail(){
		return $this->email;
	}

	/**
	 * @return int
	 */
	public function getErrorLoginCount(){
		return $this->error_login_count;
	}

	/**
	 * @return string
	 */
	public function getErrorLoginIp(){
		return $this->error_login_ip;
	}

	/**
	 * @return string
	 */
	public function getErrorLoginTime(){
		return $this->error_login_time;
	}

	/**
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getLastLoginIp(){
		return $this->last_login_ip;
	}

	/**
	 * @return string
	 */
	public function getLastLoginTime(){
		return $this->last_login_time;
	}

	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPassword(){
		return $this->password;
	}

	/**
	 * @return string
	 */
	public function getRegisteredTime(){
		return $this->registered_time;
	}

	/**
	 * @return int
	 */
	public function getSalt(){
		return $this->salt;
	}

	/**
	 * @return int
	 */
	public function getStatus(){
		return $this->status;
	}

	/**
	 * @param $status int
	 * @return string
	 */
	public function getStatusInfo($status){
		if(isset($this->status_info[$status])){
			return $this->status_info[$status];
		} else{
			return $this->status_info[-1];
		}
	}

	/**
	 * @return string
	 */
	public function getUrl(){
		return $this->url;
	}

	/**
	 * @return \ULib\Meta
	 */
	public function getMeta(){
		if($this->meta === NULL){
			lib()->load('Meta');
			//创建用户标签对象
			$this->meta = new Meta("user_meta", "users_id", $this->id);
		}
		return $this->meta;
	}

	/**
	 * 验证用户权限或获取用户权限
	 * @param null|string $verify
	 * @return array|bool
	 */
	public function Permission($verify = NULL){
		$permission = [];
		foreach(explode("\n", $this->getMeta()->get(['Permission'], "\n")['Permission']) as $v){
			$v = trim($v);
			if(!in_array($v, $permission)){
				$permission[] = $v;
			}
		}
		if($verify === NULL){
			return $permission;
		} else{
			return in_array($verify, $permission);
		}
	}

	/**
	 * 返回或设置用户的个人附加描述信息
	 * @param string|null $set 留空时为获取，否则为设置
	 * @return bool
	 */
	public function profile_message($set = NULL){
		if($set === NULL){
			if(!isset($this->profile_list['profile_message'])){
				$this->getProfileList();
			}
			return $this->profile_list['profile_message'];
		} else{
			$this->getMeta()->set(['profile_message' => $set]);
		}
		return true;
	}

	/**
	 * 获取个人视频描述
	 * @param string|null $set 留空时为获取，否则为设置
	 * @return bool
	 */
	public function profile_video($set = NULL){
		if($set === NULL){
			if(!isset($this->profile_list['profile_video'])){
				$this->getProfileList();
			}
			return $this->profile_list['profile_video'];
		} else{
			$this->getMeta()->set(['profile_video' => $set]);
		}
		return true;
	}

	/**
	 * 获取用户附加信息
	 */
	private function getProfileList(){
		if(empty($this->profile_list)){
			$this->profile_list = $this->getMeta()->get([
				'profile_message',
				'profile_video'
			], "");
		}
	}

	/**
	 * @param $list array
	 * @throws \Exception
	 */
	public function set($list){
		$data = [];
		$update = [];
		foreach($list as $name => $value){
			$name = trim($name);
			if($name !== 'id' && in_array($name, self::$column_list)){
				$data[$name] = $value;
				$update["user_" . $name] = $value;
				if(substr($name, -3) === '_ip'){
					$update["user_" . $name] = Ip::getInstance()->ip2bin($value);
				}
			}
		}
		lib()->load('UserCheck');
		if(isset($update['user_aliases']) && empty($update['user_aliases'])){
			throw new \Exception(___("Aliases can't set empty."));
		}
		if(isset($update['user_email']) && !UserCheck::CheckEmailChar($update['user_email'])){
			throw new \Exception(___("Email verify check Error"));
		}
		if(isset($update['user_name']) && !UserCheck::CheckUsernameChar($update['user_name'])){
			throw new \Exception(___("Username verify check Error"));
		}
		if(isset($update['user_password']) && !UserCheck::CheckPasswordChar($update['user_password'])){
			throw new \Exception(___("Password verify check Error"));
		}
		if(isset($update['user_url']) && $update['user_url'] != "" && !filter_var($update['user_url'], FILTER_VALIDATE_URL)){
			throw new \Exception(___("Url check error"));
		}
		if(count($update) > 0){
			if(db()->update("users", $update, ['id' => $this->id]) === false){
				throw new \Exception(___("Can't update User info.") . debug("SQL msg:" . implode(",", db()->error()['write'])));
			}
			foreach($data as $n => $v){
				$this->$n = $v;
			}
		}
	}
}