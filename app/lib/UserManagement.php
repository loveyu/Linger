<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-2-16
 * Time: 下午2:45
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace ULib;


/**
 * Class UserManagement
 * @package ULib
 */
class UserManagement{
	/**
	 * @var int
	 */
	private $user_count = 0;

	/**
	 * @var string
	 */
	private $search_type = '';

	/**
	 * @var string
	 */
	private $search_value = '';


	/**
	 * @var string
	 */
	private $order_key = 'id';

	/**
	 * 设置查找选项
	 * @param $type
	 * @param $value
	 */
	public function set_search($type, $value){
		if(!in_array($type, [
			'id',
			'name',
			'aliases',
			'email',
			'url',
			'status'
		])
		){
			$type = '';
		}
		$this->search_type = $type;
		$this->search_value = $value;
	}

	/**
	 * 设置排序方式
	 * @param string $key
	 */
	public function set_order($key){
		if(!in_array($key, [
			'id',
			'name',
			'aliases',
			'email',
		])
		){
			$key = 'id';
		}
		if($key !== 'id'){
			$key = "user_$key";
		}
		$this->order_key = $key;
	}

	/**
	 * 获取新注册的用户
	 * @param int $number
	 * @return User[]
	 */
	public function get_new_users($number = 10){
		$number = intval($number);
		$number > 1 or $number = 5;
		$data = db()->select("users", [
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
		], [
			'user_status' => 1,
			'ORDER' => 'id DESC',
			'LIMIT' => $number
		]);
		if($data === false){
			return [];
		}
		$rt = [];
		foreach($data as $v){
			$rt[] = new User($v, true);
		}
		return $rt;
	}

	/**
	 * @param $page
	 * @param $one_number
	 * @throws \Exception
	 * @return bool|array
	 */
	public function get_users($page, $one_number){
		$one_number = intval($one_number);
		$page = intval($page);
		if($one_number < 1){
			$one_number = 1;
		}
		if($page < 1){
			$page = 1;
		}
		$page_count = ceil($this->get_user_count() / $one_number);
		if($page > $page_count){
			throw new \Exception("Page is too much, User are not found.");
		}
		$where = [];
		$like = $this->get_search_sql_array();
		if($like !== false){
			$where = [
				'LIKE' => $like,
				'ORDER' => $this->order_key,
				'LIMIT' => [
					($page - 1) * $one_number,
					$one_number
				]
			];
		} else{
			$where = [
				'ORDER' => $this->order_key,
				'LIMIT' => [
					($page - 1) * $one_number,
					$one_number
				]
			];
		}
		$data = db()->select("users", [
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
		], $where);
		if($data === false){
			throw new \Exception("Data get error on SQL." . debug("ERROR:" . implode(", ", db()->error()['read'])));
		}
		return $this->user_data_convert($data);
	}

	/**
	 * 获取需要在SQL中查找的数组
	 */
	private function get_search_sql_array(){
		if($this->search_type == '' || $this->search_value == ''){
			return false;
		}
		switch($this->search_type){
			case "id":
				return ['id' => $this->search_value];
			case "name":
				return ['user_name' => $this->search_value];
			case "aliases":
				return ['user_aliases' => $this->search_value];
			case "email":
				return ['user_email' => $this->search_value];
			case "url":
				return ['user_url' => $this->search_value];
			case "status":
				return ["user_status" => $this->search_value];
		}
		return false;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private function user_data_convert($data){
		$rt = [];
		foreach($data as $v){
			$user = new User($v, true);
			$rt[] = $user->getInfo();
		}
		return $rt;
	}

	/**
	 * @param $page
	 * @param $one_number
	 * @return array
	 */
	public function get_page_info($page, $one_number){
		$rt = [];
		$rt['one_number'] = intval($one_number);
		$rt['page'] = intval($page);
		if($rt['one_number'] < 1){
			$rt['one_number'] = 1;
		}
		if($rt['page'] < 1){
			$rt['page'] = 1;
		}
		$rt['count'] = $this->get_user_count();
		$rt['page_count'] = ceil($rt['count'] / $rt['one_number']);
		if($rt['page'] == 1){
			$rt['before_page'] = NULL;
		}
		if($rt['page'] == $rt['page_count']){
			$rt['next_page'] = NULL;
		}
		return $rt;
	}

	/**
	 * @return mixed
	 */
	public function get_user_count(){
		if($this->user_count < 1){
			$where = NULL;
			$like = $this->get_search_sql_array();
			if($like !== false){
				$where = [
					'LIKE' => $like,
				];
			}
			$this->user_count = db()->count("users", $where);
		}
		return $this->user_count;
	}
} 