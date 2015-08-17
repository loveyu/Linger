<?php
/**
 * User: loveyu
 * Date: 2015/7/23
 * Time: 16:54
 */

namespace UView;

if(!class_exists('UView\ControlBase')){
	include_once(__DIR__ . "/ControlBase.php");
}

/**
 * 控制API
 * Class ControlApi
 * @package UView
 */
class ControlApi extends ControlBase{
	/**
	 * 菜单
	 */
	public function menu_list(){
		$menu = cfg()->load(_RootPath_ . "/config/control_menu.php");
		$this->rt_msg['content'] = $menu['control_menu'];
		$this->rt_msg['status'] = true;
	}
}