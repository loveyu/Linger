<?php
/**
 * User: Loveyu
 * Date: 14-4-8
 * Time: 下午4:00
 */

namespace ULib;

use Core\Log;


/**
 * Class FeedManagement
 * @package ULib
 */
class FeedManagement extends AppException{
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
	 * @param int         $id
	 * @param null|string $type
	 * @return bool
	 */
	private function getCountInfo($id, $type = NULL){
		$count = 0;
		switch($type){
			case NULL:
				$count = $this->db->count("feed", ['users_id' => $id]);
				break;
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
	 * 获取全部列表
	 * @param int $uid
	 * @return FeedInterface[]
	 */
	public function getList($uid){
		$uid = (int)$uid;
		if(!$this->getCountInfo($uid)){
			return [];
		}
		$data = $this->db->select("feed", [
			'id',
			'time',
			'action',
			'content'
		], [
			'users_id' => $uid,
			'LIMIT' => [
				$this->count['number'] * ($this->count['page'] - 1),
				$this->count['number']
			],
			'ORDER' => 'id DESC'
		]);
		if($data === false){
			Log::write(_("Get feed list error."), Log::SQL);
			return [];
		}
		return Feed::getInstance()->parseList($data);
	}

	public function delete($id, $uid){
		$id = +$id;
		$uid = +$uid;
		if(!$this->db->has("feed", [
			'AND' => [
				'id' => $id,
				'users_id' => $uid
			]
		])
		){
			$this->throwMsg(-1);
		}
		$status = $this->db->delete("feed", [
			'AND' => [
				'id' => $id,
				'users_id' => $uid
			]
		]);
		if($status === false){
			Log::write(_("Delete feed error."), Log::SQL);
			$this->throwMsg(-2);
		}
		if($status != 1){
			$this->throwMsg(-3);
		}
	}

	/**
	 * @return \int[]
	 */
	public function getCount(){
		return $this->count;
	}

	/**
	 * 获取异常信息
	 * @param int $code
	 * @return mixed
	 */
	public function getMsg($code){
		switch($code){
			case -1:
				return _("No found this feed.");
			case -2:
				return _("Delete error, please try later.");
			case -3:
				return _("No delete any data, please try later.");
		}
		return _("Unknown error.");
	}


}