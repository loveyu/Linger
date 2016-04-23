<?php
namespace ULib\tag_query;
	/**
	 * User: loveyu
	 * Date: 2016/4/8
	 * Time: 1:03
	 */
use ULib\ListGallery;

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
		$this->db = \db();
		$this->tag = $tag;
	}

	public function get_count(){
		$query = $this->db->query_by_param("select count(1) as `count` from gallery as a INNER JOIN gallery_has_tags as b on a.id=b.gallery_id where b.tags_name=:tag_name and a.gallery_status=1 order by null", ['tag_name' => $this->tag]);
		return isset($query[0]) ? (0 + $query[0]['count']) : 0;
	}

	public function query($limit){
		$limit = array_map('intval', $limit);
		$query = $this->db->query_by_param("select a.id as id from gallery as a INNER JOIN gallery_has_tags as b on a.id=b.gallery_id where b.tags_name=:tag_name and a.gallery_status=1  order by a.gallery_create_time  DESC limit " . implode(",", $limit), ['tag_name' => $this->tag]);
		$list = [];
		foreach($query as $v){
			$list[] = $v['id'];
		}
		if(empty($list)){
			return [];
		}
		\lib()->load("ListGallery");
		$list_gallery = new ListGallery();
		return $list_gallery->getListByGalleryIds($list);
	}
}