<?php
/**
 * User: Loveyu
 * Date: 14-4-8
 * Time: 下午9:44
 */

namespace ULib;


use Core\Log;

class ListGallery{
	/**
	 * 数据库操作类
	 * @var \CLib\Sql
	 */
	private $db;
	/**
	 * @var int[] 分页统计信息
	 */
	private $count = [
		'page' => 1,
		'max' => 1,
		'count' => 0,
		'number' => 20,
	];

	/**
	 * 构造
	 */
	function __construct(){
		$this->db = db();
	}

	/**
	 * 设置分页信息
	 * @param int $page
	 * @param int $number
	 */
	public function setPager($page, $number = 20){
		$page = intval($page);
		$number = intval($number);
		$this->count['page'] = $page > 0 ? $page : 1;
		$this->count['number'] = $number > 5 ? ($number > 100 ? 100 : $number) : 5;
	}

	/**
	 * @return \int[]
	 */
	public function getCount(){
		return $this->count;
	}

	private function getCountInfo($id = NULL, $type = NULL){
		$count = 0;
		switch($type){
			case 'all':
				$count = $this->db->count("gallery", ['gallery_status' => 1]);
				break;
			case 'user':
				$count = $this->db->count('gallery', [
					'AND' => [
						'users_id' => $id,
						'gallery_status' => 1
					]
				]);
				break;
		}
		$this->count['count'] = $count;
		$this->count['max'] = intval(ceil($count / $this->count['number']));
		if($this->count['page'] > $this->count['max']){
			$this->count['page'] = -1;
			return false;
		}
		return true;
	}

	public function getList(){
		if(!$this->getCountInfo(NULL, 'all')){
			return [];
		}
		$list = $this->db->select("pictures", [
			'[<]gallery' => ['id' => 'gallery_front_cover'],
			'[>]server' => ['server_name' => 'name'],
		], [
			'gallery.id' => 'gallery_id',
			'gallery.users_id' => 'users_id',
			'gallery.gallery_title' => 'gallery_title',
			'gallery.gallery_description' => 'gallery_description',
			'gallery.gallery_create_time' => 'gallery_create_time',
			'gallery.gallery_update_time' => 'gallery_update_time',
			'gallery.gallery_like_count' => 'gallery_like_count',
			'gallery.gallery_comment_count' => 'gallery_comment_count',
			'gallery.gallery_comment_status' => 'gallery_comment_status',
			'gallery.gallery_front_cover' => 'gallery_front_cover',
			'pictures.id' => 'pic_id',
			'pictures.server_name' => 'server_name',
			'server.url' => 'server_url',
			'pictures.pic_name' => 'pic_name',
			'pictures.pic_path' => 'pic_path',
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
			'gallery.gallery_status' => 1,
			'ORDER' => 'gallery.id DESC',
			'LIMIT' => [
				$this->count['number'] * ($this->count['page'] - 1),
				$this->count['number']
			]
		]);
		$this->initUser($list);
		$this->parseList($list);
		return $list;
	}

	private function parseList(&$list){
		if(count($list) > 0){
			lib()->load('Picture', 'Gallery');
			$pic = new Picture();
			$g = new Gallery();
			$pic->parsePic($list, false);
			$g->listAddTags($list);
		}
	}

	private function initUser(&$data){
		$ids = [];
		foreach($data as &$v){
			if(!isset($ids[$v['users_id']]) && User::UserStack($v['users_id']) === false){
				$ids[$v['users_id']] = $v['users_id'];
			}
		}
		if(reset($ids)){
			$list = $this->db->select("users", [
				'id' => 'id',
				'user_name' => 'name',
				'user_aliases' => 'aliases',
				'user_email' => 'email',
				'user_url' => 'url',
				'user_status' => 'status',
				'user_registered_time' => 'registered_time',
				'user_last_login_time' => 'last_login_time',
				'user_avatar' => 'avatar',
			], ['id' => $ids]);
			if($list === false){
				Log::write(_("Select other users error."), Log::SQL);
			} else{
				foreach($list as &$v){
					//保存数据到堆栈
					new User($v, true);
				}
			}
		}
	}

	public function getListOfUser($user_name){
		$user = User::getUser($user_name);
		if(!is_object($user)){
			return [];
		}
		if(!$this->getCountInfo($user->getId(), 'user')){
			return [];
		}
		$list = $this->db->select("pictures", [
			'[<]gallery' => ['id' => 'gallery_front_cover'],
			'[>]server' => ['server_name' => 'name'],
		], [
			'gallery.id' => 'gallery_id',
			'gallery.users_id' => 'users_id',
			'gallery.gallery_title' => 'gallery_title',
			'gallery.gallery_description' => 'gallery_description',
			'gallery.gallery_create_time' => 'gallery_create_time',
			'gallery.gallery_update_time' => 'gallery_update_time',
			'gallery.gallery_like_count' => 'gallery_like_count',
			'gallery.gallery_comment_count' => 'gallery_comment_count',
			'gallery.gallery_comment_status' => 'gallery_comment_status',
			'gallery.gallery_front_cover' => 'gallery_front_cover',
			'pictures.id' => 'pic_id',
			'pictures.server_name' => 'server_name',
			'server.url' => 'server_url',
			'pictures.pic_name' => 'pic_name',
			'pictures.pic_path' => 'pic_path',
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
			'AND' => [
				'gallery.gallery_status' => 1,
				'gallery.users_id' => $user->getId()
			],
			'ORDER' => 'gallery.id DESC',
			'LIMIT' => [
				$this->count['number'] * ($this->count['page'] - 1),
				$this->count['number']
			]
		]);
		$this->parseList($list);
		return $list;
	}
} 