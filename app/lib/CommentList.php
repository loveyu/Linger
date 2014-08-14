<?php
/**
 * User: loveyu
 * Date: 14-4-3
 * Time: 下午10:05
 */

namespace ULib;

use Core\Log;

if(!class_exists('CommentManagement')){
	lib()->load('CommentManagement');
}

/**
 * Class CommentList
 * @package ULib
 */
class CommentList extends AppException{
	/**
	 * 设置为用户某用户关联的评论信息，相当于给用户的评论
	 */
	const TYPE_Object_user = 1;
	/**
	 * 设置用户在该表中的评论
	 */
	const TYPE_Comment_user = 2;

	/**
	 * 评论管理类
	 * @var CommentManagement
	 */
	private $cm;
	/**
	 * 表名称
	 * @var string
	 */
	private $table;
	/**
	 * 数据表名称以及数据类型
	 * @var
	 */
	private $type;
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
	 * 根据某一类型来初始化
	 * @param string $type
	 */
	public function __construct($type){
		$this->cm = new CommentManagement();
		$this->table = $this->cm->getTable($type);
		if(empty($this->table)){
			$this->throwMsg(-1);
		}
		$this->type = $type;
		$this->db = db();
	}

	/**
	 * 获取异常信息
	 * @param int $code
	 * @return string
	 */
	public function getMsg($code){
		switch($code){
			case -1:
				return _("CommentList class construct error.");
			case -2:
				return _("Data get error.");
		}
		return _("Unknown error.");
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
	 * @return \int[]
	 */
	public function getCount(){
		return $this->count;
	}

	/**
	 * 设置评论类型的分页信息
	 * @param int $type
	 * @param int $id
	 * @return bool
	 */
	private function setCount($type, $id){
		$count = 0;
		switch($type){
			case self::TYPE_Comment_user:
				$count = $this->db->count($this->table, ['users_id' => $id]);
				break;
			case self::TYPE_Object_user:
				$count = $this->db->count($this->table, ['object_users_id' => $id]);
				break;
		}
		if($count < 1){
			return false;
		} else{
			$this->count['count'] = $count;
			$this->count['max'] = intval(ceil($count / $this->count['number']));
			if($this->count['page'] > $this->count['max']){
				$this->count['page'] = -1;
				return false;
			}
		}
		return true;
	}

	/**
	 * 根据用户获取对他的项目上的对应评论
	 * @param int $uid
	 * @return array
	 */
	public function getListOfUserOnObject($uid){
		if(!$this->setCount(self::TYPE_Object_user, $uid)){
			return [];
		}
		$list = $this->db->select($this->table, [
			'[><]comments' => ['comments_id' => 'id'],
			'[><]users' => ['users_id' => 'id'],
		], [
			$this->table . "." . $this->type . "_id" => $this->type . "_id",
			$this->table . ".comments_id" => "comments_id",
			$this->table . ".object_users_id" => "object_users_id",
			$this->table . '.users_id' => 'users_id',
			'users.id' => 'user_id',
			'users.user_name' => 'user_name',
			'users.user_aliases' => 'user_aliases',
			'users.user_email' => 'user_email',
			'users.user_url' => 'user_url',
			'users.user_status' => 'user_status',
			'users.user_registered_time' => 'user_registered_time',
			'users.user_last_login_time' => 'user_last_login_time',
			'users.user_avatar' => 'user_avatar',
			'comments.comment_content' => 'comment_content',
			'comments.comment_time' => 'comment_time'
		], [
			$this->table . '.object_users_id' => $uid,
			'LIMIT' => [
				($this->count['page'] - 1) * $this->count['number'],
				$this->count['number']
			],
			'ORDER' => $this->table . ".comments_id DESC",
		]);
		$this->getListDataParse($list);
		for($i = 0, $l = count($list); $i < $l; $i++){
			$user_info = [];
			foreach($list[$i] as $k => $v){
				if(strpos($k, "user_") === 0){
					$user_info[substr($k, 5)] = $v;
				}
			}
			//从堆栈获取用户
			$list[$i]['user_object'] = User::UserStack(+$user_info['id']);
			if(!is_object($list[$i]['user_object'])){
				$list[$i]['user_object'] = new User($user_info, true);
			}
		}
		return $list;
	}

	/**
	 * @param array $list
	 */
	private function getListDataParse(&$list){
		if($list === false){
			Log::write(_("Get comment list of user error."), Log::SQL);
			$this->throwMsg(-2);
		}
		$ol = [];
		for($i = 0, $l = count($list); $i < $l; ++$i){
			$ol[$list[$i][$this->type . "_id"]] = $list[$i][$this->type . "_id"];
		}
		$info = $this->cm->getCommentTypeInfo($this->type, $ol);
		for($i = 0, $l = count($list); $i < $l; ++$i){
			if(isset($info[$list[$i][$this->type . "_id"]])){
				$list[$i]['info'] = & $info[$list[$i][$this->type . "_id"]];
			} else{
				//此处数据出错，或者数据不完整
				Log::write(_("Data loss on CommentList class."), Log::NOTICE);
				unset($list[$i]);
			}
		}
	}

	/**
	 * 获取用户的评论列表
	 * @param int $uid
	 * @return array
	 */
	public function getListOfUser($uid){
		//仅仅查询评论内容，评论时间，对象名称，对象地址
		if(!$this->setCount(self::TYPE_Comment_user, $uid)){
			return [];
		}
		$list = $this->db->select($this->table, ['[><]comments' => ['comments_id' => 'id']], [
			$this->table . "." . $this->type . "_id" => $this->type . "_id",
			$this->table . ".comments_id" => "comments_id",
			$this->table . ".object_users_id" => "object_users_id",
			$this->table . '.users_id' => 'users_id',
			'comments.comment_content' => 'comment_content',
			'comments.comment_time' => 'comment_time'
		], [
			$this->table . '.users_id' => $uid,
			'LIMIT' => [
				($this->count['page'] - 1) * $this->count['number'],
				$this->count['number']
			]
		]);
		$this->getListDataParse($list);
		return $list;
	}
} 