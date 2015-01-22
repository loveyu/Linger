<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-2-26
 * Time: 下午7:25
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace ULib;


use CLib\Image;
use CLib\Upload;
use Core\Log;

/**
 * Class Picture
 * @package ULib
 */
class Picture{

	/**
	 * 添加上传一张图片
	 * @param array $names
	 * @param array $tags
	 * @param array $desc
	 * @param array $files
	 * @param array $server
	 * @param User  $user
	 * @return array
	 */
	public function add($names, $tags, $desc, $files, $server, $user){
		$this->checkServer($server);
		$n_c = count($names);
		$t_c = count($tags);
		$d_c = count($desc);
		$f_c = isset($files['name']) ? count($files['name']) : 0;
		if($t_c > 20){
			$this->throwMsg(-3);
		}
		if($tags == 0 || $t_c !== $d_c || $d_c !== $f_c || $t_c !== $f_c || $n_c != $t_c){
			$this->throwMsg(-2);
		}
		unset($n_c, $t_c, $d_c, $f_c);
		c_lib()->load('upload');
		$lib = $server['meta']['_Lib'] . "";
		$upload = new Upload([
			'exts' => [
				'jpg',
				'png',
				'gif'
			],
			'sub_status' => true,
			'replace' => false,
			'max_size' => 5 * 1024 * 1024,
			'image_info' => true
		], $lib, $server['meta']);
		$info = $upload->upload([$files]);
		for($i = 0; $i < count($info); $i++){
			$info[$i]['key'] = $this->getIndexOfArray($info[$i]['name'], $files['name']);
		}
		$rt = [
			'list' => [],
			'error' => []
		];
		foreach($info as $v){
			$tmp = $this->insert_data($v['save_path'] . $v['save_name'], $server, $names[$v['key']], $tags[$v['key']], $desc[$v['key']], $user->getId(), $v['image']['width'], $v['image']['height']);
			if(is_int($tmp)){
				$rt['list'][] = $tmp;
			} else{
				$rt['error'][] = $tmp;
			}
		}
		return $rt;
	}


	/**
	 * 删除一张图片
	 * @param int|array $pic_id
	 * @param int       $user_id
	 */
	public function delete($pic_id, $user_id){
		$pic_id = intval($pic_id);
		$user_id = intval($user_id);
		$info = $this->get($pic_id, $user_id);
		if(!isset($info['pic_id'])){
			$this->throwMsg(-5);
		}
		if(db()->has("gallery", ['gallery_front_cover' => $pic_id])){
			$this->throwMsg(-13);
		}
		$db = db()->getWriter();
		$db->pdo->beginTransaction();
		$rt = hook()->apply("Picture_delete", true, $pic_id, $user_id, $db);
		if($rt !== true){
			$db->pdo->rollBack();
			$this->throwMsg(-4);
		}
		$id = $db->quote($info['pic_id']);
		$d_c = $db->exec("delete from `comments` where `id` in (select `comments_id` from `pictures_has_comments` where `pictures_id`=$id);");
		if($d_c === false){
			$db->pdo->rollBack();
			$this->throwMsg(-4);
		}
		$d_p = $db->delete("pictures", [
			'AND' => ['id' => $pic_id],
			'users_id' => $user_id
		]);
		if($d_p === false){
			$db->pdo->rollBack();
			$this->throwMsg(-4);
		}
		$db->pdo->commit();
		$this->deleteFile($info);
	}

	/**
	 * 对图片文件进行处理
	 * @param $info
	 */
	public function deleteFile($info){
		lib()->load('Server');
		$server = new Server();
		$meta = $server->get($info['server_name'])[0]['meta'];
		if(isset($meta['_Lib'])){
			switch($meta['_Lib']){
				case "Local":
					lib()->load('FileAction');
					$fa = new FileAction($meta);
					$fa->delete($info['pic_path'], $info['pic_thumbnails_path'], $info['pic_hd_path'], $info['pic_display_path']);
					break;
				case 'Qiniu':
					c_lib()->load('upLoad/Qiniu');
					$qiniu = new Upload\Qiniu("", $meta);
					$path = $info['pic_path'];
					$status = $qiniu->qiniu->del($path);
					if($status === false){
						Log::write("{$info['server_name']} Delete error:" . $path, Log::NOTICE);
					}
					$thumb = $info['pic_thumbnails_path'];
					if(false !== strpos($thumb, '/')){
						$status = $qiniu->qiniu->del($thumb);
						if($status === false){
							Log::write("{$info['server_name']} Delete error:" . $thumb, Log::NOTICE);
						}
					}
					$hd = $info['pic_hd_path'];
					if(false !== strpos($hd, '/')){
						$status = $qiniu->qiniu->del($hd);
						if($status === false){
							Log::write("{$info['server_name']} Delete error:" . $hd, Log::NOTICE);
						}
					}
					$dis = $info['pic_display_path'];
					if(false !== strpos($dis, '/')){
						$status = $qiniu->qiniu->del($dis);
						if($status === false){
							Log::write("{$info['server_name']} Delete error:" . $dis, Log::NOTICE);
						}
					}
					break;
				default:
					Log::write("Now File Lib not found.Please check {$info['server_name']} and {$info['pic_path']}");
			}
		} else{
			Log::write("Now File Lib not set.Please check {$info['server_name']} and {$info['pic_path']}");
		}
	}


	/**
	 * 选择用户图片列表
	 * @param int $user_id
	 * @param int $page
	 * @param int $number
	 * @throws \Exception
	 * @return mixed
	 */
	public function select($user_id, $page = 1, $number = 15){
		$user_id = intval($user_id);
		$page = intval($page);
		if($page < 1){
			$page = 1;
		}
		if(empty($number)){
			$number = 15;
		} else{
			$number = intval($number);
			if($number < 2){
				$number = 2;
			}
		}
		if($user_id < 1){
			$this->throwMsg(-9);
		}
		$rt = [
			'list' => [],
			'max' => 1,
			'now' => $page,
			'number' => $number,
			'count' => 0,
			'error' => false
		];
		$db = db()->getReader();
		$rt['count'] = $db->count("pictures", ['users_id' => $user_id]);
		if($rt['count'] < 1){
			//图片未找到
			$this->throwMsg(-6);
		}
		$rt['max'] = ceil($rt['count'] / $rt['number']);
		if($rt['max'] < $rt['now']){
			//大于最大页
			return $rt;
		}
		$list = db()->select("pictures", [
			'[><]server' => ['server_name' => 'name']
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
			'pictures.users_id' => $user_id,
			'ORDER' => 'pictures.id DESC',
			'LIMIT' => [
				$rt['number'] * ($rt['now'] - 1),
				$rt['number']
			]
		]);
		if($list === false){
			$this->throwMsg(-10);
		}
		$this->list_add_tags($list);
		$this->parsePic($list, false);
		$rt['list'] = $list;
		return $rt;
	}

	/**
	 * @param int $number
	 * @return array|bool
	 */
	public function select_new_pic($number = 15){
		$number = intval($number);
		if($number < 1){
			$number = 1;
		}
		$list = db()->select("pictures", [
			'[><]server' => ['server_name' => 'name']
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
			'ORDER' => 'pictures.id DESC',
			'LIMIT' => [
				0,
				$number
			]
		]);
		if($list === false){
			return [];
		}
		$this->parsePic($list, false);
		return $list;
	}

	/**
	 * 对图片列表添加标签
	 * @param array $list
	 */
	public function list_add_tags(&$list){
		$ids = [];
		foreach($list as $v){
			$ids[] = $v['pic_id'];
		}
		$tmp = [];

		if(count($ids) > 0){
			$tags = db()->select("pictures_has_tags", [
				'pictures_id',
				'tags_name'
			], ['pictures_id' => $ids]);
			foreach($tags as $v){
				if(!isset($tmp[$v['pictures_id']])){
					$tmp[$v['pictures_id']] = [];
				}
				$tmp[$v['pictures_id']][] = $v['tags_name'];
			}
			unset($tags);
		}
		for($i = 0, $l = count($list); $i < $l; $i++){
			if(isset($tmp[$list[$i]['pic_id']])){
				$list[$i]['pic_tags'] = $tmp[$list[$i]['pic_id']];
			} else{
				$list[$i]['pic_tags'] = [];
			}
		}
	}

	/**
	 * 获取一张图片的信息
	 * @param int $id
	 * @return bool|array
	 */
	public function get_pic($id){
		$info = db()->select("pictures", [
			'[><]server' => ['server_name' => 'name'],
			'[><]users' => ['users_id' => 'id'],
			'[>]users_like_pictures' => [
				'id' => 'pictures_id',
				'______' => ['users_like_pictures.users_id' => is_login() ? login_user()->getId() : 0]
			]
		], [
			'pictures.id' => 'pic_id',
			'pictures.users_id' => 'user_id',
			'pictures.server_name' => 'server_name',
			'server.url' => 'server_url',
			'users.user_name' => 'user_name',
			'users.user_url' => 'user_url',
			'users.user_aliases' => 'user_aliases',
			'users.user_status' => 'user_status',
			'users.user_avatar' => 'user_avatar',
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
			'pictures.pic_like_count' => 'pic_like_count',
			'pictures.pic_display_path' => 'pic_display_path',
			'pictures.pic_display_width' => 'pic_display_width',
			'pictures.pic_display_height' => 'pic_display_height',
			'users_like_pictures.like_time' => 'pic_like_time'
		], ['pictures.id' => intval($id)]);
		if(count($info) !== 1){
			return false;
		}
		$this->list_add_tags($info);
		$this->parsePic($info);
		if(isset($info[0])){
			list($info[0]['previous_id'], $info[0]['next_id']) = $this->getNextPrevious($info[0]['pic_id']);
			return $info[0];
		} else{
			return false;
		}
	}

	/**
	 * 获取上一张和下一张图片
	 * @param $id
	 * @return array
	 */
	private function getNextPrevious($id){
		$db = db()->getReader();
		$rt = [
			0 => $db->max("pictures", 'id', ['id[<]' => $id]),
			1 => $db->min("pictures", 'id', ['id[>]' => $id]),
		];
		return $rt;
	}

	/**
	 * 获取图片的列表
	 * @param string|int $ids
	 * @param int        $user_id
	 * @return array
	 */
	public function get($ids, $user_id){
		$flag = false;
		if(!is_numeric($ids)){
			$ids = array_flip(array_flip(array_map('intval', explode(",", $ids))));
		} else{
			$ids = intval($ids);
			$flag = true;
		}
		$ps = db()->select("pictures", [
			'[><]server' => ['server_name' => 'name'],
			'[><]users' => ['users_id' => 'id']
		], [
			'pictures.id' => 'pic_id',
			'pictures.users_id' => 'user_id',
			'pictures.server_name' => 'server_name',
			'server.url' => 'server_url',
			'users.user_name' => 'user_name',
			'users.user_url' => 'user_url',
			'users.user_aliases' => 'user_aliases',
			'users.user_status' => 'user_status',
			'users.user_avatar' => 'user_avatar',
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
				'pictures.users_id' => $user_id,
				'pictures.id' => $ids
			]
		]);
		//重构之前的代码
		//$tags = db()->select("pictures_has_tags", [
		//	'pictures_id',
		//	'tags_name'
		//], ['pictures_id' => $ids]);
		//$tmp = [];
		//foreach($tags as $v){
		//	if(!isset($tmp[$v['pictures_id']])){
		//		$tmp[$v['pictures_id']] = [];
		//	}
		//	$tmp[$v['pictures_id']][] = $v['tags_name'];
		//}
		//unset($tags);
		//for($i = 0, $l = count($ps); $i < $l; $i++){
		//	if(isset($tmp[$ps[$i]['pic_id']])){
		//		$ps[$i]['pic_tags'] = $tmp[$ps[$i]['pic_id']];
		//	} else{
		//		$ps[$i]['pic_tags'] = [];
		//	}
		//}
		$this->parsePic($ps);
		$this->list_add_tags($ps);
		if($flag && isset($ps[0])){
			return $ps[0];
		} else{
			return $ps;
		}
	}

	public function get_simple_pic($pic_id){
		$ps = db()->select("pictures", [
			'[><]server' => ['server_name' => 'name'],
		], [
			'pictures.id' => 'pic_id',
			'pictures.users_id' => 'user_id',
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
			'pictures.pic_display_height' => 'pic_display_height',
			'pictures.pic_like_count' => 'pic_like_count'
		], [
			'pictures.id' => intval($pic_id)
		]);
		if(!isset($ps[0])){
			Log::write(_("get simple picture info error."), Log::SQL);
			return [];
		}
		$this->parsePic($ps, false);
		return $ps[0];
	}


	/**
	 * 删除一个单独的标签
	 * @param string $pic_id
	 * @param string $tag_name
	 * @param string $user_id
	 */
	public function remove_tag($pic_id, $tag_name, $user_id){
		$pic_id = intval($pic_id);
		$user_id = intval($user_id);
		$tag_name = trim($tag_name);
		$this->picOwnerCheck($pic_id, $user_id);
		lib()->load('Tag');
		$tag = new Tag();
		$tag->del_tag($pic_id, $tag_name, "pictures");
	}

	/**
	 * 添加一个标签
	 * @param string $pic_id
	 * @param string $tag
	 * @param string $user_id
	 * @return array | null
	 */
	public function add_tag($pic_id, $tag, $user_id){
		$pic_id = intval($pic_id);
		$user_id = intval($user_id);
		$tag_name = trim($tag);
		$this->picOwnerCheck($pic_id, $user_id);
		lib()->load('Tag');
		$tag = new Tag();
		return $tag->add_tag($pic_id, $tag_name, "pictures");
	}

	/**
	 * 编辑图片信息
	 * @param int    $pic_id
	 * @param int    $user_id
	 * @param string $desc
	 * @param int    $status
	 * @param string $name
	 */
	public function edit_info($pic_id, $user_id, $desc, $status, $name){
		$pic_id = intval($pic_id);
		$user_id = intval($user_id);
		$status = intval($status);
		$this->picOwnerCheck($pic_id, $user_id);
		if(!in_array($status, [
			0,
			1
		])
		){
			$this->throwMsg(-7);
		}
		if(db()->update("pictures", [
				'pic_status' => $status,
				'pic_description' => trim($desc),
				'pic_name' => trim($name),
			], ['id' => $pic_id]) === false
		){
			$this->throwMsg(-8);
		}
	}

	/**
	 * 喜欢或取消喜欢某一图片
	 * @param int $pic_id
	 * @param int $user_id
	 */
	public function like($pic_id, $user_id){
		$db = db();
		$pic_id = intval($pic_id);
		$user_id = intval($user_id);
		if($db->has("users_like_pictures", [
			'AND' => [
				'users_id' => $user_id,
				'pictures_id' => $pic_id
			]
		])
		){
			if($db->delete("users_like_pictures", [
					'AND' => [
						'users_id' => $user_id,
						'pictures_id' => $pic_id
					]
				]) === false
			){
				$this->throwMsg(-11);
			}
			hook()->apply('Picture_unlike', NULL, $pic_id, $user_id);
		} else{
			if($db->insert("users_like_pictures", [
					'users_id' => $user_id,
					'pictures_id' => $pic_id,
					'like_time' => date("Y-m-d H:i:s")
				]) < 0
			){
				Log::write(_("Like picture error."), Log::SQL);
				$this->throwMsg(-12);
			}
			hook()->apply('Picture_like', NULL, $pic_id, $user_id);
		}
	}

	/**
	 * 检测用户是否拥有图片
	 * @param int $pic_id
	 * @param int $user_id
	 */
	public function picOwnerCheck($pic_id, $user_id){
		if(!db()->has("pictures", [
			'AND' => [
				'id' => $pic_id,
				'users_id' => $user_id
			]
		])
		){
			$this->throwMsg(-6);
		}
	}

	/**
	 * 对数组信息进行解析，和增加部分信息
	 * @param array $list
	 * @param bool  $convert_avatar 是否对头像进行处理
	 * @return mixed
	 */
	public function parsePic(&$list, $convert_avatar = true){
		if($convert_avatar){
			lib()->load('User', 'Avatar');
		}
		for($i = 0, $l = count($list); $i < $l; $i++){
			if(!empty($list[$i]['user_avatar'])){
				if($convert_avatar){
					$list[$i]['user_avatar'] = Avatar::get($list[$i]['user_avatar'], User::getUser($list[$i]['user_id']));
				}
			}
			if(empty($list[$i]['server_url'])){
				continue;
			}
			$list[$i]['pic_url'] = $this->makePictureUrl($list[$i]['server_url'], $list[$i]['pic_path'], $list[$i]['pic_path']);
			$list[$i]['pic_thumbnails_url'] = $this->makePictureUrl($list[$i]['server_url'], $list[$i]['pic_thumbnails_path'], $list[$i]['pic_path']);
			$list[$i]['pic_hd_url'] = $this->makePictureUrl($list[$i]['server_url'], $list[$i]['pic_hd_path'], $list[$i]['pic_path']);
			$list[$i]['pic_display_url'] = $this->makePictureUrl($list[$i]['server_url'], $list[$i]['pic_display_path'], $list[$i]['pic_path']);
		}
		return $list;
	}

	/**
	 * 创建图片URL地址
	 * @param string $sever_url
	 * @param string $server_path
	 * @param string $pic_path
	 * @return string
	 */
	private function makePictureUrl($sever_url, $server_path, $pic_path){
		if(substr($sever_url, 0, 4) != "http"){
			$sever_url = (is_ssl()?"https:":"http:").$sever_url;
		}
		switch($server_path){
			case "thumbnail":
			case "hd":
			case "display":
				return $sever_url . $pic_path . "/" . $server_path;
		}
		return $sever_url . $server_path;
	}


	/**
	 * 向数据库插入数据
	 * @param string $path
	 * @param array  $server
	 * @param string $names
	 * @param string $tags
	 * @param string $desc
	 * @param int    $user_id
	 * @param int    $width
	 * @param int    $height
	 * @return mixed
	 */
	private function insert_data($path, $server, $names, $tags, $desc, $user_id, $width, $height){
		//id, users_id, server_name, pic_path, pic_width, pic_height, pic_description,
		//pic_thumbnails_path, pic_thumbnails_width, pic_thumbnails_height,
		//pic_hd_path, pic_hd_width, pic_hd_height, pic_create_time, pic_status, pic_comment_count
		//pic_display_path, pic_display_width, pic_display_height
		$data = [];
		$queue_flag = false;
		if($server['meta']['_Lib'] === 'Local'){
			$data = $this->makeThumbnail($path, $server['meta']);
		} else{
			$queue_flag = true;
		}
		$insert = [
			'users_id' => $user_id,
			'server_name' => $server['name'],
			'pic_path' => $path,
			'pic_name' => $names,
			'pic_width' => $width,
			'pic_height' => $height,
			'pic_description' => $desc,
			'pic_create_time' => date("Y-m-d H:i:s"),
			'pic_thumbnails_path' => "thumbnail",
			'pic_thumbnails_width' => $width,
			'pic_thumbnails_height' => $height,
			'pic_hd_path' => "hd",
			'pic_hd_width' => $width,
			'pic_hd_height' => $height,
			'pic_display_path' => 'display',
			'pic_display_width' => $width,
			'pic_display_height' => $height,
			'pic_comment_count' => 0,
			'pic_status' => 1
		];
		$insert = array_merge($insert, $data);
		$id = db()->insert("pictures", $insert);
		if($id < 0){
			return db()->error()['write'];
		} else{
			if($queue_flag){
				$this->queueThumbnail($id, $server['meta']);
			}
		}
		try{
			lib()->load('Tag');
			$tag = new Tag();
			$tag->pic_set($id, $tags);
		} catch(\Exception $ex){
			Log::write(_("Picture tag can not set on insert."), Log::NOTICE);
		}
		return $id;
	}

	/**
	 * 使用其他图片服务时的队列处理
	 * @param $id
	 * @param $server_meta
	 */
	private function queueThumbnail($id, $server_meta){
	}

	/**
	 * 对本地图片做缩略图处理
	 * @param string $path
	 * @param array  $server_meta
	 * @return array
	 */
	private function makeThumbnail($path, $server_meta){
		$rt = [];
		if(isset($server_meta['server_root_path'])){
			$root = $server_meta['server_root_path'];
			$img_path = $root . "/" . $path;
			if(is_file($img_path) && is_readable($img_path)){
				try{
					c_lib()->load('image');
					$img = new Image(Image::IMAGE_GD, $img_path);
					$img_w = $img->width();
					$img_h = $img->height();
					$x = $img_w / $img_h;

					//创建高清图
					if($img_w > image_hd_width()){
						$img->thumb(image_hd_width(), image_hd_width() / $x, Image::IMAGE_THUMB_SCALE);
						$t_path = "hd/" . dirname($path);
						$this->createPath($root . "/" . $t_path);
						$img->save($root . "/hd/" . $path);
						$rt['pic_hd_path'] = "hd/" . $path;
						$rt['pic_hd_width'] = $img->width();
						$rt['pic_hd_height'] = $img->height();
					} else{
						$rt['pic_hd_path'] = $path;
					}

					//创建显示图
					if($img_w > image_display_width()){
						$img->thumb(image_display_width(), image_display_width() / $x, Image::IMAGE_THUMB_SCALE);
						$t_path = "display/" . dirname($path);
						$this->createPath($root . "/" . $t_path);
						$img->save($root . "/display/" . $path);
						$rt['pic_display_path'] = "display/" . $path;
						$rt['pic_display_width'] = $img->width();
						$rt['pic_display_height'] = $img->height();
					} else{
						$rt['pic_display_path'] = $path;
					}

					//创建缩略图
					$img_w = $img->width();
					$c_w = image_thumbnail_width();
					$c_h = image_thumbnail_height();
					$img_h = $img->height();
					if($img_w < $c_w){ //目标图片宽度小于缩略图
						$img_w = $c_w;
						$img_h = $img_h * $c_w / $img_w;
					}
					if($img_h < $c_h){ //目标图片高度小于缩略图
						$img_h = $c_h;
						$img_w = $img_w * $c_h / $img_h;
					}
					//if($img_w / $img_h < $c_w / $c_h){
					if($img_w * $c_h < $c_w * $img_h){ //化简表达
						$x_w = $img_w;
						$x_h = ceil($x_w * $c_h / $c_w);
					} else{
						$x_h = $img_h;
						$x_w = ceil($x_h * $c_w / $c_h);
					}
					Log::write(print_r(get_defined_vars(), true));
					$img->thumb($x_w, $x_h, Image::IMAGE_THUMB_CENTER);
					Log::write(print_r(get_defined_vars(), true));
					//$img->thumb($c_w, $c_h, Image::IMAGE_THUMB_SCALE);
					$t_path = "thumbnail/" . dirname($path);
					$this->createPath($root . "/" . $t_path);
					$img->save($root . "/thumbnail/" . $path);
					$rt['pic_thumbnails_path'] = "thumbnail/" . $path;
					$rt['pic_thumbnails_width'] = $c_w;
					$rt['pic_thumbnails_height'] = $c_h;
				} catch(\Exception $ex){
					return [];
				}
			}
		}
		return $rt;
	}

	/**
	 * @param $path
	 * @throws \Exception
	 */
	private function createPath($path){
		if(!is_dir($path)){
			if(!mkdir($path, 0777, true)){
				throw new \Exception(_("Path create error."));
			}
		}
	}

	/**
	 * @param $name
	 * @param $arr
	 * @return int
	 */
	private function getIndexOfArray($name, $arr){
		for($i = 0; $i < count($arr); $i++){
			if($arr[$i] === $name){
				return $i;
			}
		}
		return -1;
	}


	/**
	 * @param $server
	 */
	private function checkServer($server){
		if(!is_array($server) || !isset($server['name']) || !isset($server['url']) || !isset($server['meta']['_Lib'])){
			$this->throwMsg(-1);
		}
	}

	/**
	 * @param $code
	 * @throws \Exception
	 */
	private function throwMsg($code){
		$code = intval($code);
		throw new \Exception($this->getMsg($code), $code);
	}

	/**
	 * @param $code
	 * @return string
	 */
	public function getMsg($code){
		switch($code){
			case -1:
				return _("Server info check Error.");
			case -2:
				return _("Upload param error.");
			case -3:
				return _("A maximum of 20 allowed to upload pictures.");
			case -4:
				return _("Delete picture error.") . debug(implode(", ", db()->error()['write']));
			case -5:
				return _("Picture not found.");
			case -6:
				return _("Picture not found on the user");
			case -7:
				return _("status code error");
			case -8:
				return _("Update picture info error.") . debug(implode(", ", db()->error()['write']));
			case -9:
				return _("User id error on select pictures.");
			case -10:
				return _("Select pictures error on sql.") . debug(implode(", ", db()->error()['read']));
			case -11:
				return _("Cancel like fail.");
			case -12:
				return _("Like fail.");
			case -13:
				return _("First change gallery front cover, than delete it!");
		}
		return _("Unknown Error.");
	}
}