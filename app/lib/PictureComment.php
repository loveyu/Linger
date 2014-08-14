<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-12
 * Time: 下午12:58
 * Filename: PictureComment.php
 */

namespace ULib;

if(!class_exists('CommentData')){
	lib()->load('CommentData');
}

/**
 * Class PictureComment
 * @package ULib
 */
class PictureComment extends CommentData{
	/**
	 * @var Router 路由信息类
	 */
	private $router;

	/**
	 * @var array 图片的信息
	 */
	private $picture_info = NULL;

	/**
	 * 构造函数
	 * @param int      $pic_id 图片ID
	 * @param int|bool $page   当前页面数
	 * @param array    $info   图片的相关信息
	 * @throws \Exception
	 */
	function __construct($pic_id, $page = false, $info = NULL){
		parent::__construct("pictures_has_comments", "pictures_id", intval($pic_id), "pictures");
		$this->page = intval($page);
		$this->action_method = "post_picture";
		$this->router = lib()->using('router');
		if($info === NULL){
			lib()->load('Picture');
			$pic = new Picture();
			$this->picture_info = $pic->get_simple_pic($pic_id);
		} else{
			$this->picture_info = $info;
		}
		if(!isset($this->picture_info['pic_id'])){
			throw new \Exception("Picture comment load error.");
		}
	}

	/**
	 * 检查是否允许评论
	 * @param int $id
	 * @return bool
	 */
	function checkAllowComment($id){
		return $this->picture_info['pic_status'] > 0;
	}

	/**
	 * 获取该条评论状态的抽象函数
	 * @param array $info 评论数组信息
	 * @return int
	 */
	function get_comment_status($info){
		return 1;
	}

	/**
	 * 获取该对象的用户ID
	 * @return int
	 */
	function get_object_users_id(){
		return $this->picture_info['user_id'];
	}


	/**
	 * 返回评论页面的固定连接
	 * @param int $number
	 * @return string
	 */
	function get_comment_pager($number){
		return get_url($this->router->getLink('picture_pager', $this->id, $number));
	}

	/**
	 * 对评论可添加的抽象钩子调用
	 * @param string $content    评论内容
	 * @param int    $parent     回复ID
	 * @param int    $parent_top 顶级ID
	 * @param int    $user_id    用户ID
	 * @return mixed 返回值
	 */
	function comment_hook($content, $parent, $parent_top, $user_id){
		return hook()->apply('PictureComment_comment', NULL, $this->id, $user_id, $parent, $parent_top, $content, $this->picture_info);
	}

}