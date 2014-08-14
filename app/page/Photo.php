<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-2-21
 * Time: 下午11:20
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace UView;


use Core\Page;
use ULib\CommentList;
use ULib\Gallery;
use ULib\Picture;

class Photo extends Page{
	/**
	 * @var \ULib\Theme
	 */
	private $theme;

	function __construct(){
		if(!is_login()){
			redirect_to_login();
		} else if(!login_user()->is_active()){
			redirect([
				'User',
				'activation'
			]);
		}
		$this->theme = theme();
		$this->theme->setBreadcrumb("图片中心", "Photo");
		l_h("html_tag.php");
	}

	public function main(){
		//lib()->load('Picture');
		//$pic = new Picture();
		$this->__view("User/header.php");
		$this->__view("Photo/add_pic2.php");
		$this->__view("Photo/add_gallery.php");
		$this->__view("User/footer.php");
	}

	public function add_pic(){
		$this->theme->setBreadcrumb("添加图片");
		$this->theme->setTitle("添加图片");
		$this->__view("User/header.php");
		//$this->__view("Photo/add_pic.php");
		//使用新的图片上传页面
		$this->__view("Photo/add_pic2.php");
		$this->__view("User/footer.php");
	}

	public function edit_pic(){
		lib()->load('Picture');
		$pic = new Picture();
		$list = $pic->get(req()->get('id'), login_user()->getId());
		$this->theme->setBreadcrumb("编辑图片");
		$this->theme->setTitle("编辑图片");
		$this->__view("User/header.php");
		$this->__view("Photo/edit_pic.php", ['list' => (isset($list[0]) ? $list : [$list])]);
		$this->__view("User/footer.php");
	}

	public function add_gallery(){
		$this->theme->setBreadcrumb("添加图集");
		$this->theme->setTitle("添加新的图集");
		$this->__view("User/header.php");
		$this->__view("Photo/add_gallery.php");
		$this->__view("User/footer.php");
	}

	public function edit_gallery(){
		lib()->load('Gallery');
		$gallery = new Gallery(req()->_plain()->get('id'), login_user()->getId());
		$this->theme->setBreadcrumb("编辑图集");
		$this->theme->setTitle("编辑图集信息");
		$this->theme->header_add($this->theme->css(get_bootstrap_plugin_url("markdown/markdown.min.css")));
		$this->theme->header_add($this->theme->js([
			'src' => get_bootstrap_plugin_url("markdown/markdown.js")
		]));
		$this->__view("User/header.php");
		$this->__view("Photo/edit_gallery.php", [
			'info' => $gallery->getInfo(),
			'gallery' => $gallery
		]);
		$this->__view("User/footer.php");
	}

	public function list_pic(){
		$this->theme->setBreadcrumb("图片列表");
		$this->theme->setTitle("管理你的图片");
		lib()->load('Picture');
		$pic = new Picture();
		$req = req()->_plain();
		$info = ['error' => true];
		try{
			$info = $pic->select(login_user()->getId(), $req->get('page'), $req->get('number'));
		} catch(\Exception $ex){
			$info['error'] = $ex->getMessage();
		}
		$this->__view("User/header.php");
		$this->__view("Photo/list_pic.php", $info);
		$this->__view("User/footer.php");
	}

	public function list_gallery(){
		lib()->load('Gallery');
		$g = new Gallery(0, login_user()->getId());
		$this->theme->setBreadcrumb("图集列表");
		$this->theme->setTitle("管理你的图册");
		$this->__view("User/header.php");
		$req = req()->_plain();
		$this->__view("Photo/list_gallery.php", ['list' => $g->getList($req->get('page'), $req->get('number'))]);
		$this->__view("User/footer.php");
	}

	public function select_user_pic(){
		header("Content-Type: text/html; charset=utf-8");
		//$this->__view("User/header.php");
		$this->__view("Photo/select_user_pic.php");
		//$this->__view("User/footer.php");
	}

	public function gallery_comment(){
		$this->theme->setBreadcrumb("图集评论");
		$this->theme->setTitle("图集评论");
		$this->__view("User/header.php");
		$this->__lib("CommentList");
		$cl = new CommentList("gallery");
		$list = $cl->getListOfUserOnObject(login_user()->getId());
		$this->__view("Photo/comment_show.php", [
			'list' => &$list,
			'count' => $cl->getCount()
		]);
		$this->__view("User/footer.php");
	}

	public function picture_comment(){
		$this->theme->setBreadcrumb("图片评论");
		$this->theme->setTitle("图片评论");
		$this->__view("User/header.php");
		$this->__lib("CommentList");
		$cl = new CommentList("pictures");
		$list = $cl->getListOfUserOnObject(login_user()->getId());
		$this->__view("Posts/comment_show.php", [
			'list' => &$list,
			'count' => $cl->getCount()
		]);
		$this->__view("User/footer.php");
	}
}