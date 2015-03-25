<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-25
 * Time: 下午2:02
 * Filename: Follow.php
 */

namespace UView;


use Core\Page;
use ULib\CommentList;
use ULib\FeedManagement;
use ULib\FollowManagement;

class Follow extends Page{
	/**
	 * @var \ULib\Theme
	 */
	private $theme;

	function __construct(){
		parent::__construct();
		if(!is_login()){
			redirect_to_login();
		} else if(!login_user()->is_active()){
			redirect([
				'User',
				'activation'
			]);
		}
		$this->theme = theme();
		$this->theme->setBreadcrumb("我的关注", "Follow");
		l_h("html_tag.php");
	}

	public function main(){
		$this->theme->setTitle("动态提醒");
		$this->theme->setBreadcrumb("动态提醒");
		$this->__view("User/header.php");
		$this->__view("Follow/main.php");
		$this->__view("User/footer.php");
	}

	public function me(){
		$this->__lib("FollowManagement");
		$this->theme->setTitle("我关注的用户");
		$this->theme->setBreadcrumb("我关注的用户");
		$fm = new FollowManagement();
		$fm->setPager(req()->get('page'), 20);
		$this->__view("User/header.php");
		$this->__view("Follow/me.php", [
			'list' => $fm->getMeFollow(login_user()->getId()),
			'count' => $fm->getCount()
		]);
		$this->__view("User/footer.php");
	}

	public function ta(){
		$this->theme->setTitle("我的粉丝");
		$this->theme->setBreadcrumb("我的粉丝");
		$this->__lib("FollowManagement");
		$fm = new FollowManagement();
		$fm->setPager(req()->get('page'), 20);
		$this->__view("User/header.php");
		$this->__view("Follow/ta.php", [
			'list' => $fm->getFollowMe(login_user()->getId()),
			'count' => $fm->getCount()
		]);
		$this->__view("User/footer.php");
	}

	public function gallery(){
		$this->__lib("FollowManagement");
		$this->theme->setTitle("我关注的图集");
		$this->theme->setBreadcrumb("我关注的图集");
		$fm = new FollowManagement();
		$fm->setPager(req()->get('page'), 20);
		$this->__view("User/header.php");
		$this->__view("Follow/gallery.php", [
			'list' => $fm->getFollowGallery(login_user()->getId()),
			'count' => $fm->getCount(),
		]);
		$this->__view("User/footer.php");
	}

	public function mutual(){
		$this->theme->setTitle("互相关注");
		$this->theme->setBreadcrumb("互相关注");
		$this->__lib("FollowManagement");
		$fm = new FollowManagement();
		$fm->setPager(req()->get('page'), 20);
		$this->__view("User/header.php");
		$this->__view("Follow/mutual.php", [
			'list' => $fm->getMutualFollow(login_user()->getId()),
			'count' => $fm->getCount()
		]);
		$this->__view("User/footer.php");
	}

	public function feed(){
		$this->theme->setTitle("我的动态");
		$this->theme->setBreadcrumb("我的动态");
		$this->__lib("FeedManagement");
		$fm = new FeedManagement();
		$fm->setPager(req()->get('page'), 10);
		$list = $fm->getList(login_user()->getId());
		$count = $fm->getCount();
		$this->__view("User/header.php");
		$this->__view("Follow/feed.php", [
			'list' => $list,
			'count' => $count
		]);
		$this->__view("User/footer.php");
	}

	public function comment($type = ''){
		$error = NULL;
		$cl = NULL;
		$type = trim($type);
		try{
			$this->__lib('CommentList');
			$cl = new CommentList($type);
			$cl->setPager(intval(req()->get('page')), 20);
		} catch(\Exception $ex){
			$error = $ex->getMessage();
		}
		switch($type){
			case 'gallery':
				$this->comment_gallery($cl);
				return;
			case 'pictures':
				$this->comment_pictures($cl);
				return;
			case 'posts':
				$this->comment_posts($cl);
				return;
		}
		$this->theme->setTitle("我的评论");
		$this->theme->setBreadcrumb("我的评论");
		$this->__view("User/header.php");
		if(!empty($type) && $error !== NULL){
			$this->__view("Follow/error.php", ['error' => $error]);
		} else{
			$this->__view("Follow/comment_chose.php");
		}
		$this->__view("User/footer.php");
	}

	private function comment_gallery(CommentList $cl){
		$this->theme->setTitle("图集评论");
		$this->theme->setBreadcrumb("图集评论");
		$list = $cl->getListOfUser(login_user()->getId());
		$this->__view("User/header.php");
		$this->__view("Follow/comment_show.php", [
			'list' => $list,
			'count' => $cl->getCount(),
			'type' => 'gallery'
		]);
		$this->__view("User/footer.php");
	}

	private function comment_pictures(CommentList $cl){
		$this->theme->setTitle("图片评论");
		$this->theme->setBreadcrumb("图片评论");
		$this->__view("User/header.php");
		$list = $cl->getListOfUser(login_user()->getId());
		$this->__view("Follow/comment_show.php", [
			'list' => $list,
			'count' => $cl->getCount(),
			'type' => 'pictures'
		]);
		$this->__view("User/footer.php");
	}

	private function comment_posts(CommentList $cl){
		$this->theme->setTitle("文章评论");
		$this->theme->setBreadcrumb("文章评论");
		$this->__view("User/header.php");
		$list = $cl->getListOfUser(login_user()->getId());
		$this->__view("Follow/comment_show.php", [
			'list' => $list,
			'count' => $cl->getCount(),
			'type' => 'posts'
		]);
		$this->__view("User/footer.php");
	}
}