<?php
/**
 * User: loveyu
 * Date: 2015/8/15
 * Time: 11:40
 */

namespace UView;

if(!class_exists('UView\ControlBase')){
	include_once(__DIR__ . "/ControlBase.php");
}

class MgPicture extends ControlBase{
	public function pic_list(){
		$this->_set_true(["xxx"]);
	}
}