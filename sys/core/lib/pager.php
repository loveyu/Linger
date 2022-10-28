<?php
/**
 * User: loveyu
 * Date: 2016/4/8
 * Time: 1:40
 */

namespace CLib;

/**
 * Class pager 分页核心类
 * @package CLib
 */
class pager{
	/**
	 * @var int
	 */
	private $count;
	/**
	 * @var int
	 */
	private $one_page;
	/**
	 * @var int
	 */
	private $now_page;

	/**
	 * @var int 总页数
	 */
	private $all_page = 0;

	/**
	 * @var int 当前页
	 */
	private $current_page;

	/**
	 * @var callable 连接创建器
	 */
	private $link_creator = NULL;

	/**
	 * 分页类
	 * pager constructor.
	 * @param int $count    总数
	 * @param int $one_page 没页条数
	 * @param int $now_page 当前页
	 */
	public function __construct($count, $one_page, $now_page){
		if($one_page <= 0){
			$one_page = 10;
		}
		if($now_page <= 0){
			$now_page = 1;
		}
		$this->count = $count;
		$this->one_page = $one_page;
		$this->now_page = $now_page;
		$this->all_page = ceil($count / (double)$one_page);
		$this->current_page = $now_page > $this->all_page ? $this->all_page : $now_page;
		if($this->current_page < 1){
			$this->current_page = 1;
		}
	}


	/**
	 * 获取当前SQL的偏移
	 * @return array [start,length]
	 */
	public function get_limit(){
		return [($this->current_page - 1) * $this->one_page, $this->one_page];
	}

	/**
	 * @return int
	 */
	public function getCount(){
		return $this->count;
	}

	/**
	 * @return int
	 */
	public function getOnePage(){
		return $this->one_page;
	}

	/**
	 * @return int
	 */
	public function getNowPage(){
		return $this->now_page;
	}

	/**
	 * @return int
	 */
	public function getAllPage(){
		return $this->all_page;
	}

	/**
	 * @return int
	 */
	public function getCurrentPage(){
		return $this->current_page;
	}


	/**
	 * 获取上一页和下一页
	 * @return array {'previous' => NULL,'next' => NULL}
	 */
	public function get_pager(){
		$previous = NULL;
		$next = NULL;
		if($this->now_page > 1){
			$previous = call_user_func_array($this->link_creator, [$this->now_page - 1]);
		}
		if($this->now_page < $this->all_page){
			$next = call_user_func_array($this->link_creator, [$this->now_page + 1]);
		}
		return [
			'previous' => $previous,
			'next' => $next
		];
	}

	/**
	 * 设置连接创建器
	 * @param callable $callback
	 */
	public function setLinkCreator($callback){
		if(!is_callable($callback)){
			return;
		}
		$this->link_creator = $callback;
	}
}