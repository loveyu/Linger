<?php
/**
 * User: Loveyu
 * Date: 14-4-6
 * Time: 下午2:28
 */

namespace ULib;

use CLib\Sql;
use Core\Log;

lib()->load('Feed.class');

class Feed extends AppException{
	private static $instance = NULL;
	private $db;

	private function __construct(){
		$this->db = db();
	}

	public static function getInstance(){
		if(self::$instance === NULL){
			self::$instance = new Feed();
		}
		return self::$instance;
	}

	/**
	 * 获取动态数据
	 * @param int $user_id
	 * @param int $number
	 * @param int $begin_id
	 * @return FeedInterface[]
	 */
	public function getList($user_id, $begin_id = 0, $number = 15){
		$user_id = +$user_id;
		$number = +$number;
		if($number < 5){
			$number = 5;
		} else if($number > 50){
			$number = 50;
		}
		$begin_id = +$begin_id;
		$q_id = $this->db->quote($user_id);
		if($begin_id === 0){
			$sql = <<<SQL_INPUT
Select `id`,`action`,`content`,`time`,`users_id` from `feed`
where (`users_id` in (
	select `follow_users_id` from `users_follow_users` where `users_id`={$q_id}
) or `users_id` = {$q_id}) ORDER by `id` DESC LIMIT 0,{$number};
SQL_INPUT;
		} else if($begin_id > 0){
			//获取之前的内容
			$q_b = $this->db->quote($begin_id);
			$sql = <<<SQL_INPUT
Select `id`,`action`,`content`,`time`,`users_id` from `feed`
where `id`<{$q_b} AND (`users_id` in (
	select `follow_users_id` from `users_follow_users` where `users_id`={$q_id}
) or `users_id` = {$q_id}) ORDER by `id` DESC LIMIT 0,{$number};
SQL_INPUT;
		} else{
			//获取之后的内容
			$q_b = $this->db->quote(abs($begin_id));
			$sql = <<<SQL_INPUT
Select `id`,`action`,`content`,`time`,`users_id` from `feed`
where `id`>{$q_b} AND (`users_id` in (
	select `follow_users_id` from `users_follow_users` where `users_id`={$q_id}
) or `users_id` = {$q_id}) ORDER by `id` DESC LIMIT 0,{$number};
SQL_INPUT;
		}
		$stmt = $this->db->query($sql);
		if($stmt === false){
			Log::write(___("Get feed list error."), Log::SQL);
			return [];
		}
		$data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		unset($stmt);
		return $this->parseList($data);
	}

	/**
	 * @param array $data
	 * @return FeedInterface[]
	 */
	public function parseList($data){
		/**
		 * @var FeedInterface[] $rt
		 */
		$rt = [];
		$server = [];
		$user_id = [];
		foreach($data as &$v){
			/**
			 * @var FeedInterface $obj
			 */
			$obj = @unserialize($v['content']);
			$obj->setTime($v['time']);
			if(!is_object($obj)){
				continue;
			}
			$u = $obj->getUserId();
			if($u < 1){
				continue;
			} else if(!isset($user_id[$u])){
				$user_id[$u] = $u;
			}
			$ov = $obj->getObjUserId();
			if(!isset($user_id[$ov])){
				$user_id[$ov] = $ov;
			}
			$s = $obj->getServer();
			if(!empty($s) && !isset($server[$s])){
				$server[$s] = $s;
			}
			$rt[$v['id']] = $obj;
		}
		unset($s);
		$server = $this->getServer($server);
		$this->initUser($user_id);
		foreach($rt as &$obj){
			$obj->setServer($server);
		}
		return $rt;
	}

	/**
	 * @param $list
	 * @return array
	 */
	private function getServer($list){
		if(count($list) < 1){
			return [];
		}
		lib()->load('Server');
		$s = new Server();
		$list = $s->get($list);
		$rt = [];
		if(isset($list[0]['name'])){
			foreach($list as &$v){
				$rt[$v['name']] = $v;
			}
		}
		return $rt;
	}

	private function initUser($ids){
		$idl = [];
		foreach($ids as $v){
			if(!User::UserStack($v)){
				$idl[$v] = $v;
			}
		}
		unset($ids);
		if(reset($idl)){
			$list = $this->db->select("users", [
				'id' => 'id',
				'user_name' => 'name',
				'user_aliases' => 'aliases',
				'user_email' => 'email',
				'user_url' => 'url',
				'user_status' => 'status',
				'user_registered_time' => 'registered_time',
				'user_last_login_time' => 'last_login_time',
				'user_avatar' => 'avatar',
			], ['id' => $idl]);
			if($list === false){
				Log::write(___("Select other users error."), Log::SQL);
			} else{
				foreach($list as &$v){
					//保存数据到堆栈
					new User($v, true);
				}
			}
		}
	}

	public function addHook(){
		$hook = hook();
		$hook->add('Gallery_set_public', [
			$this,
			'addGalleryFeed'
		]);
		$hook->add("Gallery_delete", [
			$this,
			'deleteGalleryFeed'
		]);
		$hook->add("Picture_delete", [
			$this,
			'deletePictureFeed'
		]);
		$hook->add("Gallery_set_draft", [
			$this,
			'cancelGalleryFeed'
		]);
		$hook->add("Gallery_updated", [
			$this,
			'addGalleryFeed'
		]);
	}

	/**
	 * 取消图集分享
	 * @param $rt
	 * @param $gid
	 * @param $uid
	 * @return mixed
	 */
	public function cancelGalleryFeed($rt, $gid, $uid){
		$rt = $this->db->delete("feed", [
			'sid' => [
				'FeedGallery_' . $gid,
				'ShareGallery_' . $gid
			]
		]);
		if($rt === false){
			Log::write("cancelGalleryFeed error", Log::SQL);
		}
	}

	/**
	 * 删除图集分享
	 * @param bool   $rt
	 * @param int[]  $ids
	 * @param \medoo $write_db
	 * @return bool
	 */
	public function deleteGalleryFeed($rt, $ids, \medoo $write_db){
		if($rt !== true || count($ids) < 1){
			return $rt;
		}
		$sids = [];
		foreach($ids as $v){
			$sids[] = "FeedGallery_$v";
			$sids[] = "ShareGallery_$v";
		}
		$write_db->delete("feed", ['sid' => $sids]);
		if($write_db === false){
			Log::write("deleteGalleryFeed Error", Log::SQL);
			return false;
		}
		return true;
	}

	/**
	 * 删除图片
	 * @param bool   $rt
	 * @param int    $id
	 * @param int    $uid
	 * @param \medoo $write_db
	 * @return bool
	 */
	public function deletePictureFeed($rt, $id, $uid, \medoo $write_db){
		if($rt !== true || $id < 1 || $uid < 1){
			return $rt;
		}
		$write_db->delete("feed", ['sid' => "SharePicture_" . $id]);
		if($write_db === false){
			Log::write("deletePictureFeed Error", Log::SQL);
			return false;
		}
		return true;
	}

	/**
	 * @param mixed $rt
	 * @param array $data_list
	 * @return mixed
	 */
	public function addGalleryFeed($rt, $data_list){
		if(isset($data_list['gallery_id']) && isset($data_list['users_id'])){
			$obj = new FeedGallery($data_list['gallery_id'], $data_list['users_id'], $data_list['gallery_title'], $data_list['gallery_description'], $data_list['gallery_front_cover']);
			if($obj->initStatus()){
				if(($id = $this->insertData($obj)) !== false){
					$this->db->delete("feed", [
						'AND' => [
							'sid' => $obj->getSid(),
							'id[!]' => $id
						]
					]);
				}
			}
		}
		return $rt;
	}

	public function addPictureShare($rt, $pid, $uid){
		$obj = new FeedSharePicture($pid, $uid);
		if($obj->initStatus()){
			if(($id = $this->insertData($obj)) !== false){
				//删除重复分享
				$this->db->delete("feed", [
					'AND' => [
						'sid' => $obj->getSid(),
						'users_id' => $obj->getUserId(),
						'id[!]' => $id
					]
				]);
			} else{
				$this->throwMsg(-2);
			}
		} else{
			$this->throwMsg(-1);
		}
		return $rt;
	}

	public function addGalleryShare($rt, $gid, $uid){
		$obj = new FeedShareGallery($gid, $uid);
		if($obj->initStatus()){
			if(($id = $this->insertData($obj)) !== false){
				//删除重复分享
				$this->db->delete("feed", [
					'AND' => [
						'sid' => $obj->getSid(),
						'users_id' => $obj->getUserId(),
						'id[!]' => $id
					]
				]);
			} else{
				$this->throwMsg(-3);
			}
		} else{
			$this->throwMsg(-4);
		}
		return $rt;
	}

	public function addTalk($rt, $content, $uid){
		if(strlen($content) < 1){
			$this->throwMsg(-5);
		}
		$obj = new FeedTalk(htmlspecialchars($content, ENT_NOQUOTES), $uid);
		if($obj->initStatus()){
			if($this->insertData($obj) === false){
				$this->throwMsg(-7);
			}
		} else{
			$this->throwMsg(-6);
		}
		return $rt;
	}

	private function insertData(FeedInterface $object){
		$content = @serialize($object);
		$sid = $object->getSid();
		$action = $object->getAction();
		if(is_string($action)){
			$status = $this->db->insert('feed', [
				'action' => $action,
				'content' => $content,
				'sid' => $sid,
				'users_id' => $object->getUserId(),
				'time' => date("Y-m-d H:i:s")
			]);
			if($status < 1){
				Log::write(___("Insert feed error.") . "On " . $action, Log::SQL);
				return false;
			}
			return $status;
		}
		return false;
	}

	/**
	 * 获取图片的地址
	 * @param string $url  服务器地址
	 * @param string $path 路径
	 * @param string $dst  原图地址
	 * @return string
	 */
	public static function getPicOfPath($url, $path, $dst){
		if(strpos($path, '/') === false){
			return $dst . "/" . $path;
		}
		return $url . $path;
	}

	/**
	 * 获取异常信息
	 * @param int $code
	 * @return mixed
	 */
	public function getMsg($code){
		switch($code){
			case -1:
				return ___("Init share picture error.");
			case -2:
				return ___("Share picture data insert error.");
			case -3:
				return ___("Share gallery data insert error.");
			case -4:
				return ___("Init share gallery error.");
			case -5:
				return ___("Share content is empty.");
			case -6:
				return ___("Share talk data error.");
			case -7:
				return ___("Create share data error.");
		}
		return ___("Unknown error.");
	}

} 