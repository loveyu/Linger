<?php
/**
 * User: loveyu
 * Date: 2015/7/23
 * Time: 16:54
 */

namespace UView;

use Core\Page;

/**
 * 控制API
 * Class ControlApi
 * @package UView
 */
class ControlApi extends Page{
	/**
	 * @var array 最终用户返回消息
	 */
	private $rt_msg = [
		'status' => false,
		'code' => NULL,
		'msg' => '',
		'content' => NULL
	];

	/**
	 * 发送状态头
	 */
	public function __construct(){
		parent::__construct();
		header('Content-type: application/json; Charset=utf-8');
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		if(!is_login()){
			$this->rt_msg['msg'] = '用户未登陆';
			exit;
		} else if(!login_user()->Permission("Control")){
			$this->rt_msg['msg'] = '权限不足';
			exit;
		}
	}

	/**
	 * 析构方法，输出JSON数据
	 */
	function __destruct(){
		echo json_encode($this->rt_msg);
	}

	/**
	 * 菜单
	 */
	public function menu_list(){
		$menu = cfg()->load(_RootPath_ . "/config/control_menu.php");
		$this->rt_msg['content'] = $menu['control_menu'];
		$this->rt_msg['status'] = true;
	}
}