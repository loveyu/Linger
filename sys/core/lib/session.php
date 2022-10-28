<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-8
 * Time: 下午9:36
 */

namespace CLib;

c_lib()->load('interface/SessionInterface');

/**
 * 用户Session类
 * Class Session
 * @package CLib
 */
class Session implements SessionInterface
{
	/**
	 * Session实例
	 * @var SessionInterface
	 */
	private $drive;

	/**
	 * @param string $drive_name
	 * @param array $drive_cfg 驱动配置
	 * @throws \Exception
	 */
	function __construct($drive_name = 'Local',$drive_cfg=[]) {
		c_lib()->load('session/' . $drive_name);
		$drive_name = "CLib\\Session\\" . $drive_name;
		if(!class_exists($drive_name)){
			throw new \Exception(___("Session Drive Not Found"));
		}
		$this->drive = new $drive_name($drive_cfg);
	}

	public function get($name) {
		return call_user_func_array([
			$this->drive,
			'get'
		], func_get_args());
	}

	public function set($name, $value) {
		return call_user_func_array([
			$this->drive,
			'set'
		], func_get_args());
	}

	public function delete($name) {
		return call_user_func_array([
			$this->drive,
			'delete'
		], func_get_args());
	}

	/**
	 * 彻底删除SESSION
	 * @return void
	 */
	public function destroy() {
		$this->drive->destroy();
	}
}