<?php
namespace UView;

use Core\Page;
use ULib\CountMessage;
use ULib\Gallery;
use ULib\GalleryComment;
use ULib\ListGallery;
use ULib\PictureComment;
use ULib\Picture;
use ULib\Post;
use ULib\PostComment;
use ULib\User;
use ULib\UserManagement;

class Show extends Page{
	private $theme;

	function __construct(){
		parent::__construct();
		if(strpos(u()->getUriInfo()->getPath(), "/Show") === 0){
			$this->__load_404();
			exit;
		} else{
			$this->theme = theme();
		}
	}

	public function home(){
		$this->__lib('Picture', 'Gallery', 'UserManagement');
		$pic = new Picture();
		$g = new Gallery();
		$um = new UserManagement();
		$this->__view("Home/header.php");
		$p_list = $pic->select_new_pic(18);
		$g_list = $g->select_new_gallery(8, true);
		$u_list = $um->get_new_users(5);
		$this->__view("Show/home.php", [
			'pic_list' => $p_list,
			'gallery_list' => $g_list,
			'user_list' => $u_list
		]);
		$this->__view("Home/footer.php");
	}


	/**
	 * @param int $id  图片ID
	 * @param int $c_p 页数，默认使用0，根据其进行排序
	 */
	public function picture($id = 0, $c_p = 0){
		$id = intval($id);
		$c_p = intval($c_p);
		$n = func_num_args();
		if($n < 1 || $n > 2 || $c_p < 0 || $id < 1){
			$this->__load_404();
			return;
		}
		$this->__lib('Picture', 'PictureComment');
		$pic = new Picture();
		$info = $pic->get_pic($id);
		if(!is_array($info)){
			$this->__load_404();
		} else{
			$this->theme->setTitle("第 {$info['pic_id']} 号图片");
			$this->__view("Home/header.php");
			$this->__view("Show/picture.php", [
				'info' => $info,
				'CommentData' => new PictureComment($info['pic_id'], $c_p, $info)
			]);
			$this->__view("Home/footer.php");
		}
	}

	/**
	 * @param int $id  图集ID
	 * @param int $c_p 页数，默认使用0，根据其进行排序
	 */
	public function gallery($id = 0, $c_p = 0){
		$id = intval($id);
		$c_p = intval($c_p);
		$n = func_num_args();
		if($n < 1 || $n > 2 || $c_p < 0 || $id < 1){
			$this->__load_404();
			return;
		}
		$this->__lib("Gallery", 'GalleryComment');
		$g = new Gallery($id);
		$info = $g->getInfo(true);
		if(!is_array($info) || !isset($info['gallery_status']) || ($info['gallery_status'] != 1 && !(is_login() && $info['user_id'] == login_user()->getId() && strtolower(req()->get('preview')) == 'true'))){
			$this->__load_404();
		} else{
			$this->theme->setTitle($info['gallery_title'] . " [图集]");
			$this->__view("Home/header.php");
			$this->__view("Show/gallery.php", [
				'gallery' => $g,
				'info' => $info,
				'CommentData' => new GalleryComment($id, $c_p, $info)
			]);
			$this->__view("Home/footer.php");
		}
	}

	public function user($user_name = ''){
		$user = User::getUser($user_name);
		if($user === NULL){
			$this->__load_404();
		} else{
			$this->__lib("CountMessage");
			$count = new CountMessage();
			$this->theme->setTitle($user->getAliases() . "(" . $user->getName() . ") 的主页");
			$this->__view("Home/header.php");
			$this->__view("Show/user.php", [
				'user' => $user,
				'count' => $count->getUserCount($user)
			]);
			$this->__view("Home/footer.php");
		}
	}

	public function post($name = NULL, $c_p = 0){
		$this->__lib('Post', 'PostComment');
		$post = new Post(NULL, $name);
		$info = $post->getInfo();
		if(!isset($info['post_id']) || ($info['post_status'] != 1 && !(is_login() && $info['post_users_id'] == login_user()->getId() && strtolower(req()->get('preview')) == 'true'))){
			$this->__load_404();
		} else{
			$this->theme->setTitle($info['post_title'] . " - 文章");
			$this->__view("Home/header.php");
			$this->__view("Show/post.php", [
				'info' => $info,
				'user' => $post->getPostUser(),
				'CommentData' => new PostComment($info['post_id'], $c_p, $info)
			]);
			$this->__view("Home/footer.php");
		}
	}

	public function post_list($page = 0){
		$this->__lib('Post');
		$post = new Post();
		$post->setPager($page, 2);
		$list = $post->getPublicList();
		$count = $post->getCount();
		if(empty($list) || $count['page'] > $count['max']){
			$this->__load_404();
			return;
		}
		$this->theme->setTitle("文章列表");
		$this->__view("Home/header.php");
		$this->__view("Show/post_list.php", [
			'list' => $list,
			'count' => $count
		]);
		$this->__view("Home/footer.php");
	}

	public function tag_list($tag_name = '', $page = 0){
		var_dump(func_get_args());
	}

	public function tag(){
		var_dump(time());
	}

	public function time_line(){
		if(!is_login()){
			redirect_to_login();
		}
		$this->theme->setTitle("时间线");
		$this->theme->footer_add($this->theme->js(['src' => get_style("time_line.js")]));
		$this->theme->footer_add($this->theme->js(['src' => get_js_url("jquery.form.js")]));
		$this->__view("Home/header.php");
		$this->__view("Show/time_line.php");
		$this->__view("Home/footer.php");
	}

	public function user_gallery_list($user = NULL, $page = 0){
		$page = (int)$page;
		$this->__lib('ListGallery');
		$lg = new ListGallery();
		$lg->setPager($page, 6);
		$pager = [
			'previous' => NULL,
			'next' => NULL
		];
		$list = $lg->getListOfUser($user);
		if(!isset($list[0]) || !reset($list[0])){
			$this->__load_404();
		} else{
			$count = $lg->getCount();
			/**
			 * @var \ULib\Router $router
			 */
			$router = lib()->using('router');
			if($count['page'] > 1){
				if($count['page'] == 2){
					$pager['previous'] = get_url($router->getLink("user_gallery_list", $user));
				} else{
					$pager['previous'] = get_url($router->getLink("user_gallery_list_pager", $user, $count['page'] - 1));
				}
			}
			if($count['page'] < $count['max']){
				$pager['next'] = get_url($router->getLink("user_gallery_list_pager", $user, $count['page'] + 1));
			}
			$user = User::getUser($user);
			if($page > 0){
				$this->theme->setTitle($user->getName() . " 的图集列表 第{$page}页");
			} else{
				$this->theme->setTitle($user->getName() . " 的图集列表");
			}
			$this->__view("Home/header.php");
			$this->__view("Show/user_header.php", ['user' => $user]);
			$this->__view("Show/gallery_list.php", [
				'list' => $list,
				'pager' => $pager,
				'type' => 'user',
				'number' => $count['page'],
			]);
			$this->__view("Home/footer.php");
		}
	}

	public function gallery_list($page = 0){
		$page = (int)$page;
		$this->__lib('ListGallery');
		$lg = new ListGallery();
		$lg->setPager($page, 6);
		$pager = [
			'previous' => NULL,
			'next' => NULL
		];
		$list = $lg->getList();

		if(!isset($list[0]) || !reset($list[0])){
			$this->__load_404();
		} else{
			$count = $lg->getCount();
			/**
			 * @var \ULib\Router $router
			 */
			$router = lib()->using('router');
			if($count['page'] > 1){
				if($count['page'] == 2){
					$pager['previous'] = get_url($router->getLink("gallery_list"));
				} else{
					$pager['previous'] = get_url($router->getLink("gallery_list_pager", $count['page'] - 1));
				}
			}
			if($count['page'] < $count['max']){
				$pager['next'] = get_url($router->getLink("gallery_list_pager", $count['page'] + 1));
			}
			if($page > 0){
				$this->theme->setTitle("图集列表 第{$page}页");
			} else{
				$this->theme->setTitle("图集列表");
			}
			$this->__view("Home/header.php");
			$this->__view("Show/gallery_list.php", [
				'list' => $list,
				'pager' => $pager,
				'number' => $count['page'],
				'type' => 'all'
			]);
			$this->__view("Home/footer.php");
		}
	}
}