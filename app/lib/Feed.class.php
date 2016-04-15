<?php
/**
 * 动态相关的实例化类
 * User: Loveyu
 * Date: 14-4-6
 * Time: 下午8:41
 */
namespace ULib;

/**
 * Interface FeedInterface
 * @package ULib
 */
interface FeedInterface{
	/**
	 * 获取一个唯一的SID，可以为空即不需要
	 * @return string
	 */
	public function getSid();

	/**
	 * 初始化状态
	 * @return bool
	 */
	public function initStatus();

	/**
	 * 获取用户ID
	 * @return int
	 */
	public function getUserId();

	/**
	 * 返回所需服务器
	 * @return string
	 */
	public function getServer();

	/**
	 * @param array $list
	 */
	public function setServer($list);

	/**
	 * @return string
	 */
	public function getTime();

	/**
	 * @param string $time
	 */
	public function setTime($time);


	/**
	 * 获取对应的信息，用于解析
	 * @return array
	 */
	public function getInfo();

	/**
	 * 获取对应的Action类型
	 * @return string
	 */
	public function getAction();

	/**
	 * 获取资源对象ID
	 * @return int
	 */
	public function getObjUserId();

}

/**
 * Class FeedGallery
 * @package ULib
 */
class FeedGallery implements FeedInterface{
	/**
	 * @var
	 */
	private $gid;
	/**
	 * @var
	 */
	private $uid;
	/**
	 * @var bool
	 */
	private $status = false;
	/**
	 * @var string
	 */
	private $title;
	/**
	 * @var string
	 */
	private $desc;
	/**
	 * @var array|bool
	 */
	private $front_cover = [];
	/**
	 * @var
	 */
	private $server;
	/**
	 * @var array
	 */
	private $tags = [];
	/**
	 * @var bool
	 */
	private $is_update = false;
	/**
	 * @var null
	 */
	private $time = NULL;

	/**
	 * @param $gallery_id
	 * @param $user_id
	 * @param $title
	 * @param $desc
	 * @param $front_cover
	 */
	function __construct($gallery_id, $user_id, $title, $desc, $front_cover){
		$this->gid = +$gallery_id;
		$this->uid = +$user_id;
		$this->title = trim($title);
		$this->desc = trim($desc);
		lib()->load('Tag');
		$this->front_cover = $this->getFrontCoverPath($front_cover);
		if($this->front_cover === false){
			return;
		}

		$tag = new Tag();
		$this->tags = $tag->getGalleryTags($this->gid);
		$this->checkUpdate();
		$this->status = true;
	}

	/**
	 * 检测是否是更新的图集
	 */
	private function checkUpdate(){
		if(db()->has("feed", ['sid' => $this->getSid()])){
			$this->is_update = true;
		}
	}


	/**
	 * @param $id
	 * @return array|bool
	 */
	private function getFrontCoverPath($id){
		$info = db()->get("pictures", [
			'server_name',
			'pic_name',
			'pic_path',
			'pic_description',
			'pic_thumbnails_path',
			'pic_hd_path',
			'pic_display_path',
		], [
			'AND' => [
				'id' => $id,
				'users_id' => $this->uid,
				'pic_status' => 1
			]
		]);
		if(!isset($info['server_name'])){
			return false;
		}
		$this->server = $info['server_name'];
		unset($info['server_name']);
		return $info;
	}

	/**
	 * 初始化状态
	 * @return bool
	 */
	public function initStatus(){
		return $this->status;
	}


	/**
	 * 获取一个唯一的SID，可以为空即不需要
	 * @return string
	 */
	public function getSid(){
		return "FeedGallery_" . $this->gid;
	}

	/**
	 * 获取用户ID
	 * @return int
	 */
	public function getUserId(){
		return $this->uid;
	}

	/**
	 * 返回所需服务器
	 * @return string
	 */
	public function getServer(){
		return $this->server;
	}

	/**
	 * @param array $list
	 */
	public function setServer($list){
		if(isset($list[$this->server])){
			$s = & $list[$this->server];
			$this->front_cover['pic_url'] = $s['url'] . $this->front_cover['pic_path'];
			$this->front_cover['pic_thumbnails_url'] = Feed::getPicOfPath($s['url'], $this->front_cover['pic_thumbnails_path'], $this->front_cover['pic_url']);
			$this->front_cover['pic_hd_url'] = Feed::getPicOfPath($s['url'], $this->front_cover['pic_hd_path'], $this->front_cover['pic_url']);
			$this->front_cover['pic_display_url'] = Feed::getPicOfPath($s['url'], $this->front_cover['pic_display_path'], $this->front_cover['pic_url']);
		}
	}

	/**
	 * @param string $time
	 */
	public function setTime($time){
		$this->time = $time;
	}

	/**
	 * @return string
	 */
	public function getTime(){
		return $this->time;
	}

	/**
	 * 获取对应的信息，用于解析
	 * @return array
	 */
	public function getInfo(){
		return [
			'title' => $this->title,
			'tags' => $this->tags,
			'action' => $this->getAction(),
			'front_cover' => $this->front_cover,
			'users_id' => $this->uid,
			'id' => $this->gid,
			'link' => gallery_link($this->gid),
			'desc' => $this->desc,
			'time' => $this->time,
			'is_update' => $this->is_update
		];
	}

	/**
	 * 获取对应的Action类
	 * @return string
	 */
	public function getAction(){
		return "Gallery";
	}

	/**
	 * 获取资源对象ID
	 * @return int
	 */
	public function getObjUserId(){
		return $this->uid;
	}

}

/**
 * TODO 单张分享模式
 * Class FeedSharePicture
 * @package ULib
 */
class FeedSharePicture extends AppException implements FeedInterface{

	/**
	 * @var array
	 */
	private $info = [];
	/**
	 * @var
	 */
	private $server;
	/**
	 * @var bool
	 */
	private $status = false;
	/**
	 * @var
	 */
	private $s_uid;
	/**
	 * @var
	 */
	private $o_uid;
	/**
	 * @var
	 */
	private $pid;
	/**
	 * @var null
	 */
	private $time = NULL;

	/**
	 * @param $pid
	 * @param $uid
	 */
	function __construct($pid, $uid){
		lib()->load('Picture');
		$pic = new Picture();
		$info = $pic->get_simple_pic($pid);
		if(!isset($info['pic_status'])){
			$this->throwMsg(-2);
		}
		if($info['pic_status'] < 0){
			$this->throwMsg(-1);
		}
		$this->pid = +$pid;
		User::getUser($uid);
		$this->s_uid = +$uid;
		$this->info[] = [
			'id' => $info['pic_id'],
			'name' => $info['pic_name'],
			'desc' => $info['pic_description'],
			'thumbnail' => $info['pic_thumbnails_path'],
			'display' => $info['pic_display_path'],
			'pic_path' => $info['pic_path']
		];
		$this->server = $info['server_name'];
		$this->o_uid = +$info['user_id'];
		$this->status = true;
	}

	/**
	 * 获取一个唯一的SID，可以为空即不需要
	 * @return string
	 */
	public function getSid(){
		return "SharePicture_" . $this->pid;
	}

	/**
	 * 初始化状态
	 * @return bool
	 */
	public function initStatus(){
		return $this->status;
	}

	/**
	 * 获取用户ID
	 * @return int
	 */
	public function getUserId(){
		return $this->s_uid;
	}

	/**
	 * 返回所需服务器
	 * @return string
	 */
	public function getServer(){
		return $this->server;
	}

	/**
	 * @param array $list
	 */
	public function setServer($list){
		if(isset($list[$this->server])){
			$s = & $list[$this->server];
			foreach($this->info as &$v){
				$v['pic_url'] = $s['url'] . $v['pic_path'];
				$v['thumbnail_url'] = Feed::getPicOfPath($s['url'], $v['thumbnail'], $v['pic_url']);
				$v['display_url'] = Feed::getPicOfPath($s['url'], $v['display'], $v['pic_url']);
				$v['link'] = picture_link($v['id']);
			}
		}
	}

	/**
	 * @return string
	 */
	public function getTime(){
		return $this->time;
	}

	/**
	 * @param string $time
	 */
	public function setTime($time){
		$this->time = $time;
	}

	/**
	 * 获取对应的信息，用于解析
	 * @return array
	 */
	public function getInfo(){
		return [
			'info' => $this->info,
			'action' => $this->getAction(),
			'share_users_id' => $this->s_uid,
			'object_users_id' => $this->o_uid,
			'time' => $this->time,
			'id' => $this->pid
		];
	}

	/**
	 * 获取对应的Action类型
	 * @return string
	 */
	public function getAction(){
		return "SharePicture";
	}

	/**
	 * 获取资源对象ID
	 * @return int
	 */
	public function getObjUserId(){
		return $this->o_uid;
	}

	/**
	 * 获取异常信息
	 * @param int $code
	 * @return mixed
	 */
	public function getMsg($code){
		switch((int)$code){
			case -1:
				return ___("This picture share are close.");
			case -2:
				return ___("This picture are not found.");
		}
		return ___("Unknown error.");
	}
}

/**
 * Class FeedShareGallery
 * @package ULib
 */
class FeedShareGallery extends AppException implements FeedInterface{
	private $time;
	private $o_uid;
	private $s_uid;
	private $gid;
	private $title;
	private $desc;
	private $front_cover;
	private $server;
	private $status = false;

	function __construct($gid, $user){
		$this->s_uid = +$user;
		$this->gid = +$gid;
		lib()->load('Gallery');
		$info = Gallery::getSimpleInfo($gid);
		if(!isset($info['gallery_status'])){
			$this->throwMsg(-1);
		}
		if($info['gallery_status'] < 1){
			$this->throwMsg(-2);
		}
		$this->title = $info['gallery_title'];
		$this->desc = $info['gallery_description'];
		$this->o_uid = +$info['users_id'];
		if($info['gallery_front_cover'] > 0){
			$front = db()->get("pictures", [
				'server_name',
				'pic_name',
				'pic_path',
				'pic_description',
				'pic_thumbnails_path',
				'pic_hd_path',
				'pic_display_path',
			], [
				'AND' => [
					'id' => $info['gallery_front_cover'],
					'users_id' => $this->o_uid,
					'pic_status' => 1
				]
			]);
			if(isset($front['server_name'])){
				$this->server = $front['server_name'];
				unset($front['server_name']);
				$this->front_cover = $front;
			}
		}
		$this->status = true;
	}


	/**
	 * 获取异常信息
	 * @param int $code
	 * @return mixed
	 */
	public function getMsg($code){
		switch($code){
			case -1:
				return ___("Gallery are not found.");
			case -2:
				return ___("Gallery share are close.");
		}
		return ___("Unknown error.");
	}

	/**
	 * 获取一个唯一的SID，可以为空即不需要
	 * @return string
	 */
	public function getSid(){
		return "ShareGallery_" . $this->gid;
	}

	/**
	 * 初始化状态
	 * @return bool
	 */
	public function initStatus(){
		return $this->status;
	}

	/**
	 * 获取用户ID
	 * @return int
	 */
	public function getUserId(){
		return $this->s_uid;
	}

	/**
	 * 返回所需服务器
	 * @return string
	 */
	public function getServer(){
		return $this->server;
	}

	/**
	 * @param array $list
	 */
	public function setServer($list){
		if(!empty($this->server) && isset($list[$this->server])){
			$s = & $list[$this->server];
			$this->front_cover['pic_url'] = $s['url'] . $this->front_cover['pic_path'];
			$this->front_cover['pic_thumbnails_url'] = Feed::getPicOfPath($s['url'], $this->front_cover['pic_thumbnails_path'], $this->front_cover['pic_url']);
			$this->front_cover['pic_hd_url'] = Feed::getPicOfPath($s['url'], $this->front_cover['pic_hd_path'], $this->front_cover['pic_url']);
			$this->front_cover['pic_display_url'] = Feed::getPicOfPath($s['url'], $this->front_cover['pic_display_path'], $this->front_cover['pic_url']);
		}
	}

	/**
	 * @return string
	 */
	public function getTime(){
		return $this->time;
	}

	/**
	 * @param string $time
	 */
	public function setTime($time){
		$this->time = $time;
	}

	/**
	 * 获取对应的信息，用于解析
	 * @return array
	 */
	public function getInfo(){
		return [
			'title' => $this->title,
			'desc' => $this->desc,
			'front_cover' => $this->front_cover,
			's_uid' => (int)$this->s_uid,
			'o_uid' => (int)$this->o_uid,
			'time' => $this->time,
			'gid' => $this->gid,
			'action' => $this->getAction(),
			'link' => gallery_link($this->gid)
		];
	}

	/**
	 * 获取对应的Action类型
	 * @return string
	 */
	public function getAction(){
		return "ShareGallery";
	}

	/**
	 * 获取资源对象ID
	 * @return int
	 */
	public function getObjUserId(){
		return (int)$this->o_uid;
	}

}

class FeedTalk extends AppException implements FeedInterface{
	private $content;
	private $status = false;
	private $uid;
	private $time;

	function __construct($content, $uid){
		$content = trim($content);
		if(($l = strlen($content)) < 1){
			$this->throwMsg(-1);
		}
		if($l > 288){
			$this->throwMsg(-2);
		}
		$uid = (int)$uid;
		User::getUser($uid);
		$this->content = $content;
		$this->uid = $uid;
		$this->status = true;
	}


	/**
	 * 获取异常信息
	 * @param int $code
	 * @return mixed
	 */
	public function getMsg($code){
		switch($code){
			case -1:
				return ___("Share content is empty.");
			case -2:
				return ___("Share content is to long.");
		}
		return ___("Unknown error.");
	}

	/**
	 * 获取一个唯一的SID，可以为空即不需要
	 * @return string
	 */
	public function getSid(){
		//无SID
		return "";
	}

	/**
	 * 初始化状态
	 * @return bool
	 */
	public function initStatus(){
		return $this->status;
	}

	/**
	 * 获取用户ID
	 * @return int
	 */
	public function getUserId(){
		return $this->uid;
	}

	/**
	 * 返回所需服务器
	 * @return string
	 */
	public function getServer(){
		return NULL;
	}

	/**
	 * @param array $list
	 */
	public function setServer($list){
	}

	/**
	 * @return string
	 */
	public function getTime(){
		return $this->time;
	}

	/**
	 * @param string $time
	 */
	public function setTime($time){
		$this->time = $time;
	}

	/**
	 * 获取对应的信息，用于解析
	 * @return array
	 */
	public function getInfo(){
		return [
			'action' => $this->getAction(),
			'content' => $this->parseContent(),
			'time' => $this->time,
			'user' => $this->uid
		];
	}

	private function parseContent(){
		return get_markdown($this->content);
	}

	/**
	 * 获取对应的Action类型
	 * @return string
	 */
	public function getAction(){
		return "Talk";
	}

	/**
	 * 获取资源对象ID
	 * @return int
	 */
	public function getObjUserId(){
		return $this->uid;
	}

}