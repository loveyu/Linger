<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-14
 * Time: 下午4:36
 * Filename: Comment.php
 */

namespace ULib;


use CLib\Ip;
use Core\Log;

/**
 * 评论信息类
 * Class Comment
 * @package ULib
 */
class Comment{
	/**
	 * @var \CLib\Ip|null IP操作实例
	 */
	private $ip_c = NULL;
	/**
	 * @var int 评论ID
	 */
	private $comment_id;
	/**
	 * @var User 用户信息
	 */
	private $user;
	/**
	 * @var string 评论内容
	 */
	private $comment_content;
	/**
	 * @var string 时间
	 */
	private $comment_time;
	/**
	 * @var int|NULL 回复ID
	 */
	private $comment_parent;
	/**
	 * @var int 状态信息
	 */
	private $comment_status;
	/**
	 * @var string IP信息
	 */
	private $comment_ip;
	/**
	 * @var string UA信息
	 */
	private $comment_agent;
	/**
	 * @var int 喜欢数量
	 */
	private $comment_like_count;
	/**
	 * @var int 对评论的点赞数
	 */
	private $comment_top;
	/**
	 * @var int 顶级评论ID
	 */
	private $comment_parent_top;

	/**
	 * @var string 当前用户喜欢该评论的时间
	 */
	private $comment_like_time;

	/**
	 * @var int[] 评论结点信息
	 */
	private $sub_node = [];

	/**
	 * @var Comment[] 评论堆栈
	 */
	private static $comment_stack = [];

	/**
	 * 构造方法，使用评论的信息和对应的用户类
	 * @param array $set_info
	 * @param int   $user_id
	 */
	function __construct($set_info, $user_id){
		$this->ip_c = Ip::getInstance();
		$this->user = User::getUser($user_id);
		if(is_array($set_info) && isset($set_info['comment_id'])){
			static $names = [];
			if(count($names) === 0){
				$ref = new \ReflectionClass($this);
				foreach($ref->getProperties() as $ro){
					$names[] = $ro->getName();
				}
				$names = array_flip($names);
				unset($names['ip_c'], $names['user'], $names['sub_node']);
			}
			if(isset($set_info['comment_ip'])){
				$set_info['comment_ip'] = $this->ip_c->bin2ip($set_info['comment_ip']);
			}
			foreach($set_info as $k => $v){
				if(isset($names[$k])){
					$this->$k = $v;
				}
			}
			self::$comment_stack[$this->getCommentId()] = $this;
		} else{
			Log::write(___("Comment class construct error"), Log::ALERT);
		}
	}

	/**
	 * 从堆栈中获取评论信息
	 * @param int $id
	 * @return null|Comment
	 */
	public static function &getStack($id){
		$rt = NULL;
		if(isset(self::$comment_stack[$id])){
			$rt = & self::$comment_stack[$id];
		}
		return $rt;
	}

	/**
	 * 创建子结点信息
	 * @param int $i
	 */
	public function createSubNode($i){
		$this->sub_node[] = $i;
	}

	/**
	 * @return Comment[]
	 */
	public function &getSubNode(){
		$rt = [];
		foreach($this->sub_node as $v){
			$rt[] = & self::getStack($v);
		}
		return $rt;
	}

	/**
	 * @return string
	 */
	public function getCommentAgent(){
		return $this->comment_agent;
	}

	/**
	 * @return string
	 */
	public function getCommentContent(){
		return $this->comment_content;
	}

	/**
	 * @return int
	 */
	public function getCommentId(){
		return $this->comment_id;
	}

	/**
	 * @return string
	 */
	public function getCommentIp(){
		return $this->comment_ip;
	}

	/**
	 * @return int
	 */
	public function getCommentLikeCount(){
		return $this->comment_like_count;
	}

	/**
	 * @return int|NULL
	 */
	public function getCommentParent(){
		return $this->comment_parent;
	}

	/**
	 * @return int
	 */
	public function getCommentParentTop(){
		return $this->comment_parent_top;
	}

	/**
	 * @return int
	 */
	public function getCommentStatus(){
		return $this->comment_status;
	}

	/**
	 * @return bool 当前用户是否喜欢该评论
	 */
	public function userLikeComment(){
		return !empty($this->comment_like_time);
	}

	/**
	 * @return string
	 */
	public function getCommentLikeTime(){
		return $this->comment_like_time;
	}

	/**
	 * @return string
	 */
	public function getCommentTime(){
		return $this->comment_time;
	}

	/**
	 * @return int
	 */
	public function getCommentTop(){
		return $this->comment_top;
	}

	/**
	 * @return \ULib\User
	 */
	public function getUser(){
		return $this->user;
	}

	/**
	 * 返回对其回复的主题数量
	 * @return int
	 */
	public function getSubCount(){
		return count($this->sub_node);
	}
}