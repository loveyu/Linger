<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-8
 * Time: 下午8:47
 */

namespace ULib;


/**
 * Class Captcha
 * @package ULib
 */
class Captcha{
	/**
	 * @var resource 保存的零时图片资源
	 */
	private $resource;

	/**
	 * 在SESSION中存储的名称
	 * @var string
	 */
	private $session_name;

	/**
	 * 构造函数
	 * @param string $session_name
	 */
	function __construct($session_name = 'Captcha'){
		$this->session_name = $session_name;
	}


	/**
	 * 释放图片资源
	 */
	function __destruct(){
		// TODO: Implement __destruct() method.
		if(is_resource($this->resource)){
			imagedestroy($this->resource);
		}
	}


	/**
	 * 创建一个验证码的图片资源
	 * @param $id int 默认的验证码序号
	 * @return resource
	 */
	public function create($id = 0){
		$this->clean_code($id);
		//$code 为2个长度的数组,0为需哟绘制的图形,1为存储在session中的答案
		$code = [rand(1000, 9999) . ""];
		$code[1] = $code[0];
		session()->set($this->session_name . "_" . $id, $code[1]);
		$code = hook()->apply("Captcha_create_code", $code);
		return $this->resource = $this->create_resource($code[0]);
	}

	/**
	 * 创建图片资源文件
	 * @param $code
	 * @return resource
	 */
	private function create_resource($code){
		$resource = hook()->apply("Captcha_create_resource", NULL, $code);
		if(!is_resource($resource)){
			$resource = imagecreate(60, 20);
			$black = ImageColorAllocate($resource, 0, 0, 0); //RGB黑色标识符
			$white = ImageColorAllocate($resource, 255, 255, 255); //RGB白色标识符
			imagefill($resource, 0, 0, $white);
			imagestring($resource, 5, 10, 3, $code, $black);
		}
		return $resource;
	}

	/**
	 * 销毁验证码
	 * @param $id int 验证码ID
	 */
	private function clean_code($id){
		session()->delete($this->session_name . "_" . $id);
	}

	/**
	 * 获取验证码
	 * @param $id int 验证码ID
	 * @return string
	 */
	private function get_code($id){
		return session()->get($this->session_name . "_" . $id);
	}

	/**
	 * 验证当前验证码是否正确
	 * @param      $code
	 * @param bool $destroy 是否销毁当前验证码
	 * @param      $id      int 默认的验证码序号
	 * @return bool
	 */
	public function verify($code, $destroy = false, $id = 0){
        $code = strtolower(trim((string)$code));
        if ($code === '') {
            return false;
        }
		$s_code = $this->get_code($id);
		if($s_code === $code){
			if($destroy){
				$this->clean_code($id);
			}
			return true;
		}
		return false;
	}

}