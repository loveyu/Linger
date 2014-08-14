<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-2-20
 * Time: 下午9:37
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace ULib;


/**
 * 主题控制类
 * Class Theme
 * @package ULib
 */
class Theme{
	/**
	 * 用户中心导航信息
	 * @var array
	 */
	private $breadcrumb = [];

	/**
	 * @var string 主标题
	 */
	private $title;

	/**
	 * 面包屑标题
	 * @var string
	 */
	private $breadcrumb_title = '';

	/**
	 * @var array 头部信息列表
	 */
	private $header_list = [];
	/**
	 * @var array 底部信息列表
	 */
	private $footer_list = [];

	function __construct(){
		hook()->add('header_hook', array(
			$this,
			'header_hook'
		));
		hook()->add('footer_hook', array(
			$this,
			'footer_hook'
		));
	}

	/**
	 * 生成一个js引入连接
	 * @param array $list 传入名称列表
	 * @return string
	 */
	function js($list){
		if(!isset($list['type'])){
			$list['type'] = 'text/javascript';
		}
		$d = "";
		foreach($list as $n => $v){
			$d .= " " . $n . '="' . $v . '"';
		}
		return "<script$d></script>";
	}

	/**
	 * 生成css引入连接
	 * @param array|string $list 传入名称列表
	 * @return string
	 */
	function css($list){
		if(!is_array($list)){
			$r = $list;
			$list = [];
			$list['href'] = $r;
		}
		if(!isset($list['rel'])){
			$list['rel'] = 'stylesheet';
		}
		if(!isset($list['type'])){
			$list['type'] = 'text/css';
		}
		return $this->link($list);
	}

	/**
	 * 生成引入连接
	 * @param array $list
	 * @return string
	 */
	function link($list){
		$d = "";
		foreach($list as $n => $v){
			$d .= " " . $n . '="' . $v . '"';
		}
		return "<link$d />";
	}

	/**
	 * 生成标签
	 * @param array $list
	 * @return string
	 */
	function meta($list){
		$d = "";
		foreach($list as $n => $v){
			$d .= " " . $n . '="' . $v . '"';
		}
		return "<meta$d />";
	}

	/**
	 * 添加自定义内容到头部
	 * @param string $content
	 * @param int    $pr 排名，先后顺序
	 */
	public function header_add($content, $pr = 50){
		if(!isset($this->header_list[$pr])){
			$this->header_list[$pr] = array();
		}
		$this->header_list[$pr][] = $content;
	}

	/**
	 * 添加自定义内容到底部
	 * @param string $content
	 * @param int    $pr 排名，先后顺序
	 */
	public function footer_add($content, $pr = 50){
		if(!isset($this->footer_list[$pr])){
			$this->footer_list[$pr] = array();
		}
		$this->footer_list[$pr][] = $content;
	}

	/**
	 * 输出头部内容
	 * @param bool  $echo 是否直接输出
	 * @param array $con  连接字符，[0]之前，[1]之后
	 * @return string 返回数据
	 */
	public function header_out($echo = true, $con = array(
		"",
		"\n"
	)){
		$data = '';
		$keys = array_keys($this->header_list);
		sort($keys);
		foreach($keys as $key){
			foreach($this->header_list[$key] as $v){
				$data .= $con[0] . $v . $con[1];
			}
		}
		if($echo){
			echo $data;
		}
		return $data;
	}

	/**
	 * 输出底部内容
	 * @param bool  $echo 是否直接输出
	 * @param array $con  连接字符，[0]之前，[1]之后
	 * @return string 返回数据
	 */
	public function footer_out($echo = true, $con = array(
		"",
		"\n"
	)){
		$data = '';
		$keys = array_keys($this->footer_list);
		sort($keys);
		foreach($keys as $key){
			foreach($this->footer_list[$key] as $v){
				$data .= $con[0] . $v . $con[1];
			}
		}
		if($echo){
			echo $data;
		}
		return $data;
	}

	/**
	 * 设置页面描述
	 * @param $desc
	 */
	public function set_desc($desc){
		$desc = trim($desc);
		if(empty($desc)){
			return;
		}
		$this->header_add($this->meta(array(
			'name' => 'Description',
			'content' => $desc
		)));
	}

	/**
	 * 设置页面关键字
	 * @param string|array $keywords
	 */
	public function set_keywords($keywords){
		$keywords = trim($keywords);
		if(is_array($keywords)){
			$keywords = implode(", ", $keywords);
		}
		if(empty($keywords)){
			return;
		}
		$this->header_add($this->meta(array(
			'name' => 'Keywords',
			'content' => $keywords
		)));
	}

	/**
	 * 头部钩子
	 */
	public function header_hook(){
		$this->header_out();
	}

	/**
	 * 底部钩子
	 */
	public function footer_hook(){
		$this->footer_out();
	}

	/**
	 * 设置主标题
	 * @param string $title
	 */
	public function setTitle($title){
		$this->title = $title;
	}

	/**
	 * 获取标题
	 * @return string
	 */
	public function getTitle(){
		if(empty($this->title) && !empty($this->breadcrumb_title)){
			return $this->breadcrumb_title . " - " . site_title();
		} else if(!empty($this->title) && empty($this->breadcrumb_title)){
			return $this->title . " - " . site_title();
		} else if(empty($this->title) && empty($this->breadcrumb_title)){
			return site_title() . " - " . site_desc();
		}
		return $this->breadcrumb_title . " - " . $this->title . " | " . site_title();
	}


	/**
	 * 设置当前页面的面包屑
	 * @param string      $name
	 * @param null|string $url_param 留空表示不生成连接
	 */
	public function setBreadcrumb($name, $url_param = NULL){
		if(empty($this->breadcrumb_title)){
			$this->breadcrumb_title = $name;
		}
		array_push($this->breadcrumb, [
			"name" => $name,
			"url" => $url_param
		]);
	}

	/**
	 * 获取面包屑内容
	 * @param string $before 前置输出
	 * @return string
	 */
	public function getBreadcrumb($before = ''){
		$rt = "";
		for($i = 0, $l = count($this->breadcrumb); $i < $l; $i++){
			if($this->breadcrumb[$i]['url'] === NULL){
				$rt .= "$before<li" . (($i + 1) == $l ? " class='active'" : "") . ">" . $this->breadcrumb[$i]['name'] . "</li>\n";
			} else{
				$rt .= "$before<li" . (($i + 1) == $l ? " class='active'" : "") . "><a href=\"" . get_url($this->breadcrumb[$i]['url']) . "\">" . $this->breadcrumb[$i]['name'] . "</a></li>\n";
			}
		}
		return $rt;
	}

	/**
	 * 获取用户中心菜单
	 * @return array
	 */
	private function get_user_menu_list(){
		$menu = [
			[
				'url' => ['Photo'],
				'name' => '图片中心',
				'sub' => [
					[
						'url' => [
							'Photo',
							'add_pic'
						],
						'name' => '添加图片'
					],
					[
						'url' => [
							'Photo',
							'edit_pic'
						],
						'name' => '编辑图片',
						'hide' => true
					],
					[
						'url' => [
							'Photo',
							'add_gallery'
						],
						'name' => '添加图集'
					],
					[
						'url' => [
							'Photo',
							'edit_gallery'
						],
						'name' => '编辑图集',
						'hide' => true
					],
					[
						'url' => [
							'Photo',
							'list_gallery'
						],
						'name' => '图集管理'
					],
					[
						'url' => [
							'Photo',
							'gallery_comment'
						],
						'name' => '图集评论'
					],
					[
						'url' => [
							'Photo',
							'list_pic'
						],
						'name' => '图片管理'
					],
					[
						'url' => [
							'Photo',
							'picture_comment'
						],
						'name' => '图片评论'
					],
				]
			],
			[
				'url' => ['Follow'],
				'name' => '我的关注',
				'sub' => [
					[
						'url' => [
							'Follow',
							'me'
						],
						'name' => '关注的用户',
					],
					[
						'url' => [
							'Follow',
							'gallery'
						],
						'name' => '关注的图集',
					],
					[
						'url' => [
							'Follow',
							'ta'
						],
						'name' => '粉丝',
					],
					[
						'url' => [
							'Follow',
							'mutual'
						],
						'name' => '互相关注',
					],
					[
						'url' => [
							'Follow',
							'feed'
						],
						'name' => '我的动态',
					],
					[
						'url' => [
							'Follow',
							'comment'
						],
						'name' => '我的评论',
					],
				]
			],
			[
				'url' => ['User'],
				'name' => '用户中心',
				'sub' => [
					[
						'name' => '编辑信息',
						'url' => [
							'User',
							'edit_info'
						]
					],
					[
						'name' => '切换头像',
						'url' => [
							'User',
							'edit_avatar'
						]
					],
					[
						'name' => '密码与安全',
						'url' => [
							'User',
							'password'
						]
					],
					[
						'name' => '更换邮箱',
						'url' => [
							'User',
							'email'
						],
						'hide' => !edit_email_action()
					],
					[
						'name' => '激活用户',
						'url' => [
							'User',
							'activation'
						],
						'hide' => login_user()->is_active()
					],
				]
			],
			[
				'name' => '消息中心',
				'url' => ['Message'],
				'sub' => [
					[
						'name' => '收信箱',
						'url' => [
							'Message',
							'inbox'
						]
					],
					[
						'name' => '发信箱',
						'url' => [
							'Message',
							'outbox'
						]
					],
					[
						'name' => '通知设置',
						'url' => [
							'Message',
							'option'
						]
					]
				]
			]
		];
		if(login_user()->Permission('Posts')){
			$menu[] = [
				'name' => '文章发布',
				'url' => ['Posts'],
				'sub' => [
					[
						'name' => '管理',
						'url' => [
							'Posts',
							'management'
						],
					],
					[
						'name' => '评论',
						'url' => [
							'Posts',
							'comment'
						],
					],
					[
						'name' => '编辑',
						'url' => [
							'Posts',
							'edit'
						],
						'hide' => true
					],
				]
			];
		}
		return hook()->apply('Theme_get_menu', $menu);
	}

	/**
	 * 创建一个分页菜单
	 * @param int    $now         当前页面序号
	 * @param int    $max         最大序号
	 * @param int    $count       统计数量
	 * @param string $url_replace URL连接替换字符{number}
	 * @return string
	 */
	public function createNav($now, $max, $count, $url_replace){
		$now = 0 + $now;
		$max = 0 + $max;
		$rt = "";
		if($now <= 1){
			$rt .= "<li class='disabled'><a href=\"#\">&laquo;</a></li>";
		} else{
			$rt .= "<li><a title=\"" . _("Previous") . "\" href=\"" . str_replace("{number}", $now - 1, $url_replace) . "\">&laquo;</a></li>";
		}

		$bb = $now - 4;
		if($bb < 2){
			$bb = 1;
		}
		$nn = $now + 4;
		if($nn > $max){
			$nn = $max;
		}
		if($now > 1 && $bb > 1){
			$rt .= "<li><a title='" . _('The first page') . "' href=\"" . str_replace("{number}", 1, $url_replace) . "\">" . _("Index") . "</a></li>";
		}
		for(; $bb <= $nn; $bb++){
			$class = "";
			if($bb == $now){
				$class = " class=\"active\"";
			}
			$rt .= "<li$class><a title='" . _('The ') . $bb . _(' page') . "' href=\"" . str_replace("{number}", $bb, $url_replace) . "\">" . $bb . "</a></li>";
		}
		if($bb < $max){
			$rt .= "<li><a title='" . _('The ') . $max . _(' page') . "' href=\"" . str_replace("{number}", $max, $url_replace) . "\">" . _("End") . "</a></li>";
		}
		if($now >= $max){
			$rt .= "<li class='disabled'><a href=\"#\">&raquo;</a></li>";
		} else{
			$rt .= "<li><a title=\"" . _("Next") . "\" href=\"" . str_replace("{number}", $now + 1, $url_replace) . "\">&raquo;</a></li>";
		}
		return $rt;
	}

	/**
	 * @param string $class
	 * @return string
	 */
	public function get_user_menu($class = "active"){
		$list = $this->get_user_menu_list();
		$rt = "";
		$ui = u()->getUriInfo()->getUrlList();
		foreach($list as $v){
			$flag = [
				$v['url'][0] == $ui[0],
				false
			];
			if(!isset($v['hide']) || !$v['hide'] || ($v['hide'] && $flag[0])){
				$rt .= "<li" . ($flag[0] ? " class=\"$class\"" : '') . "><div class='title'><a href='" . get_url($v['url']) . "'>" . $v['name'] . "</a><span class=\"glyphicon glyphicon-chevron-down\"></span></div>";
				if(isset($v['sub']) && count($v['sub']) > 0){
					$rt .= "<ul class='sub'>";
					foreach($v['sub'] as $v2){
						$flag[1] = $v2['url'][0] == $ui[0] && isset($ui[1]) && $v2['url'][1] == $ui[1];
						if(!isset($v2['hide']) || !$v2['hide'] || ($v2['hide'] && $flag[1])){
							$rt .= "<li" . ($flag[1] ? " class=\"$class\"" : '') . "><a href='" . get_url($v2['url']) . "'>" . $v2['name'] . "</a>";
						}
					}
					$rt .= "</ul>";
				}
				$rt .= "</li>";
			}
		}
		return $rt;
	}
}