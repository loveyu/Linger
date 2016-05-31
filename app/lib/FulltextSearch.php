<?php
/**
 * User: loveyu
 * Date: 2016/6/1
 * Time: 0:42
 */

namespace ULib;

/**
 * 全文索引搜索
 * Class FulltextSearch
 * @package ULib
 */
class FulltextSearch{
	/**
	 * @var ElasticsearchBase 搜索对象
	 */
	private $elastic_obj;

	/**
	 * @var string 索引名词
	 */
	private $index_name;

	/**
	 * @var bool 是否开启搜索功能
	 */
	private $search_open = false;

	/**
	 * FullTextAction constructor.
	 */
	public function __construct(){
		$cfg = cfg();
		$this->elastic_obj = new ElasticsearchBase($cfg->get('option', 'elastic_server'), $cfg->get('option', 'elastic_index_prefix'));
		$this->index_name = $cfg->get('option', 'elastic_index');
		$this->search_open = $cfg->get('option', 'elastic_status');
		$this->search_open = $this->search_open === true || $this->search_open === "1" || $this->search_open == "open";
	}

	/**
	 * 传入一组类型列表，查询对应类型的搜索关键字的记录总数
	 * @param string $keyword
	 * @param array  $type_names
	 * @return \ArrayObject
	 */
	public function count_map($keyword, $type_names){
		if(!$this->search_open){
			return new \ArrayObject();
		}
		if(!is_string($keyword) || $keyword === ""){
			return array_fill_keys($type_names, 0);
		}
		$rt = [];
		foreach($type_names as $name){
			$strut = $this->get_search_strut($keyword, $name);
			if(empty($strut)){
				$rt[$name] = 0;
			} else{
				$rt[$name] = $this->search_count(['query' => $strut], $name);
			}
		}
		return !empty($rt) ? $rt : new \ArrayObject();
	}

	/**
	 * 依据查询的名称获取搜索的总数
	 * @param array  $strut 搜索结构
	 * @param string $type_name
	 * @return int
	 */
	private function search_count($strut, $type_name){
		if(empty($strut)){
			return 0;
		}
		return $this->elastic_obj->count($this->index_name, $type_name, $strut);
	}

	/**
	 * 获取查询结构
	 * @param string $keyword
	 * @param string $type
	 * @return bool|array
	 */
	private function get_search_strut($keyword, $type){
		switch($type){
			case "pic":
				return [
					"multi_match" => [
						"query" => $keyword,
						"fields" => ["name", "desc", "tags"]
					]
				];
				break;
			case "gallery":
				return [
					"multi_match" => [
						"query" => $keyword,
						"fields" => ["title", "desc", "tags", "detail",]
					]
				];
				break;
			case "post":
				return [
					"multi_match" => [
						"query" => $keyword,
						"fields" => ["title", "abstract", "tags", "content", "route"]
					]
				];
				break;
		}
		return false;
	}

}