<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-23
 * Time: 下午1:51
 * Filename: CountMessage.php
 */

namespace ULib;


use Core\Log;

class CountMessage{
	/**
	 * @var \CLib\Sql
	 */
	private $db;

	private $type_list = [
		'posts',
		'gallery',
		'pictures'
	];

	private $count_info = [];

	function __construct(){
		$this->db = db();
		lib()->add('CountMessage', $this);
	}

	/**
	 * 获取用户统计信息
	 * @param User $user
	 * @return array|false
	 */
	public function getUserCount($user){
		if(!isset($this->count_info[$user->getId()])){
			$rt = $this->db->get("user_count", [
				'picture_count',
				'gallery_count',
				'comment_count',
				'user_follow_count',
				'user_fans_count',
				'like_gallery_count',
				'like_picture_count',
				'like_comment_count',
				'follow_gallery_count',
				'unread_message_count'
			], ['users_id' => $user->getId()]);
			if($rt === false){
				Log::write(___("Get user count info error.") . "USER ID:" . $user->getId(), Log::SQL);
				return false;
			}
			$this->count_info[$user->getId()] = & $rt;
		}
		return $this->count_info[$user->getId()];
	}


	public function getUnreadMessage($user_id){
		$user_id = intval($user_id);
		if(!isset($this->count_info[$user_id]['unread_message_count'])){
			$this->getUserCount(User::getUser($user_id));
		}
		return isset($this->count_info[$user_id]['unread_message_count']) ? $this->count_info[$user_id]['unread_message_count'] : 0;
	}

	/**
	 * @return array
	 */
	public function getTypeList(){
		return $this->type_list;
	}

	/**
	 * 添加一个访问计数器
	 * 返回当前访问次数或者FALSE
	 * @param string $type
	 * @param int    $id
	 * @return int|false
	 */

	public function addCount($type, $id){
		if(!in_array($type, $this->type_list)){
			return false;
		}
		if(!$this->db->has($type, ['id' => $id])){
			return false;
		}
		$count = $this->db->select($type . "_views", ['views_count'], [
			'AND' => [
				$type . "_id" => $id,
				'views_date' => date("Y-m-d")
			],
			'LIMIT' => 1
		]);
		if($count === false){
			Log::write(___("Count info get error."), Log::SQL);
			return false;
		}
		if(isset($count[0]['views_count'])){
			$status = $this->db->update($type . "_views", ['views_count[+]' => 1], [
				'AND' => [
					$type . "_id" => $id,
					'views_date' => date("Y-m-d")
				]
			]);
			if($status === false){
				Log::write(___("Count info update error."), Log::SQL);
				return false;
			}
			if($status > 0){
				return $count[0]['views_count'] + 1;
			}
		} else{
			$status = $this->db->insert($type . "_views", [
				$type . "_id" => $id,
				'views_date' => date("Y-m-d"),
				'views_count' => 1
			]);
			if($status !== -1){
				return 1;
			} else{
				Log::write(___("Count info insert error."), Log::SQL);
			}
		}
		return false;
	}
} 