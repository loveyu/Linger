<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-6
 * Time: 下午4:15
 */

namespace ULib;


/**
 * Class UserCheck
 * @package ULib
 */
class UserCheck{
	/**
	 * @param $pwd string
	 * @return bool
	 */
	public static function CheckPasswordChar($pwd){
		$pwd = strtolower($pwd);
		return hook()->apply("UserCheck_CheckPasswordChar", strlen($pwd) == 40 && preg_match("/^[0-9a-f]+$/", $pwd) > 0, $pwd);
	}

	/**
	 * @param $name string
	 * @return bool
	 */
	public static function CheckUsernameChar($name){
		$name = strtolower(trim($name));
		$l = strlen($name);
		return hook()->apply("UserCheck_CheckUsernameChar", $l <= 20 && $l > 5 && preg_match("/^[_a-z]{1}[a-z0-9_.]{5,19}$/", $name) > 0, $name);
	}

	/**
	 * @param $email
	 * @return bool
	 */
	public static function CheckEmailChar($email){
		$email = strtolower(trim($email));
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			return true;
		} else{
			return false;
		}
	}

	/**
	 * @param $email string
	 * @return true|string
	 */
	public static function CheckEmail($email){
		$email = strtolower(trim($email));
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			return ___("Email does not comply with the rules");
		}
		if(db()->has("users", ['user_email' => $email])){
			return ___('Email already exists');
		}
		return hook()->apply('UserCheck_CheckEmail', true, $email);
	}

	/**
	 * @param $password string
	 * @return true|string
	 */
	public static function CheckPassword($password){
		$password = strtolower(trim($password));
		if(!self::CheckPasswordChar($password)){
			return ___('Password hash is incorrect');
		}
		return hook()->apply('UserCheck_CheckPassword', true, $password);
	}

	/**
	 * @param $name string
	 * @return true|string
	 */
	public static function CheckName($name){
		$name = strtolower(trim($name));
		if(!self::CheckUsernameChar($name)){
			return ___("User name does not comply with the rules");
		}

		if(db()->has("users", ['user_name' => $name])){
			return ___('User name already exists');
		}
		return hook()->apply('UserCheck_CheckEmail', true, $name);
	}

	/**
	 * 根据盐和HASH值计算存储在数据库中的密码
	 * @param $hash
	 * @param $salt
	 * @return string
	 */
	public static function CreatePassword($hash, $salt){
		return _hash(_hash($hash, true) . $salt);
	}

	/**
	 * 根据明文创建一个需要提交的Hash密码
	 * @param $plain
	 * @return string
	 */
	public static function MakeHashChar($plain){
		$sort = str_split($plain);
		sort($sort);
		return _hash($plain . md5(join('', $sort)));
	}

	/**
	 * 获取用户的默认头像
	 * @return string
	 */
	public static function DefaultAvatar(){
		return hook()->apply("UserCheck_DefaultAvatar", "{default}");
	}

}