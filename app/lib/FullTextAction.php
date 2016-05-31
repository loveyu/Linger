<?php
/**
 * User: loveyu
 * Date: 2016/5/30
 * Time: 0:47
 */

namespace ULib;


/**
 * 全文索引类
 * Class FullTextAction
 * @package ULib
 */
class FullTextAction{

	/**
	 * @var ElasticsearchBase 搜索对象
	 */
	private $elastic_obj;

	/**
	 * @var string 索引名词
	 */
	private $index_name;

	/**
	 * @var FullTextAction
	 */
	private static $instance;

	/**
	 * @var bool 是否开启搜索功能
	 */
	private $search_open = false;

	/**
	 * FullTextAction constructor.
	 */
	private function __construct(){
		$cfg = cfg();
		$this->elastic_obj = new ElasticsearchBase($cfg->get('option', 'elastic_server'), $cfg->get('option', 'elastic_index_prefix'));
		$this->index_name = $cfg->get('option', 'elastic_index');
		$this->search_open = $cfg->get('option', 'elastic_status');
		$this->search_open = $this->search_open === true || $this->search_open === "1" || $this->search_open == "open";
	}

	/**
	 * 获取全文索引实例
	 * @return FullTextAction
	 */
	public static function getInstance(){
		if(self::$instance === NULL){
			self::$instance = new FullTextAction();
		}
		return self::$instance;
	}

	/**
	 * 转换图片列表
	 * @param array $pic_info_list 图片列表的数据是原始数据，不带标签数据
	 * @return array 返回的数据以ID为主键
	 */
	private function convert_picture($pic_info_list){
		$rt = [];
		$ids = [];
		foreach($pic_info_list as $v){
			$item = [
				'add_time' => date("Y-m-d\\TH:i:s", strtotime($v['pic_create_time'])),
				'modify_time' => date("Y-m-d\\TH:i:s", strtotime($v['pic_create_time'])),
				'desc' => htmlspecialchars(strip_tags($v['pic_description'])),
				'name' => htmlspecialchars(strip_tags($v['pic_name'])),
				'tags' => []
			];
			$rt[$v['id']] = $item;
			$ids[] = $v['id'];
		}
		//查询标签
		$tag_obj = new Tag();
		$tag_map = $tag_obj->getPicTagsMap($ids);
		foreach($tag_map as $id => $v){
			$rt[$id]['tags'] = $v;
		}
		return $rt;
	}

	/**
	 * 更新全文索引
	 * @param int $pic_id
	 */
	public function update_picture($pic_id){
		if(!$this->search_open){
			return;
		}
		$pic_id = (int)$pic_id;
		$pic_obj = new Picture();
		$info = $pic_obj->get_raw_picture($pic_id);
		if(empty($info) || !$info['pic_status']){
			$this->delete_picture($pic_id);
			return;
		}
		$list = $this->convert_picture(array($info));
		$list = reset($list);
		$this->elastic_obj->put_document($this->index_name, "pic", $pic_id, $list);
	}

	/**
	 * 删除一张或多张
	 * @param array|int $pic_ids
	 */
	public function delete_picture($pic_ids){
		if(!$this->search_open){
			return;
		}
		if(!is_array($pic_ids)){
			$pic_ids = array($pic_ids);
		}
		$pic_ids = array_map('intval', $pic_ids);
		$this->elastic_obj->bulk_delete($this->index_name, "pic", $pic_ids);
	}
}