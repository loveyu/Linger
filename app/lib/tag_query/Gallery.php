<?php
namespace ULib\tag_query;
	/**
	 * User: loveyu
	 * Date: 2016/4/8
	 * Time: 1:03
	 */

/**
 * 查询图集的标记数据信息
 * Class Gallery
 * @package ULib\tag_query
 */
class Gallery{
	/**
	 * @var \CLib\Sql
	 */
	private $db;

	private $tag;

	/**
	 * Gallery constructor.
	 * @param string $tag
	 */
	public function __construct($tag){
		$this->db = db();
		$this->tag = $tag;
	}

	public function get_count(){
		$query = $this->db->query_by_param("select count(1) as `count` from gallery as a INNER JOIN gallery_has_tags as b on a.id=b.gallery_id where b.tags_name=:tag_name order by null", ['tag_name' => $this->tag]);
		return isset($query[0]) ? (0 + $query[0]['count']) : 0;
	}

	public function query($limit){

	}
}