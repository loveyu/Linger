<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-15
 * Time: 下午6:17
 * Filename: Router.php
 */

namespace ULib;


class Router{
	/**
	 * @var \CLib\Router
	 */
	private $c_router;
	private $router_list;

	function __construct(){
		$this->c_router = c_lib()->load('router')->add('router', new \CLib\Router());
		//对路由信息反序列化
		$router = @unserialize(cfg()->get('option', 'router_list'));
		if(is_array($router)){
			foreach(array_keys($router) as $c){
				if(empty($router[$c])){
					unset($router[$c]);
				}
			}
		}
		$this->router_list = $this->defaultRouter();
		if(is_array($router)){
			$this->router_list = array_merge($this->router_list, $router);
		}
	}

	private function defaultRouter(){
		return [
			'picture' => 'picture-%number%',
			'picture_pager' => 'picture-%number%/comment-page-%number%',
			'gallery' => 'gallery-%number%',
			'gallery_pager' => 'gallery-%number%/comment-page-%number%',
			'user' => 'user/%user_name%',
			'post' => 'post/%post_name%',
			'post_pager' => 'post/%post_name%/comment-page-%number%',
			'post_list' => 'post_list/',
			'post_list_pager' => 'post_list/page-%number%',
			'gallery_list' => 'gallery_list/',
			'gallery_list_pager' => 'gallery_list/page-%number%',
			'user_gallery_list' => 'user/%user_name%/gallery/',
			'user_gallery_list_pager' => 'user/%user_name%/gallery/page-%number%',
			'time_line' => 'TimeLine',
			'tag_all' => 'tag/',
			'tag_list' => 'tag/%word%/',
			'tag_list_pager' => 'tag/%word%/page-%number%',
			'tag_type_list' => 'tag/%word%/%tag_type%/',
			'tag_type_list_pager' => 'tag/%word%/%tag_type%/page-%number%',
		];
	}

	public function createRouter(){
		$this->c_router->add_preg(hook()->apply("Router_createRouter",$this->createPregList()));
		//$this->c_router->add_preg('/^picture-([1-9]{1}[0-9]*)\.html$/', 'Show/picture/[1]');
		//$this->c_router->add_preg('/^picture-([1-9]{1}[0-9]*)-p([1-9]{1}[0-9]*)\.html$/', 'Show/picture/[1]/[2]');
	}

	public function get($name){
		if(isset($this->router_list[$name])){
			return $this->router_list[$name];
		} else{
			return '';
		}
	}

	public function getLink($name){
		if(isset($this->router_list[$name])){
			$param = func_get_args();
			array_shift($param);
			$ui = $this->router_list[$name];
			if(@preg_match_all('/(%[\s\S]+?%)/', $ui, $matches) > 0 && isset($matches[1]) && is_array($matches[1])){
				for($i = 0, $l = count($param), $l2 = count($matches[1]); $i < $l && $i < $l2; ++$i){
					$search = @preg_quote($matches[1][$i]);
					$ui = @preg_replace("/$search/", $param[$i], $ui, 1);
				}
			}
			return $ui;
		} else{
			return '';
		}
	}

	private function  createPregList(){
		$rt = [];
		$search = [
			'.',
			'/',
			'%number%',
			'%user_name%',
			'%post_name%',
			'%word%',//单词或中文之类的
			'%tag_type%',//标签类型，{picture|gallery}
		];
		$replace = [
			'\.',
			'\\/',
			'([1-9]{1}[0-9]*)',
			'([_a-z]{1}[a-z0-9_.]{5,19})',
			'([a-zA-Z0-9]+[a-zA-Z0-9_-]*)',
			'([\x{4e00}-\x{9fa5}A-Za-z0-9_]+)',
			'(picture|gallery)',
		];
		$control_list = [
			'picture' => 'Show/picture/[1]',
			'picture_pager' => 'Show/picture/[1]/[2]',
			'gallery' => 'Show/gallery/[1]',
			'gallery_pager' => 'Show/gallery/[1]/[2]',
			'user' => 'Show/user/[1]',
			'post' => 'Show/post/[1]',
			'post_pager' => 'Show/post/[1]/[2]',
			'post_list' => 'Show/post_list',
			'post_list_pager' => 'Show/post_list/[1]',
			'gallery_list' => 'Show/gallery_list',
			'gallery_list_pager' => 'Show/gallery_list/[1]',
			'user_gallery_list' => 'Show/user_gallery_list/[1]',
			'user_gallery_list_pager' => 'Show/user_gallery_list/[1]/[2]',
			'time_line' => 'Show/time_line',
			'tag_all' => 'Show/tag',
			'tag_list' => 'Show/tag_list/[1]',
			'tag_list_pager' => 'Show/tag_list/[1]/[2]',
			'tag_type_list' => 'Show/tag_[2]_list/[1]',
			'tag_type_list_pager' => 'Show/tag_[2]_list/[1]/[3]',
		];
		foreach($this->router_list as $name => $v){
			$p = "/^" . str_replace($search, $replace, $v) . "$/u";
			if(isset($control_list[$name]) && !isset($rt[$p])){
				$rt[$p] = $control_list[$name];
			}
		}
		return $rt;
	}

	public function process($u){
		return $this->c_router->result($u);
	}
} 