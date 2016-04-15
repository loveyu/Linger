<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-25
 * Time: 下午2:53
 * Filename: FollowManagement.php
 */

namespace ULib;


use Core\Log;

/**
 * Class FollowManagement
 * @package ULib
 */
class FollowManagement extends AppException{
	/**
	 * 数据库操作类
	 * @var \CLib\Sql
	 */
	private $db;

	/**
	 * @var int[] 分页统计信息
	 */
	private $count = [
		'page' => 1,
		'max' => 1,
		'count' => 0,
		'number' => 20,
	];

	/**
	 * 构造
	 */
	function __construct(){
		$this->db = db();
	}

	/**
	 * 关注用户
	 * @param $id
	 * @param $u_id
	 */
	public function follow($id, $u_id){
		$id = intval($id);
		$u_id = intval($u_id);
		if($id < 1 || $u_id < 1){
			$this->throwMsg(-1);
		}
		if($id == $u_id){
			$this->throwMsg(-2);
		}
		try{
			User::getUser($id);
			User::getUser($u_id);
		} catch(\Exception $ex){
			$this->throwMsg(-5);
		}
		$get = $this->db->select("users_follow_users", ['users_id'], [
			'AND' => [
				'users_id' => $u_id,
				'follow_users_id' => $id
			]
		]);
		if($get === false){
			Log::write(___("users follow table get error."), Log::SQL);
			$this->throwMsg(-3);
		}
		if(isset($get[0])){
			$this->throwMsg(-4);
		}
		$status = $this->db->insert("users_follow_users", [
			'users_id' => $u_id,
			'follow_users_id' => $id,
			'follow_time' => date("Y-m-d H:i:s"),
			'follow_update' => 0,
			'follow_update_time' => date("Y-m-d H:i:s")
		]);
		if($status === -1){
			Log::write(___("follow message insert error."), Log::SQL);
			$this->throwMsg(-3);
		}
		hook()->apply('FollowManagement_follow', NULL, $id, $u_id);
	}

	public function follow_cancel($id, $u_id){
		$id = intval($id);
		$u_id = intval($u_id);
		if($id < 1 || $u_id < 1){
			$this->throwMsg(-1);
		}
		if($id == $u_id){
			$this->throwMsg(-2);
		}
		try{
			User::getUser($id);
			User::getUser($u_id);
		} catch(\Exception $ex){
			$this->throwMsg(-5);
		}
		if(!$this->db->has("users_follow_users", [
			'AND' => [
				'users_id' => $u_id,
				'follow_users_id' => $id
			]
		])
		){
			$this->throwMsg(-7);
		}
		$status = $this->db->delete("users_follow_users", [
			'AND' => [
				'users_id' => $u_id,
				'follow_users_id' => $id
			]
		]);
		if($status === false){
			Log::write(___("follow message delete error."), Log::SQL);
			$this->throwMsg(-14);
		}
		if($status < 1){
			Log::write(___("follow message can not be delete."), Log::ERR);
			$this->throwMsg(-8);
		}
		hook()->apply('FollowManagement_follow_cancel', NULL, $id, $u_id);
	}

	/**
	 * 设置分页信息
	 * @param int $page
	 * @param int $number
	 */
	public function setPager($page, $number = 20){
		$page = intval($page);
		$number = intval($number);
		$this->count['page'] = $page > 0 ? $page : 1;
		$this->count['number'] = $number > 5 ? ($number > 100 ? 100 : $number) : 5;
	}

	/**
	 * 获取关注的统计信息
	 * @param int $id      用户ID
	 * @param int $flag_id 关注类型，为1时表示我关注的，为2时表示关注我的，为3时表示互相关注的,4为关注的图集
	 * @return bool 是否能获取到当前页面
	 */
	private function getCountInfo($id, $flag_id){
		$count = 0;
		switch($flag_id){
			case 1:
				//我关注的
				$count = $this->db->count("users_follow_users", ['users_id' => $id]);
				break;
			case 2:
				//关注我的
				$count = $this->db->count("users_follow_users", ['follow_users_id' => $id]);
				break;
			case 3:
				//互相关注的
				$q_id = $this->db->quote($id);
				$sql = <<<EOM
SELECT count(*) FROM `users_follow_users` as `u1`
inner join `users_follow_users` as `u2`
on `u1`.`follow_users_id` = `u2`.`users_id` AND `u2`.`follow_users_id`=`u1`.`users_id`
where `u1`.`users_id`={$q_id};";
EOM;

				$stmt = $this->db->query($sql);
				if($stmt === false){
					Log::write(___("Count follow message error"), Log::SQL);
				} else{
					$count = +$stmt->fetchColumn();
				}
				break;
			case 4:
				$count = $this->db->count("users_follow_gallery", ['users_id' => $id]);
				break;
			default:
				Log::write("\$flag_id=$flag_id is error. On " . __FILE__ . ":" . __LINE__);
				$this->throwMsg(-6);
		}
		$this->count['count'] = $count;
		$this->count['max'] = intval(ceil($count / $this->count['number']));
		if($this->count['page'] > $this->count['max']){
			$this->count['page'] = -1;
			return false;
		}
		return true;
	}

	/**
	 * 获取分页信息
	 * @return \int[]
	 */
	public function getCount(){
		return $this->count;
	}


	/**
	 * 获取我关注的用户
	 * @param int $id
	 * @return array
	 */
	public function getMeFollow($id){
		$id = intval($id);
		if(!$this->getCountInfo($id, 1)){
			return [];
		}
		$rt = $this->db->select("users_follow_users", ['[><]users' => ['follow_users_id' => 'id']], [
			'users_follow_users.users_id' => 'follow_user',
			'users_follow_users.follow_time' => 'follow_time',
			'users_follow_users.follow_update' => 'follow_update',
			'users_follow_users.follow_update_time' => 'follow_update_time',
			'users.id' => 'user_id',
			'users.user_name' => 'user_name',
			'users.user_aliases' => 'user_aliases',
			'users.user_email' => 'user_email',
			'users.user_url' => 'user_url',
			'users.user_status' => 'user_status',
			'users.user_registered_time' => 'user_registered_time',
			'users.user_registered_ip' => 'user_registered_ip',
			'users.user_last_login_time' => 'user_last_login_time',
			'users.user_last_login_ip' => 'user_last_login_ip',
			'users.user_error_login_count' => 'user_error_login_count',
			'users.user_error_login_ip' => 'user_error_login_ip',
			'users.user_error_login_time' => 'user_error_login_time',
			'users.user_cookie_salt' => 'user_cookie_salt',
			'users.user_cookie_login' => 'user_cookie_login',
			'users.user_avatar' => 'user_avatar'
		], [
			'users_follow_users.users_id' => $id,
			'LIMIT' => [
				$this->count['number'] * ($this->count['page'] - 1),
				$this->count['number']
			],
			'ORDER' => "users_follow_users.follow_time DESC"
		]);
		if($rt === false){
			Log::write(___("Get me follow users list error on sql."), Log::SQL);
		}
		return $this->parse_data($rt);
	}

	/**
	 * 获取关注我的用户
	 * @param int $id
	 * @return array
	 */
	public function getFollowMe($id){
		$id = intval($id);
		if(!$this->getCountInfo($id, 2)){
			return [];
		}
		$rt = $this->db->select("users_follow_users", ['[><]users' => ['users_id' => 'id']], [
			'users_follow_users.users_id' => 'follow_user',
			'users_follow_users.follow_time' => 'follow_time',
			'users_follow_users.follow_update' => 'follow_update',
			'users_follow_users.follow_update_time' => 'follow_update_time',
			'users.id' => 'user_id',
			'users.user_name' => 'user_name',
			'users.user_aliases' => 'user_aliases',
			'users.user_email' => 'user_email',
			'users.user_url' => 'user_url',
			'users.user_status' => 'user_status',
			'users.user_registered_time' => 'user_registered_time',
			'users.user_registered_ip' => 'user_registered_ip',
			'users.user_last_login_time' => 'user_last_login_time',
			'users.user_last_login_ip' => 'user_last_login_ip',
			'users.user_error_login_count' => 'user_error_login_count',
			'users.user_error_login_ip' => 'user_error_login_ip',
			'users.user_error_login_time' => 'user_error_login_time',
			'users.user_cookie_salt' => 'user_cookie_salt',
			'users.user_cookie_login' => 'user_cookie_login',
			'users.user_avatar' => 'user_avatar'
		], [
			'users_follow_users.follow_users_id' => $id,
			'LIMIT' => [
				$this->count['number'] * ($this->count['page'] - 1),
				$this->count['number']
			],
			'ORDER' => "users_follow_users.follow_time DESC"
		]);
		if($rt === false){
			Log::write(___("Get follow me users list error on sql."), Log::SQL);
		}
		return $this->parse_data($rt);
	}

	public function getMutualFollow($id){
		$id = intval($id);
		if(!$this->getCountInfo($id, 3)){
			return [];
		}
		$q_id = $this->db->quote($id);
		$b = $this->count['number'] * ($this->count['page'] - 1);
		$sql = <<< EOM
select
`me`.`users_id` as `me_follow_user`,
`me`.`follow_time` as `me_follow_time`,
`me`.`follow_update` as `me_follow_update`,
`me`.`follow_update_time` as `me_follow_update_time`,
`ta`.`users_id` as `ta_follow_user`,
`ta`.`follow_time` as `ta_follow_time`,
`ta`.`follow_update` as `ta_follow_update`,
`ta`.`follow_update_time` as `ta_follow_update_time`,
`users`.`id` as `user_id`,
`users`.`user_name` as `user_name`,
`users`.`user_aliases` as `user_aliases`,
`users`.`user_email` as `user_email`,
`users`.`user_url` as `user_url`,
`users`.`user_status` as `user_status`,
`users`.`user_registered_time` as `user_registered_time`,
`users`.`user_registered_ip` as `user_registered_ip`,
`users`.`user_last_login_time` as `user_last_login_time`,
`users`.`user_last_login_ip` as `user_last_login_ip`,
`users`.`user_error_login_count` as `user_error_login_count`,
`users`.`user_error_login_ip` as `user_error_login_ip`,
`users`.`user_error_login_time` as `user_error_login_time`,
`users`.`user_cookie_salt` as `user_cookie_salt`,
`users`.`user_cookie_login` as `user_cookie_login`,
`users`.`user_avatar` as `user_avatar`
from `users_follow_users` as `me`
inner join `users_follow_users` as `ta`
on `me`.`follow_users_id` = `ta`.`users_id` AND `ta`.`follow_users_id`=`me`.`users_id`
inner join `users`
on `ta`.`users_id`=`users`.`id`
where `me`.`users_id`={$q_id}
ORDER BY `me`.`follow_time` DESC
LIMIT {$b}, {$this->count['number']};
EOM;
		$stmt = $this->db->getReader()->query($sql);
		if($stmt === false){
			Log::write(___("Get mutual follow users list error on sql."), Log::SQL);
		}
		$rt = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		unset($stmt);
		return $this->parse_data($rt);
	}

	public function follow_gallery($gid, $uid){
		$gid = intval($gid);
		$uid = intval($uid);
		$s = $this->db->select("gallery", [
			'gallery.id' => 'gallery_id',
			'gallery.users_id' => 'gallery_users_id',
			'gallery.gallery_title' => 'gallery_title',
			'gallery.gallery_description' => 'gallery_description',
			'gallery.gallery_create_time' => 'gallery_create_time',
			'gallery.gallery_update_time' => 'gallery_update_time',
			'gallery.gallery_like_count' => 'gallery_like_count',
			'gallery.gallery_comment_count' => 'gallery_comment_count',
			'gallery.gallery_comment_status' => 'gallery_comment_status',
			'gallery.gallery_front_cover' => 'gallery_front_cover',
		], ['id' => $gid]);
		if($s === false){
			Log::write(___("Follow gallery sql error."), Log::SQL);
			$this->throwMsg(-7);
		}
		if(!isset($s[0]['gallery_users_id']) || $s[0]['gallery_users_id'] < 1){
			$this->throwMsg(-8);
		}
		if(intval($s[0]['gallery_users_id']) === $uid){
			$this->throwMsg(-9);
		}
		$g_uid = intval($s[0]['gallery_users_id']);
		if($this->db->has("users_follow_gallery", [
			'AND' => [
				'users_id' => $uid,
				'gallery_id' => $gid
			]
		])
		){
			$this->throwMsg(-10);
		}
		$status = $this->db->insert("users_follow_gallery", [
			'users_id' => $uid,
			'gallery_id' => $gid,
			'follow_time' => date("Y-m-d H:i:s"),
			'follow_update' => 0,
			'follow_update_time' => date("Y-m-d H:i:s")
		]);
		if($status < 0){
			Log::write(___("insert follow gallery error"), Log::SQL);
			$this->throwMsg(-7);
		}
		hook()->apply('FollowManagement_follow_gallery', NULL, $gid, $g_uid, $uid, $s[0]);
	}

	/**
	 * 取消对图集的关注
	 * @param $gid
	 * @param $uid
	 */
	public function follow_gallery_cancel($gid, $uid){
		$gid = intval($gid);
		$uid = intval($uid);
		if(!$this->db->has("users_follow_gallery", [
			'AND' => [
				'users_id' => $uid,
				'gallery_id' => $gid
			]
		])
		){
			$this->throwMsg(-11);
		} else{
			$del = $this->db->delete("users_follow_gallery", [
				'AND' => [
					'users_id' => $uid,
					'gallery_id' => $gid
				]
			]);
			if($del === false){
				Log::write(___("Delete gallery follow data error."));
				$this->throwMsg(-12);
			}
			if($del < 1){
				$this->throwMsg(-13);
			}
		}
		hook()->apply('FollowManagement_follow_gallery_cancel', NULL, $gid, $uid);
	}

	public function getFollowGallery($user_id){
		$user_id = intval($user_id);
		if(!$this->getCountInfo($user_id, 4)){
			return [];
		}
		$sd = $this->db->select("gallery", [
			"[><]users_follow_gallery" => ['id' => 'gallery_id'],
			"[><]users" => ['users_id' => "id"]
		], [
			'users_follow_gallery.users_id' => 'follow_user',
			'users_follow_gallery.follow_time' => 'follow_time',
			'users_follow_gallery.follow_update' => 'follow_update',
			'users_follow_gallery.follow_update_time' => 'follow_update_time',
			'gallery.gallery_title' => 'gallery_title',
			'gallery.id' => 'gallery_id',
			'gallery.gallery_description' => 'gallery_description',
			'gallery.gallery_create_time' => 'gallery_create_time',
			'gallery.gallery_update_time' => 'gallery_update_time',
			'gallery.gallery_like_count' => 'gallery_like_count',
			'gallery.gallery_comment_count' => 'gallery_comment_count',
			'gallery.gallery_comment_status' => 'gallery_comment_status',
			'gallery.gallery_front_cover' => 'gallery_front_cover',
			'users.id' => 'user_id',
			'users.user_name' => 'user_name',
			'users.user_aliases' => 'user_aliases',
			'users.user_email' => 'user_email',
			'users.user_url' => 'user_url',
			'users.user_status' => 'user_status',
			'users.user_registered_time' => 'user_registered_time',
			'users.user_registered_ip' => 'user_registered_ip',
			'users.user_last_login_time' => 'user_last_login_time',
			'users.user_last_login_ip' => 'user_last_login_ip',
			'users.user_error_login_count' => 'user_error_login_count',
			'users.user_error_login_ip' => 'user_error_login_ip',
			'users.user_error_login_time' => 'user_error_login_time',
			'users.user_cookie_salt' => 'user_cookie_salt',
			'users.user_cookie_login' => 'user_cookie_login',
			'users.user_avatar' => 'user_avatar'
		], ["users_follow_gallery.users_id" => $user_id]);
		if($sd === false){
			Log::write(___("Get user follow gallery error!"), Log::SQL);
			return [];
		}
		return $this->parse_data($sd);
	}

	/**
	 * 解析数据并返回
	 * @param array $data
	 * @return array
	 */
	private function  parse_data($data){
		$rt = [];
		foreach($data as &$v){
			$u = [];
			$f = [];
			foreach($v as $key => &$value){
				$index = strpos($key, "_");
				switch($index){
					case 4:
						$u[substr($key, 5)] = $value;
						break;
					default:
						$f[$key] = $value;
						break;
				}
			}
			if(count($u) === 16){
				$user = User::UserStack(+$u['id']);
				if(!is_object($user)){
					$user = new User($u, true);
				}
				$rt[] = [
					'user' => $user,
					'follow' => $f
				];
			}
		}
		return $rt;
	}
	/**
	 * 获取异常信息
	 * @param int $code
	 * @return string
	 */
	public function getMsg($code){
		$code = intval($code);
		switch($code){
			case -1:
				return ___("User id error . ");
			case -2:
				return ___("Can't follow yourself.");
			case -3:
				return ___("Follow user error.");
			case -4:
				return ___("You've already attention to this user . ");
			case -5:
				return ___("Follow user not found!");
			case -6:
				return ___("Follow count info flag error . ");
			case -7:
				return ___("Follow gallery error . ");
			case -8:
				return ___("Follow gallery is not exists");
			case -9:
				return ___("You can't follow yourself gallery.");
			case -10:
				return ___("You have already attention to this gallery.");
			case -11:
				return ___("The gallery concern does not exists.");
			case -12:
				return ___("Delete gallery follow data error.");
			case -13:
				return ___("Delete gallery error.");
			case -14:
				return ___("Cancel follow user error.");
		}
		return ___("Unknown error.");
	}
} 