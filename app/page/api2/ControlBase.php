<?php
/**
 * User: loveyu
 * Date: 2015/8/15
 * Time: 11:46
 */

namespace UView;


use Core\Page;

class ControlBase extends Page{
	/**
	 * @var array 最终用户返回消息
	 */
	protected $rt_msg = [
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
			$this->_set_msg('用户未登陆');
			$this->__exit();
		} else if(!login_user()->Permission("Control")){
			$this->_set_msg('权限不足');
			$this->__exit();
		}
	}

	/**
	 * 析构方法，输出JSON数据
	 */
	public function __destruct(){
		echo json_encode($this->rt_msg);
	}

	protected function _set_msg($msg){
		$this->rt_msg['msg'] = $msg;
	}

	protected function _set_code($code, $msg){
		$this->rt_msg['code'] = $code;
		$this->rt_msg['msg'] = $msg;
	}

	protected function _set_true($data){
		$this->rt_msg['content'] = $data;
		$this->rt_msg['status'] = true;
	}
}