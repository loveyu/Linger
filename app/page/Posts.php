<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-4-1
 * Time: 上午9:07
 * Filename: Posts.php
 */

namespace UView;


use Core\Page;
use ULib\CommentList;
use ULib\Post;
use ULib\User;

class Posts extends Page{
	/**
	 * @var \ULib\Theme
	 */
	private $theme;

	public function __construct(){
		parent::__construct();
		if(!is_login()){
			redirect(array(
				"Home",
				"login"
			));
		} else if(!login_user()->Permission("Posts")){
			redirect(array(
				'Home',
				'permission'
			));
		}
		$this->theme = theme();
		$this->theme->setBreadcrumb("文章中心", "Message");
		header("Content-Type: text/html; charset: utf-8");
	}

	public function main(){
		$this->theme->setBreadcrumb("文章发布");
		$this->theme->setTitle("文章发布");
		$this->__view("User/header.php");
		$this->__view("Posts/post.php");
		$this->__view("User/footer.php");
	}

	public function management(){
		$this->theme->setBreadcrumb("文章管理");
		$this->theme->setTitle("文章管理");
		$this->__lib('Post');
		$post = new Post();
		$post->setPager(req()->get('page'));
		$this->__view("User/header.php");
		$this->__view("Posts/management.php", [
			'data' => $post->getList(login_user()->getId()),
			'count' => $post->getCount()
		]);
		$this->__view("User/footer.php");
	}

	public function comment(){
		$this->theme->setBreadcrumb("文章评论");
		$this->theme->setTitle("文章评论");
		$this->__view("User/header.php");
		$this->__lib("CommentList");
		$cl = new CommentList("posts");
		$list = $cl->getListOfUserOnObject(login_user()->getId());
		$this->__view("Posts/comment_show.php", [
			'list' => &$list,
			'count' => $cl->getCount()
		]);
		$this->__view("User/footer.php");
	}

	public function edit(){
		l_h('html_tag.php');
		$this->theme->setBreadcrumb("编辑文章");
		$this->theme->setTitle("编辑文章");
		$id = intval(req()->get('id'));
		$this->__lib('Post');
		$post = new Post($id);
		$info = $post->getInfo(login_user()->getId());
		$this->theme->header_add($this->theme->css(get_bootstrap_plugin_url("markdown/markdown.min.css")));
		$this->theme->header_add($this->theme->js([
			'src' => get_bootstrap_plugin_url("markdown/markdown.js")
		]));
		if(!isset($info['post_id']) || $info['post_id'] != $id){
			$this->__view("User/header.php");
			$this->__view("Posts/not_found.php");
		} else{
			$this->__view("User/header.php");
			$this->__view("Posts/edit.php", [
				'info' => $info,
				'post' => $post,
			]);
		}
		$this->__view("User/footer.php");
	}
}