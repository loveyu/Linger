<?php
/**
 * User: loveyu
 * Date: 2016/6/1
 * Time: 0:42
 */

namespace ULib;
use Core\Log;

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

	/**
	 * 查询搜索结果
	 * @param string $keyword  关键字
	 * @param string $type     搜索类型
	 * @param int    $page     搜索第几页
	 * @param int    $one_page 每页显示数量
	 * @return array|false 查询失败返回false
	 */
	public function search($keyword, $type, $page, $one_page){
		$strut = $this->get_search_strut($keyword, $type);
		if(empty($strut)){
			return false;
		}
		$highlight = $this->get_type_highlight_field($type);
		$result = $this->query($type, $strut, $page, $one_page, $total, $highlight);
		if(empty($result)){
			return false;
		}
		$convert = new FulltextDataConvert();
		switch($type){
			case "pic":
				return $convert->toPic($result);
			case "gallery":
				return $convert->toGallery($result);
			case "post":
				return $convert->toPost($result);
			default:
				return false;
		}
	}

	/**
	 * 获取要高亮的选项
	 * @param string $type
	 * @return bool|mixed
	 */
	private function get_type_highlight_field($type){
		$map = ['pic' => ['name', 'tags']];
		if(isset($map[$type])){
			return $map[$type];
		}
		return false;
	}

	/**
	 * 查询数据集的简单方式
	 * @param string $types         类型表
	 * @param array  $query_strut   关键字查询的结构
	 * @param int    $page          当前第几页
	 * @param int    $one_page      每页显示的数量
	 * @param int    $total_count   返回记录的总数
	 * @param array  $highlight     高亮选项
	 * @param string $highlight_tag 高亮标签
	 * @return array|false
	 */
	private function query($types, $query_strut, &$page, &$one_page, &$total_count, $highlight = array(), $highlight_tag = 'em'){
		$page = (int)$page;
		$one_page = (int)$one_page;
		if($page < 1){
			$page = 1;
		}
		if($one_page < 1 || $one_page > 100){
			$one_page = 10;
		}
		$total_count = 0;
		$param = array(
			"from" => ($page - 1) * $one_page,
			"size" => $one_page,
			"query" => $query_strut,
		);

		if($highlight){
			$param['highlight'] = array(
				"pre_tags" => array("<{$highlight_tag}>"),
				"post_tags" => array("</{$highlight_tag}>"),
				'fields' => array_fill_keys($highlight, new \ArrayObject())
			);
		}
		$result = $this->elastic_obj->post($this->index_name, "{$types}/_search", $param);
		if(!isset($result['hits'])){
			return false;
		}
		$total_count = (int)$result['hits']['total'];
		$rt = array();
		foreach($result['hits']['hits'] as $item){
			if(ctype_digit($item['_id'])){
				if(!isset($rt[(int)$item['_id']])){
					$rt[(int)$item['_id']] = array();
				}
				$rt[(int)$item['_id']]['source'] = $item['_source'];
				if(isset($item['highlight'])){
					$rt[(int)$item['_id']]['highlight'] = $item['highlight'];
				}
			}
		}
		return $rt;
	}

}