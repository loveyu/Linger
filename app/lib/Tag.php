<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-2-26
 * Time: 下午9:51
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace ULib;

use Core\Log;

/**
 * Class Tag
 * @package ULib
 */
class Tag{
	/**
	 * @var \CLib\Sql
	 */
	private $db;

	/**
	 * 构造函数
	 */
	function __construct(){
		$this->db = db();
	}

	/**
	 * @param int    $pic_id
	 * @param string $tags
	 * @throws \Exception
	 */
	public function pic_set($pic_id, $tags){
		$list = $this->tag_exp($tags);
		$list = $this->analysisTags($list, $this->getPicTags($pic_id));
		$add = [];
		foreach($list['add'] as $v){
			$in = [];
			$in['tags_name'] = $v;
			$in['pictures_id'] = $pic_id;
			$add[] = $in;
		}
		if(count($add) > 0){
			array_unshift($add, "pictures_has_tags");
			if(call_user_func_array([
					$this->db,
					'insert'
				], $add) === false
			){
				$this->throwMsg(-6);
			}
		}
		if(count($list['del']) > 0){
			if($this->db->delete("pictures_has_tags", [
					'AND' => [
						'pictures_id' => $pic_id,
						'tags_name' => $list['del']
					]
				]) === false
			){
				$this->throwMsg(-5);
			}
		}
		//		foreach($list['add'] as $v){
		//			$out = 0;
		//			$stmt = $this->db->getWriter()->prepare("call `picture_add_tag`(?, ?, @out_status);");
		//			$stmt->bindParam(1, $v, \PDO::PARAM_STR, 100);
		//			$stmt->bindParam(2, $pic_id, \PDO::PARAM_INT);
		//			//$stmt->bindParam(3, $out, \PDO::PARAM_INPUT_OUTPUT | \PDO::PARAM_INT);//BUG on pdo mysql
		//			if(!$stmt->execute()){
		//				throw new \Exception(_("Call tag add error.") . debug(implode(", ", $stmt->errorInfo())), 1);
		//			} else{
		//				$stmt = $this->db->getWriter()->query("select @out_status;");
		//				$out = intval($stmt->fetchAll(\PDO::FETCH_ASSOC)[0]['@out_status']);
		//			}
		//			switch($out){
		//				case -1:
		//					$this->throwMsg(-1);
		//					break;
		//				case -2:
		//					$this->throwMsg(-2);
		//					break;
		//			}
		//		}
		//		foreach($list['del'] as $v){
		//			$out = 0;
		//			$stmt = $this->db->getWriter()->prepare("call picture_del_tag(?,?,@out_status);");
		//			$stmt->bindParam(1, $v, \PDO::PARAM_STR, 100);
		//			$stmt->bindParam(2, $pic_id, \PDO::PARAM_INT);
		//			//$stmt->bindParam(3, $out, \PDO::PARAM_INPUT_OUTPUT);//BUG on pdo mysql
		//			if(!$stmt->execute()){
		//				throw new \Exception(_("Call tag del error.") . debug(implode(", ", $stmt->errorInfo())), 1);
		//			} else{
		//				$stmt = $this->db->getWriter()->query("select @out_status;");
		//				$out = intval($stmt->fetchAll(\PDO::FETCH_ASSOC)[0]['@out_status']);
		//			}
		//			switch($out){
		//				case -1:
		//					$this->throwMsg(-1);
		//					break;
		//				case -2:
		//					$this->throwMsg(-3);
		//					break;
		//				case -3:
		//					$this->throwMsg(-4);
		//					break;
		//			}
		//		}
	}

	/**
	 * 获取热门标签列表
	 * @param array $limit
	 * @return array
	 */
	public function get_hot_tags($limit = array()){
		return $this->db->select("tags", "*", [
			'ORDER' => 'count DESC',
			'LIMIT' => $limit
		]);
	}

	/**
	 * 图集删除标签
	 * @param int    $id
	 * @param string $tag
	 * @param string $table
	 */
	public function del_tag($id, $tag, $table){
		if(!in_array($table, [
			'pictures',
			'gallery'
		])
		){
			$this->throwMsg(-7);
		}
		if(($flag = db()->delete($table . "_has_tags", [
				'AND' => [
					$table . '_id' => $id,
					'tags_name' => $tag
				]
			])) < 1
		){
			if($flag === false){
				Log::write(implode(', ', db()->error()['write']) . "\n" . implode(', ', db()->last_query()), Log::SQL);
				$this->throwMsg(-5);
			}
			$this->throwMsg(-4);
		}
	}

	/**
	 * 添加一个标签
	 * @param int    $pic_id
	 * @param string $tags
	 * @param string $table 标签表 pictures or gallery
	 * @return array|null
	 */
	public function add_tag($pic_id, $tags, $table){
		$list = $this->tag_exp($tags);
		if($table === "pictures"){
			$list = array_diff($list, $this->getPicTags($pic_id));
		} else if($table === "gallery"){
			$list = array_diff($list, $this->getGalleryTags($pic_id));
		} else{
			$this->throwMsg(-7);
		}
		$add = [];
		foreach($list as $v){
			$in = [];
			$in['tags_name'] = $v;
			$in[$table . '_id'] = $pic_id;
			$add[] = $in;
		}
		if(count($add) > 0){
			array_unshift($add, $table . "_has_tags");
			if(call_user_func_array([
					$this->db,
					'insert'
				], $add) === false
			){
				$this->throwMsg(-6);
			}
		} else{
			return NULL;
		}
		return $list;
	}

	/**
	 * 抛出异常信息
	 * @param $code
	 * @throws \Exception
	 */
	private function throwMsg($code){
		$code = intval($code);
		throw new \Exception($this->getMsg($code), $code);
	}

	/**
	 * 获取信息
	 * @param $code
	 * @return string
	 */
	public function getMsg($code){
		switch($code){
			case -1:
				return ___("Pic is not found.");
			case -2:
				return ___("Add pic tag error on tag is exists.");
			case -3:
				return ___("Pic not found this tag.");
			case -4:
				return ___("Tag is not found.");
			case -5:
				return ___("Delete tags error on sql.") . debug(implode(",", $this->db->error()['write']));
			case -6:
				return ___("Insert tags error on sql.") . debug(implode(",", $this->db->error()['write']));
			case -7:
				return ___("table not defined");
			default:
				return ___("Unknown Error");
		}
	}

	/**
	 * 分析数组，获取差值
	 * @param array $set
	 * @param array $get
	 * @return array
	 */
	private function analysisTags($set, $get){
		$rt = [
			'add' => [],
			'del' => []
		];
		$get = array_flip($get);
		foreach($set as $v){
			if(!isset($get[$v])){
				$rt['add'][] = $v;
			}
		}
		$set = array_flip($set);
		foreach($get as $k => $v){
			if(!isset($set[$k])){
				$rt['del'][] = $k;
			}
		}
		return $rt;
	}

	/**
	 * 获取图片的TAG
	 * @param int $id
	 * @return array
	 */
	public function getPicTags($id){
		$list = $this->db->select("pictures_has_tags", ['tags_name'], ['pictures_id' => $id]);
		$rt = [];
		foreach($list as $v){
			$rt[] = $v['tags_name'];
		}
		return $rt;
	}

	/**
	 * 获取图集的TAG
	 * @param int $id
	 * @return array
	 */
	public function getGalleryTags($id){
		$list = $this->db->select("gallery_has_tags", ['tags_name'], ['gallery_id' => $id]);
		$rt = [];
		foreach($list as $v){
			$rt[] = $v['tags_name'];
		}
		return $rt;
	}

	/**
	 * 分割TAG
	 * @param string $tags
	 * @return array
	 */
	public function tag_exp($tags){
		$tags = str_replace([
			'“',
			'”',
			'‘',
			'’',
			'，',
			'、',
			'。',
			'【',
			'】',
			'；',
			''
		], ",", $tags);
		$list = array_flip(array_flip(preg_split("/[,\[\]<>{}'|\\\\\/()#!]/", $tags)));
		$rt = [];
		foreach($list as $v){
			$t = trim($v);
			if($t !== ''){
				$rt[] = $t;
			}
		}
		return $rt;
	}
} 