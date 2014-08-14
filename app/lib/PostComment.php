<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-4-1
 * Time: 下午3:38
 * Filename: PostComment.php
 */

namespace ULib;


if(!class_exists('CommentData')){
	lib()->load('CommentData');
}

class PostComment extends CommentData{
	/**
	 * @var Router 路由信息类
	 */
	private $router;

	/**
	 * @var array 文章信息
	 */
	private $post_info;

	function __construct($post_id, $page = false, $info = NULL){
		parent::__construct("posts_has_comments", "posts_id", intval($post_id), "posts");
		$this->page = intval($page);
		$this->action_method = "post_post";
		$this->router = lib()->using('router');
		if($info === NULL){
			$this->post_info = $this->db->get("posts", [
				'users_id' => 'user_id',
				'post_status',
				'post_allow_comment'
			], ['id' => $post_id]);
		} else{
			$this->post_info = $info;

		}
	}

	/**
	 * 检查是否允许评论
	 * @param int $id
	 * @return bool
	 */
	function checkAllowComment($id){
		return $this->post_info['post_status'] == 1 && $this->post_info['post_allow_comment'] == 1;
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
		return $this->post_info['user_id'];
	}


	/**
	 * 返回评论页面的固定连接
	 * @param int $number
	 * @return string
	 */
	function get_comment_pager($number){
		$this->router->getLink('post_pager', $this->post_info['post_name'], $number);
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
		return hook()->apply('PostComment_comment', NULL, $this->id, $user_id, $parent, $parent_top, $content);
	}

} 