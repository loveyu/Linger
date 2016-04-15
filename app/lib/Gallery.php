<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-1
 * Time: 下午8:47
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace ULib;

/**
 * Class Gallery
 * @package ULib
 */
use Core\Log;

/**
 * Class Gallery
 * @package ULib
 */
class Gallery extends AppException{
	/**
	 * @var \CLib\Sql
	 */
	private $db;

	/**
	 * @var null|array
	 */
	private $info = NULL;

	/**
	 * @var string
	 */
	private $error = '';

	/**
	 * @var Meta
	 */
	private $meta = NULL;

	/**
	 * @var int
	 */
	private $gallery_id = 0;

	/**
	 * @var null
	 */
	private $user_id = NULL;

	/**
	 * @param int  $id
	 * @param null $user_id
	 */
	public function __construct($id = 0, $user_id = NULL){
		$this->db = db();
		$this->gallery_id = intval($id);
		$this->user_id = $user_id;
		lib()->load('Meta');
		$this->meta = new Meta("gallery_meta", "gallery_id", $id);
	}

	/**
	 * 喜欢或取消喜欢某一图集
	 * @param int $g_id
	 * @param int $user_id
	 */
	public function like($g_id, $user_id){
		$db = db();
		$g_id = intval($g_id);
		$user_id = intval($user_id);
		if($db->has("users_like_gallery", [
			'AND' => [
				'users_id' => $user_id,
				'gallery_id' => $g_id
			]
		])
		){
			if($db->delete("users_like_gallery", [
					'AND' => [
						'users_id' => $user_id,
						'gallery_id' => $g_id
					]
				]) === false
			){
				$this->throwMsg(-13);
			}
			hook()->apply('Gallery_unlike', NULL, $g_id, $user_id);
		} else{
			if($db->insert("users_like_gallery", [
					'users_id' => $user_id,
					'gallery_id' => $g_id,
					'like_time' => date("Y-m-d H:i:s")
				]) < 0
			){
				$this->throwMsg(-14);
			}
			hook()->apply('Gallery_like', NULL, $g_id, $user_id);
		}
	}

	/**
	 * 获取图集的详细信息
	 * @return string
	 */
	public function more_info(){
		try{
			return $this->meta->get(['more_info'], '')['more_info'];
		} catch(\Exception $ex){
			return false;
		}
	}

	/**
	 * 设置对应的信息
	 * @param array $meta
	 * @throws \Exception
	 */
	public function set_meta_info($meta){
		if(isset($meta['more_info'])){
			$this->meta->set(['more_info' => htmlspecialchars($meta['more_info'], ENT_NOQUOTES)]);
		} else{
			throw new \Exception(___("Set gallery more info error."));
		}
		//传入数据未过滤
		hook()->apply("Gallery_set_meta_info", NULL, $meta, $this->meta);
	}

	/**
	 * 获取图集ID
	 * @return int
	 */
	public function getGalleryId(){
		return $this->gallery_id;
	}

	/**
	 * 插入标题创建新的画集
	 * @param string $title
	 * @param int    $user_id
	 * @return int
	 */
	public function add($title, $user_id){
		$user_id = intval($user_id);
		$title = trim($title);
		if(empty($title)){
			$this->throwMsg(-3);
		}
		$rt = $this->db->insert("gallery", [
			'users_id' => $user_id,
			'gallery_title' => $title,
			'gallery_create_time' => date("Y-m-d H:i:s"),
			'gallery_update_time' => date("Y-m-d H:i:s"),

		]);
		if($rt === -1){
			$this->throwMsg(-1);
		} else if($rt == 0){
			$this->throwMsg(-2);
		}
		return $rt;
	}

	/**
	 * 设置图集封面ID
	 * @param int $g_id
	 * @param int $p_id 为0时置空
	 * @param int $u_id
	 * @return array|null
	 */
	public function set_front_cover($g_id, $p_id, $u_id){
		$g_id = intval($g_id);
		$p_id = intval($p_id);
		$u_id = intval($u_id);
		$this->galleryOwnerCheck($g_id, $u_id);
		lib()->load('Picture');
		if($p_id < 1){
			$p_id = NULL;
			$this->db->update("gallery", ['gallery_front_cover' => $p_id], ['id' => $g_id]);
			return NULL;
		} else{
			$p = new Picture();
			$p->picOwnerCheck($p_id, $u_id);
			$this->db->update("gallery", ['gallery_front_cover' => $p_id], ['id' => $g_id]);
			return $p->get($p_id, $u_id);
		}
	}

	/**
	 * @param $g_id
	 * @param $tag
	 * @param $user_id
	 * @return array|null
	 */
	public function add_tag($g_id, $tag, $user_id){
		$g_id = intval($g_id);
		$user_id = intval($user_id);
		$tag_name = trim($tag);
		$this->galleryOwnerCheck($g_id, $user_id);
		lib()->load('Tag');
		$tag = new Tag();
		return $tag->add_tag($g_id, $tag_name, "gallery");
	}

	/**
	 * 插入图片到图集中
	 * @param string $list 用英文逗号分隔的字符
	 * @return int 插入图集的数量
	 */
	public function add_pic($list){
		$list = array_flip(array_flip(array_map('intval', explode(",", $list))));
		if(empty($list)){
			$this->throwMsg(-9);
		}
		$this->galleryOwnerCheck($this->gallery_id, $this->user_id);
		$own_list = $this->db->select("pictures", ['id'], [
			'AND' => [
				'id' => $list,
				'users_id' => $this->user_id
			]
		]);
		$call = function ($id){
			return intval($id['id']);
		};
		$own_list = array_map($call, $own_list);
		if(count($own_list) < 1){
			$this->throwMsg(-10);
		}
		$has_pic_list = $this->db->select("gallery_has_pictures", ['pictures_id' => 'id'], ['gallery_id' => $this->gallery_id]);
		$has_pic_list = array_map($call, $has_pic_list);
		$diff = array_diff($own_list, $has_pic_list);
		if(count($diff) < 1){
			$this->throwMsg(-11);
		}
		$diff = array_map(function ($data){
			return [
				'pictures_id' => $data,
				'gallery_id' => $this->gallery_id
			];
		}, $diff);
		array_unshift($diff, "gallery_has_pictures");
		$rt = call_user_func_array([
			$this->db,
			'insert'
		], $diff);
		if($rt === false){
			$this->throwMsg(-2);
		}
		return $rt;
	}

	/**
	 * @param $list
	 */
	public function remove_pic($list){
		$list = array_flip(array_flip(array_map('intval', explode(",", $list))));
		if(empty($list)){
			$this->throwMsg(-9);
		}
		$this->galleryOwnerCheck($this->gallery_id, $this->user_id);
		if($this->db->delete("gallery_has_pictures", [
				'AND' => [
					'gallery_id' => $this->gallery_id,
					'pictures_id' => $list
				]
			]) === false
		){
			$this->throwMsg(-12);
		}
	}

	/**
	 * 删除一个单独的标签
	 * @param string $g_id
	 * @param string $tag_name
	 * @param string $user_id
	 */
	public function remove_tag($g_id, $tag_name, $user_id){
		$g_id = intval($g_id);
		$user_id = intval($user_id);
		$tag_name = trim($tag_name);
		$this->galleryOwnerCheck($g_id, $user_id);
		lib()->load('Tag');
		$tag = new Tag();
		$tag->del_tag($g_id, $tag_name, "gallery");
	}

	/**
	 * @param $g_id
	 * @param $user_id
	 */
	private function galleryOwnerCheck($g_id, $user_id){
		if(!db()->has("gallery", [
			'AND' => [
				'id' => $g_id,
				'users_id' => $user_id
			]
		])
		){
			$this->throwMsg(-6);
		}
	}

	/**
	 * @param $title
	 * @param $desc
	 * @param $comment_status
	 */
	public function edit_info($title, $desc, $comment_status){
		$this->galleryOwnerCheck($this->gallery_id, $this->user_id);
		if(!in_array($comment_status, [
			0,
			1
		])
		){
			$this->throwMsg(-7);
		}
		if(db()->update("gallery", [
				'gallery_comment_status' => $comment_status,
				'gallery_description' => trim($desc),
				'gallery_title' => trim($title),
				'gallery_update_time' => date("Y-m-d H:i:s"),
			], ['id' => $this->gallery_id]) === false
		){
			$this->throwMsg(-8);
		}
	}


	/**
	 * 获取新的图集
	 * @param int  $number
	 * @param bool $need_front_cover
	 * @return array|bool
	 */
	public function select_new_gallery($number = 16, $need_front_cover = false){
		$number = intval($number);
		$number = $number > 2 ? $number : 2;
		if($need_front_cover == true){
			$where = [
				'AND' => [
					'gallery.gallery_front_cover[!]' => NULL,
					'gallery.gallery_status' => 1
				],
				'ORDER' => 'gallery.id DESC',
				'LIMIT' => [
					0,
					$number
				]
			];
		} else{
			$where = [
				'gallery.gallery_status' => 1,
				'ORDER' => 'gallery.id DESC',
				'LIMIT' => [
					0,
					$number
				]
			];
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
		], $where);
		lib()->load('Picture');
		$pic = new Picture();
		$pic->parsePic($list, false);
		return $list;
	}

	/**
	 * 为图集列表添加标签信息
	 * @param array $list
	 */
	public function listAddTags(&$list){
		$ids = [];
		foreach($list as $v){
			$ids[] = $v['gallery_id'];
		}
		$tmp = [];

		if(count($ids) > 0){
			$tags = db()->select("gallery_has_tags", [
				'gallery_id',
				'tags_name'
			], ['gallery_id' => $ids]);
			foreach($tags as $v){
				if(!isset($tmp[$v['gallery_id']])){
					$tmp[$v['gallery_id']] = [];
				}
				$tmp[$v['gallery_id']][] = $v['tags_name'];
			}
			unset($tags);
		}
		for($i = 0, $l = count($list); $i < $l; $i++){
			if(isset($tmp[$list[$i]['gallery_id']])){
				$list[$i]['gallery_tags'] = $tmp[$list[$i]['gallery_id']];
			} else{
				$list[$i]['gallery_tags'] = [];
			}
		}
	}

	/**
	 * @param int $page
	 * @param int $number
	 * @return array
	 */
	public function getList($page = 1, $number = 20){
		$page = 0 + $page;
		if(empty($number)){
			$number = 20;
		} else{
			$number = 0 + $number;
		}
		if($page < 1){
			$page = 1;
		}
		if($number < 1){
			$number = 1;
		}
		$count = $this->countUserGallery($this->user_id);
		$rt = [
			'count' => $count,
			'max' => ceil($count / $number),
			'found' => true,
			'number' => $number,
			'page' => $page
		];
		if($page > $rt['max']){
			$rt['found'] = false;
			return $rt;
		}
		$gallery = $this->db->select("gallery", [
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
			'gallery.gallery_status' => 'gallery_status'
		], [
			'users_id' => $this->user_id,
			'ORDER' => 'gallery_id DESC',
			'LIMIT' => [
				$number * ($page - 1),
				$number
			]
		]);
		$ids = [];
		$gallery2 = [];
		foreach($gallery as $v){
			$ids[] = $v['gallery_id'];
			$gallery2[$v['gallery_id']] = $v;
			$gallery2[$v['gallery_id']]['tags'] = [];
		}
		unset($gallery);
		$tags = $this->db->select("gallery_has_tags", [
			'gallery_id',
			'tags_name'
		], ['gallery_id' => $ids]);
		foreach($tags as $v){
			$gallery2[$v['gallery_id']]['tags'][] = $v['tags_name'];
		}
		$rt['gallery'] = $gallery2;
		return $rt;
	}

	/**
	 * @param $user_id
	 * @return int
	 */
	public function countUserGallery($user_id){
		return $this->db->count("gallery", ['users_id' => $user_id]);
	}

	/**
	 * 简单的获取图集信息
	 * @param $id
	 * @return array|bool
	 */
	public static function getSimpleInfo($id){
		return db()->get("gallery", [
			'gallery.id' => 'gallery_id',
			'users_id',
			'gallery_title',
			'gallery_description',
			'gallery_create_time',
			'gallery_like_count',
			'gallery_front_cover',
			'gallery_status',
			'gallery_update_time'
		], ['id' => $id]);
	}

	/**
	 * 获取当前设置的图集信息
	 * @return array
	 */
	private function get(){
		$where = ['gallery.id' => $this->gallery_id];
		if($this->user_id !== NULL){
			$where = [
				'AND' => [
					'gallery.id' => $this->gallery_id,
					'gallery.users_id' => intval($this->user_id)
				]
			];
		}
		$this->info = $this->db->select("gallery", [
			'[><]users' => ['users_id' => 'id'],
			'[>]users_like_gallery' => [
				'id' => 'gallery_id',
				'______' => ['users_like_gallery.users_id' => is_login() ? login_user()->getId() : 0]
			]
		], [
			'users.user_name' => 'user_name',
			'users.user_url' => 'user_url',
			'users.user_aliases' => 'user_aliases',
			'users.user_status' => 'user_status',
			'users.user_avatar' => 'user_avatar',
			'gallery.id' => 'gallery_id',
			'gallery.users_id' => 'user_id',
			'gallery.gallery_title' => 'gallery_title',
			'gallery.gallery_description' => 'gallery_description',
			'gallery.gallery_create_time' => 'gallery_create_time',
			'gallery.gallery_like_count' => 'gallery_like_count',
			'gallery.gallery_update_time' => 'gallery_update_time',
			'gallery.gallery_comment_count' => 'gallery_comment_count',
			'gallery.gallery_comment_status' => 'gallery_comment_status',
			'gallery.gallery_front_cover' => 'gallery_front_cover',
			'gallery.gallery_status' => 'gallery_status',
			'users_like_gallery.like_time' => 'gallery_like_time'
		], $where);
		if(!isset($this->info[0])){
			$this->info = NULL;
			$this->error = ___('Gallery not found.');
		} else{
			lib()->load('Avatar', 'User');
			$this->info = $this->info[0];
			$this->info['user_avatar'] = Avatar::get($this->info['user_avatar'], User::getUser($this->info['user_id']));
			$this->info['gallery_tags'] = $this->getTags($this->gallery_id);
			$this->info['gallery_pictures'] = $this->getPictures($this->gallery_id);
			$front_cover = intval($this->info['gallery_front_cover']);
			if($front_cover > 0){
				lib()->load('Picture');
				$pic = new Picture();
				$this->info['front_cover'] = $pic->get_pic($front_cover);
			}
		}
		return $this->info;
	}

	/**
	 * 获取图集的图片
	 * @param $gallery_id
	 * @return array|bool
	 */
	public function getPictures($gallery_id){
		$rt = $this->db->select("pictures", [
			'[><]gallery_has_pictures' => ['id' => 'pictures_id'],
			'[><]server' => ['server_name' => 'name']
		], [
			'gallery_has_pictures.gallery_id' => 'gallery_id',
			'pictures.id' => 'pic_id',
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
		], ['AND' => ['gallery_has_pictures.gallery_id' => $gallery_id]]);
		//		for($i = 0, $l = count($rt); $i < $l; $i++){
		//			if($rt[$i]['pic_thumbnails_path'] === 'thumbnail'){
		//				$rt[$i]['url'] = $rt[$i]['server_url'] . $rt[$i]['pic_path'] . "/thumbnail";
		//			} else{
		//				$rt[$i]['url'] = $rt[$i]['server_url'] . $rt[$i]['pic_thumbnails_path'];
		//			}
		//		}
		lib()->load('Picture');
		$pic = new Picture();
		$pic->parsePic($rt, false);
		$pic->list_add_tags($rt);
		return $rt;
	}

	/**
	 * 将图集设置为公共的
	 */
	public function set_public(){
		$this->galleryOwnerCheck($this->gallery_id, $this->user_id);
		$si = $this->getSimpleInfo($this->gallery_id);
		if(empty($si['gallery_title'])){
			$this->throwMsg(-18);
		}
		if(empty($si['gallery_description'])){
			$this->throwMsg(-19);
		}
		if($si['gallery_front_cover'] < 1){
			$this->throwMsg(-20);
		}
		if(!$this->db->has("gallery_has_tags", ['gallery_id' => $this->gallery_id])){
			$this->throwMsg(-21);
		}
		if(!$this->db->has("gallery_has_pictures", ['gallery_id' => $this->gallery_id])){
			$this->throwMsg(-22);
		}
		$u = [
			'gallery_status' => 1,
			'gallery_update_time' => date("Y-m-d H:i:s")
		];
		$si = array_merge($si, $u);
		$d = $this->db->update("gallery", $u, [
			'AND' => [
				'id' => $this->gallery_id,
				'users_id' => $this->user_id
			]
		]);
		if($d === false){
			Log::write(___("Gallery set public error."), Log::SQL);
			$this->throwMsg(-16);
		}
		//对于更新成功后的信息钩子
		hook()->apply('Gallery_set_public', NULL, $si);
	}

	/**
	 * 图集信息已更新
	 */
	public function updated(){
		if(hook()->check("Gallery_updated")){
			$si = $this->getSimpleInfo($this->gallery_id);
			hook()->apply('Gallery_updated', NULL, $si);
		}
	}

	/**
	 * 将图集设置为草稿
	 */
	public function set_draft(){
		$this->galleryOwnerCheck($this->gallery_id, $this->user_id);
		$d = $this->db->update("gallery", [
			'gallery_status' => 0,
			'gallery_update_time' => date("Y-m-d H:i:s")
		], [
			'AND' => [
				'id' => $this->gallery_id,
				'users_id' => $this->user_id
			]
		]);
		if($d === false){
			Log::write(___("Gallery set draft error."), Log::SQL);
			$this->throwMsg(-17);
		}
		hook()->apply('Gallery_set_draft', NULL, $this->gallery_id, $this->user_id);
	}

	/**
	 * @param int|string $ids
	 * @param int        $user_id
	 */
	public function delete($ids, $user_id){
		$user_id = intval($user_id);
		if(is_numeric($ids)){
			$id = [intval($ids)];
		} else{
			$id = array_flip(array_flip(array_map('intval', explode(",", $ids))));
		}
		$db = $this->db->getWriter();
		$db->pdo->beginTransaction();
		$rt = hook()->apply("Gallery_delete", true, $id, $db);
		if($rt !== true){
			$db->pdo->rollBack();
			$this->throwMsg(-4);
		}
		$rt = $db->delete("gallery", [
			'AND' => [
				'id' => $id,
				'users_id' => $user_id
			]
		]);
		if($rt === false){
			$db->pdo->rollBack();
			$this->throwMsg(-4);
		}
		if($rt < 1){
			$db->pdo->rollBack();
			$this->throwMsg(-5);
		}
		$db->pdo->commit();
	}

	/**
	 * @return Meta
	 */
	public function getMeta(){
		return $this->meta;
	}

	/**
	 * @param $g_id
	 * @return array
	 */
	public function getTags($g_id){
		lib()->load('Tag');
		$tag = new Tag();
		return $tag->getGalleryTags($g_id);
	}

	/**
	 * 获取图集信息
	 * @param bool $previous_and_next 是否获取上一条和下一条
	 * @return array
	 */
	public function getInfo($previous_and_next = false){
		if($this->info === NULL){
			$this->get();
		}
		if($previous_and_next && !isset($this->info['previous_and_next'])){
			$this->getPreviousNext();
		}
		return $this->info;
	}


	/**
	 * 获取上一页和下一页的信息
	 */
	private function getPreviousNext(){
		if($this->gallery_id > 0){
			$read = $this->db->getReader();
			$stmt = $read->query("call getGalleryPreviousNextID(" . $read->quote($this->gallery_id) . ");");
			if($stmt !== false){
				$list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
				$this->info['previous_and_next'] = [
					'previous' => NULL,
					'next' => NULL
				];
				//释放连接操作
				unset($stmt);
				if(isset($list[0]) && array_sum($list[0]) > 0){
					$list = $list[0];
					$info = $read->select("gallery", [
						'id',
						'gallery_title'
					], [
						'id' => $list,
						'ORDER' => 'id'
					]);
					if($info === false){
						Log::write(___("Get gallery previous and next info error.") . join(",", $read->error()), Log::SQL);
						$this->throwMsg(-15);
					} else{
						for($i = 0, $c = count($info); $i < $c; ++$i){
							if($info[$i]['id'] == $list['previous']){
								$this->info['previous_and_next']['previous'] = & $info[$i];
							} else if($info[$i]['id'] == $list['next']){
								$this->info['previous_and_next']['next'] = & $info[$i];
							}
						}
					}
				} else{
					//为找到上一页和下一页数据
					//可能仅仅一页，数组留空
				}
			} else{
				Log::write(___("Gallery get previous and next page id error.") . join(", ", $read->error()), Log::SQL);
				$this->throwMsg(-15);
			}
		}
	}

	/**
	 * @return string
	 */
	public function getError(){
		return $this->error;
	}

	/**
	 * @param $code
	 * @return string
	 */
	public function getMsg($code){
		switch($code){
			case -1:
				return ___("Insert gallery error sql.") . debug(implode(", ", $this->db->error()['write']));
			case -2:
				return ___("Insert failed.");
			case -3:
				return ___("title not be empty");
			case -4:
				return ___("delete create a error.") . debug(implode(", ", $this->db->error()['write']));
			case -5:
				return ___("No delete any gallery");
			case -6:
				return ___("No permission on this gallery");
			case -7:
				return ___("comment status error.");
			case -8:
				return ___("Gallery info update error.") . debug(implode(", ", $this->db->error()['write']));
			case -9:
				return ___("Pic list is empty");
			case -10:
				return ___("User own pic list is empty");
			case -11:
				return ___("No new pictures add");
			case -12:
				return ___("Delete gallery pictures make a sql error.") . debug(implode(", ", $this->db->error()['write']));
			case -13:
				return ___("Cancel like gallery error.");
			case -14:
				return ___("Like gallery error.");
			case -15:
				return ___("Gallery get previous and next page id error.");
			case -16:
				return ___("Gallery set public error.");
			case -17:
				return ___("Gallery set draft error.");
			case -18:
				return ___("Gallery must be have a title.");
			case -19:
				return ___("Gallery must be have a description");
			case -20:
				return ___("Gallery must be have a front cover.");
			case -21:
				return ___("Gallery must be have a tag.");
			case -22:
				return ___("Gallery must be have a picture.");
		}
		return ___("Unknown error.");
	}
} 