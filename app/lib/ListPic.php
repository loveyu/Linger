<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-7
 * Time: 下午12:25
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace ULib;


use Core\Log;

/**
 * 图片选择类
 * Class ListPic
 * @package ULib
 */
class ListPic{
	/**
	 * 用户的ID
	 * @var int
	 */
	private $user_id = 0;
	/**
	 * 当前页面
	 * @var int
	 */
	private $page = 1;
	/**
	 * 每页显示数量
	 * @var int
	 */
	private $number = 50;
	/**
	 * @var null
	 */
	private $tag = NULL;
	/**
	 * 是否
	 * @var bool
	 */
	private $tag_like = false;
	/**
	 * 排序方式
	 * @var string
	 */
	private $order = 'pictures.id';
	/**
	 * 排序类型
	 * @var string
	 */
	private $order_type = 'DESC';
	/**
	 * 开始日期
	 * @var string
	 */
	private $date_begin = "";
	/**
	 * 结束日期
	 * @var string
	 */
	private $date_end = "";

	/**
	 * 设置用户ID
	 * @param int $id
	 * @return $this
	 */
	public function setUser($id){
		$this->user_id = intval($id);
		if($this->user_id < 0){
			$this->throwMsg(-1);
		}
		return $this;
	}

	/**
	 * 设置当前页面
	 * @param int $page
	 * @return $this
	 */
	public function setPage($page = 1){
		$this->page = intval($page);
		if($this->page < 1){
			$this->page = 1;
		}
		return $this;
	}

	/**
	 * 设置每页数量
	 * @param int $number
	 * @return $this
	 */
	public function setLimit($number = 50){
		$number = intval($number);
		if($number !== 0){
			if($number < 5){
				$number = 5;
			}
			$this->number = $number;
		}
		return $this;
	}

	/**
	 * 设置是否搜索标签
	 * @param null $tag
	 * @return $this
	 */
	public function setTag($tag = NULL){
		$this->tag = trim($tag);
		return $this;
	}

	/**
	 * 设置开始时间
	 * @param string $datetime
	 * @return $this
	 */
	public function setDateBegin($datetime){
		if(!empty($datetime)){
			$time = strtotime($datetime);
			if($time > 0){
				$this->date_begin = date("Y-m-d H:i:s", $time);
			}
		}
		return $this;
	}

	/**
	 * 设置结束时间
	 * @param string $datetime
	 * @return $this
	 */
	public function setDateEnd($datetime){
		if(!empty($datetime)){
			$time = strtotime($datetime);
			if($time > 0){
				$this->date_end = date("Y-m-d H:i:s", $time);
			}
		}
		return $this;
	}

	/**
	 * 设置标签是否使用模糊匹配模式
	 * @param bool $flag
	 * @return $this
	 */
	public function setTagModeIsLike($flag = false){
		if($flag){
			$this->tag_like = true;
		} else{
			$this->tag_like = false;
		}
		return $this;
	}

	/**
	 * 是否使用倒排序
	 * @param bool $DESC
	 * @return $this
	 */
	public function order_type($DESC = true){
		if($DESC){
			$this->order_type = 'DESC';
		} else{
			$this->order_type = "ASC";
		}
		return $this;
	}

	/**
	 * 使用创建时间来排序
	 * @return $this
	 */
	public function order_by_pic_create_time(){
		$this->order = 'pictures.pic_create_time';
		return $this;
	}

	/**
	 * 获取数据，并将参数重置
	 * @return array
	 */
	public function get(){
		$and = [];
		if($this->user_id > 0){
			$and['pictures.users_id'] = $this->user_id;
		}
		if(!empty($this->tag)){
			if($this->tag_like){
				$and['LIKE'] = ['pictures_has_tags.tags_name' => $this->tag];
			} else{
				$and['pictures_has_tags.tags_name'] = $this->tag;
			}
		}
		if(!empty($this->date_begin) && !empty($this->date_end)){
			$and['pictures.pic_create_time[<>]'] = [
				$this->date_begin,
				$this->date_end
			];
		} else if(!empty($this->date_begin)){
			$and['pictures.pic_create_time[>]'] = $this->date_begin;
		} else if(!empty($this->date_end)){
			$and['pictures.pic_create_time[<]'] = $this->date_end;
		}
		$n = count($and);
		if($n < 1){
			$where = NULL;
		} else{
			$where = ['AND' => $and];
		}
		//'ORDER'=>
		$count = $this->getCount($where);
		$max_page = ceil($count / $this->number);
		if($this->page > $max_page){
			$this->page = $max_page;
		}
		$where['ORDER'] = "{$this->order} {$this->order_type}";
		$where['LIMIT'] = [
			$this->page > 1 ? $this->number * ($this->page - 1) : 0,
			$this->number
		];
		$content = $this->getContent($where);
		//		print_r(db()->last_query());
		//		print_r(db()->error());
		if($content === false){
			$this->throwMsg(-2);
		}
		return [
			'max' => $max_page,
			'count' => $count,
			'now' => $this->page,
			'content' => $content
		];
	}


	/**
	 * 重置参数
	 * @return $this
	 */
	private function reset(){
		$this->number = 50;
		$this->page = 1;
		$this->tag = NULL;
		$this->user_id = 0;
		$this->tag_like = false;
		$this->date_begin = "";
		$this->date_end = "";
		$this->order_type = "DESC";
		$this->order = "pictures.id";
		return $this;
	}

	/**
	 * 获取内容
	 * @param array $where
	 * @return array|bool
	 */
	private function getContent($where){
		$col = [
			'pictures.id' => 'pic_id',
			'pictures.pic_thumbnails_path' => 'pic_thumbnails_path',
			'pictures.pic_path' => 'pic_path',
			'server.url' => 'pic_base_url',
		];
		if(!empty($this->tag)){
			$rt = db()->select("pictures", [
				'[><]pictures_has_tags' => ['id' => 'pictures_id'],
				'[><]server' => ['server_name' => 'name']
			], $col, $where);
		} else{
			$rt = db()->select("pictures", ['[><]server' => ['server_name' => 'name']], $col, $where);
		}
		if($rt === false){
			$this->throwMsg(-2);
		}
		for($i = 0, $l = count($rt); $i < $l; $i++){
			if($rt[$i]['pic_thumbnails_path'] === 'thumbnail'){
				$rt[$i]['url'] = $rt[$i]['pic_base_url'] . $rt[$i]['pic_path'] . "/thumbnail";
			} else{
				$rt[$i]['url'] = $rt[$i]['pic_base_url'] . $rt[$i]['pic_thumbnails_path'];
			}
		}
		return $rt;
	}

	/**
	 * 获取统计数目
	 * @param array $where
	 * @return int
	 */
	private function getCount($where){
		$db = db();
		$where_sql = $db->getReader()->where_clause($where);
		if(!empty($this->tag)){
			$sql = "SELECT count(*) FROM `pictures` INNER JOIN `pictures_has_tags` on `pictures_has_tags`.`pictures_id`=`pictures`.`id` " . $where_sql;
		} else{
			$sql = "select count(*) from `pictures` " . $where_sql;
		}
		$stmt = $db->getReader()->query($sql);
		if($stmt === false){
			$this->throwMsg(-2);
		}
		return intval($stmt->fetchColumn());
	}

	/**
	 * 抛出异常信息
	 * @param int $code
	 * @throws \Exception
	 */
	private function throwMsg($code){
		$code = intval($code);
		throw new \Exception($this->getMsg($code), $code);
	}

	/**
	 * 获取异常信息
	 * @param int $code
	 * @return string
	 */
	public function getMsg($code){
		switch($code){
			case -1:
				return ___("User is error.");
			case -2:
				Log::write(implode(", ", db()->error()['read']) . "\n" . db()->last_query()['read'], Log::SQL);
				return ___("Data select error on sql.");
			default:
				return ___("Unknown error");
		}
	}

}