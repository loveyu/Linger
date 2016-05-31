<?php
/**
 * User: loveyu
 * Date: 2016/5/1
 * Time: 17:12
 */

namespace UView;


use Core\Page;
use ULib\Picture;
use ULib\FulltextSearch;

/**
 * Class DataApi
 * 数据查询接口
 * @package UView
 */
class DataApi extends Page{
	/**
	 * DataApi constructor.
	 * 构造器
	 */
	public function __construct(){
		parent::__construct();
	}

	/**
	 * @param int $num
	 * @param int $page
	 */
	public function get_new_pics($num = 15, $page = 1){
		$this->__lib('Picture');
		$num = intval($num);
		$page = intval($page);
		$num = ($num >= 5 && $num < 80) ? $num : 15;
		$page = ($page >= 1 && $page <= 100) ? $page : 1;
		$pic = new Picture();
		$data = $pic->select_new_pic($num, ($page - 1) * $num);
		send_json_header();
		echo json_encode([
			'data' => $data,
			'status' => true
		], JSON_UNESCAPED_UNICODE);
	}

	/**
	 * 搜索初始化接口
	 * 查询总数
	 */
	public function search_init(){
		$search = new FulltextSearch();
		$list = $search->count_map(req()->get('keyword'), ['pic', 'gallery', 'post']);
		$rt = [];
		foreach($list as $k => $v){
			$rt[] = ['name' => $k, 'num' => $v];
		}
		send_json_header();
		echo json_encode([
			'data' => $rt
		]);
	}

	/**
	 * 搜索数据接口
	 */
	public function search(){
		$search = new FulltextSearch();
		$result = $search->search(req()->get('keyword'), req()->get('type'), req()->get('page'), 40);
		$rt = [
			'result' => $result,
			'count' => is_array($result) ? count($result) : 0
		];
		send_json_header();
		echo json_encode($rt);
	}

}