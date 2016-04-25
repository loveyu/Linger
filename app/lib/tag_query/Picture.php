<?php
/**
 * 查询图片的标签列表
 * User: loveyu
 * Date: 2016/4/26
 * Time: 1:37
 */

namespace ULib\tag_query;


class Picture{
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

	/**
	 * 获取总数
	 * @return int
	 */
	public function get_count(){
		$query = $this->db->query_by_param("SELECT count(1) AS `count` FROM pictures AS a INNER JOIN pictures_has_tags AS b ON a.id=b.pictures_id WHERE b.tags_name=:tag_name AND a.pic_status=1 ORDER BY NULL", ['tag_name' => $this->tag]);
		return isset($query[0]) ? (0 + $query[0]['count']) : 0;
	}

	/**
	 * 查询图集
	 * @param array $limit
	 * @return array
	 */
	public function query($limit){
		$limit = array_map('intval', $limit);
		$list = \db()->select("pictures", [
			'[><]server' => ['server_name' => 'name'],
			'[><]pictures_has_tags' => ['id' => 'pictures_id']
		], [
			'pictures.id' => 'pic_id',
			'pictures.users_id' => 'user_id',
			'pictures.server_name' => 'server_name',
			'server.url' => 'server_url',
			'pictures.pic_path' => 'pic_path',
			'pictures.pic_name' => 'pic_name',
			'pictures.pic_create_time' => 'pic_create_time',
			'pictures.pic_width' => 'pic_width',
			'pictures.pic_height' => 'pic_height',
			'pictures.pic_description' => 'pic_description',
			'pictures.pic_thumbnails_path' => 'pic_thumbnails_path',
			'pictures.pic_thumbnails_width' => 'pic_thumbnails_width',
			'pictures.pic_thumbnails_height' => 'pic_thumbnails_height',
			'pictures.pic_hd_path' => 'pic_hd_path',
			'pictures.pic_hd_width' => 'pic_hd_width',
			'pictures.pic_hd_height' => 'pic_hd_height',
			'pictures.pic_status' => 'pic_status',
			'pictures.pic_comment_count' => 'pic_comment_count',
			'pictures.pic_display_path' => 'pic_display_path',
			'pictures.pic_display_width' => 'pic_display_width',
			'pictures.pic_display_height' => 'pic_display_height'
		], [
			'pictures_has_tags.tags_name' => $this->tag,
			'ORDER' => 'pictures.id DESC',
			'LIMIT' => $limit
		]);
		if($list === false){
			return [];
		}
		$picture = new \ULib\Picture();
		$picture->parsePic($list, false);
		return $list;
	}
}