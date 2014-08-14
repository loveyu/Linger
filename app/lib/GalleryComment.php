<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-19
 * Time: 下午1:46
 * Filename: GalleryComment.php
 */

namespace ULib;

if(!class_exists('CommentData')){
	lib()->load('CommentData');
}

class GalleryComment extends CommentData{
	/**
	 * @var Router 路由信息类
	 */
	private $router;

	/**
	 * @var array 图集信息
	 */
	private $gallery_info;

	function __construct($g_id, $page = false, $info = NULL){
		parent::__construct("gallery_has_comments", "gallery_id", intval($g_id), "gallery");
		$this->page = intval($page);
		$this->action_method = "post_gallery";
		$this->router = lib()->using('router');
		if(!is_array($info)){
			$this->gallery_info = $this->db->get("gallery", [
				"gallery_comment_status",
				//根据Gallery类中此处返回的为user_id
				'users_id' => 'user_id',
				'gallery_title',
				'gallery_status'
			], ['id' => $g_id]);
		} else{
			$this->gallery_info = $info;
		}
	}

	/**
	 * 检查是否允许评论
	 * @param int $id
	 * @return bool
	 */
	function checkAllowComment($id){
		return $this->gallery_info['gallery_comment_status'] > 0 && $this->gallery_info['gallery_status'] > 0;
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
		return $this->gallery_info['user_id'];
	}


	/**
	 * 返回评论页面的固定连接
	 * @param int $number
	 * @return string
	 */
	function get_comment_pager($number){
		return get_url($this->router->getLink('gallery_pager', $this->id, $number));
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
		return hook()->apply('GalleryComment_comment', NULL, $this->id, $user_id, $parent, $parent_top, $content, $this->gallery_info);
	}


} 