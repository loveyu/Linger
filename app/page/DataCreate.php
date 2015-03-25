<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-2-17
 * Time: 下午1:53
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace UView;


use Core\Page;
use ULib\Comment;
use ULib\CommentManagement;
use ULib\CountMessage;
use ULib\POTCreator;
use ULib\Tag;
use ULib\User;
use ULib\UserCheck;
use ULib\UserRegister;

class DataCreate extends Page{

	function __construct(){
		parent::__construct();
		if(!_Debug_ || $_SERVER['REMOTE_ADDR']!=='127.0.0.1'){
			$this->__load_404();
			exit;
		}
	}

	public function createUrlList(){
		$db = db();
		header("Content-Type: text/plain; charset=utf-8");
		echo get_url(), "\n";
		echo gallery_list_link(), "\n";
		echo post_list_link(), "\n";
		/**
		 * @var \ULib\Router $router
		 */
		$router = lib()->using('router');
		$list = $db->select("users", ['user_name'], ['LIMIT' => 20]);
		foreach($list as $v){
			echo user_link($v['user_name']), "\n";
			echo user_gallery_list_link($v['user_name']), "\n";
		}
		$list = $db->select("gallery", ['id']);
		foreach($list as $v){
			echo gallery_link($v['id']), "\n";
		}
		$list = $db->select("pictures", ['id']);
		foreach($list as $v){
			echo picture_link($v['id']), "\n";
		}
		$list = $db->select("posts", ['post_name']);
		foreach($list as $v){
			echo post_link($v['post_name']), "\n";
		}
		$page_list = [
			'UserApi',
			'Control',
			'Follow',
			'Home',
			'Message',
			'Photo',
			'Posts',
			'Show',
			'Tool',
			'User'
		];
		foreach($page_list as $f){
			u()->require_page_class($f . '.php');
			$list = get_class_methods("\\UView\\$f");
			foreach($list as $v){
				if(substr($v, 0, 2) == "__"){
					continue;
				}
				echo get_url($f, $v), "\n";
			}
		}
	}

	public function add_view($type = '', $id = 1){
		$this->__lib("CountMessage");
		$cm = new CountMessage();
		var_dump($cm->addCount($type, $id));
	}

	public function test_getCommentType($id = 1){
		$this->__lib("CommentManagement");
		$cm = new CommentManagement();
		var_dump($cm->getCommentType($id));
	}

	public function pot_create(){
		lib()->load('POTCreator');
		$pot = new POTCreator();
		$pot->write_pot(_Language_ . "/zh_CN/LC_MESSAGES/" . _AppName_ . ".pot");
		echo c()->getTimer()->get_second();
	}

	public function class_time($i = 10){
		$t = c()->getTimer();
		$v = [];
		$this->__lib('Comment');
		$user = new User(1);
		echo $b = $t->get_second(), "\n$i...\n";
		for($m = 0; $m < $i; ++$m){
			$v[] = new Comment([], $user);
		}
		echo $e = $t->get_second() . "\n";
		echo ($e - $b) / $i . "\n";
	}

	public function tag_test(){
		header("Content-type:text/html; charset=utf-8");
		lib()->load('Tag');
		$tag = new Tag();
		try{
			$tag->pic_set(156, "巴黎，风景，独好");
		} catch(\Exception $ex){
			echo $ex->getMessage();
		}
	}

	public function create_user($number = 0){
		set_time_limit(0);
		header("Content-Type: text/plain; charset=utf-8");
		$number = 0 + $number;
		lib()->load('UserRegister', 'UserCheck');
		$ur = new UserRegister();
		$t = time();
		$e = 0;
		$s = 0;
		hook()->add('UserRegister_Captcha', function (){
			//通过钩子去掉用户注册验证码
			return true;
		});
		hook()->add('MailTemplate_mailSend', function (){
			//去掉发送邮件发送功能
			return false;
		});
		for($i = 0; $i < $number; $i++){
			$name = $this->rand(15);
			$email = $name . "@pitus.com";
			$password = UserCheck::MakeHashChar("123456");
			if(($code = $ur->Register($email, $password, $name, "244")) > 0){
				$s++;
			} else{
				echo $ur->CodeMsg($code) . "\n";
				$e++;
			}
		}
		echo time() - $t . "s\nS:$s,\nE:$e\nOK\n";
	}

	private function rand($length = 40){
		$str = "abcdefghijklmnopqretuvwxyz0123456789";
		$rt = "a";
		$l = strlen($str);
		for($i = 0; $i < $length - 1; $i++){
			$rt .= $str[rand(0, $l - 1)];
		}
		return $rt;
	}
} 