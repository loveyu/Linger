<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-8
 * Time: 下午9:36
 */

namespace CLib;

/**
 * Session 操作接口
 * Interface SessionInterface
 * @package CLib
 */
interface SessionInterface
{
	/**
	 * GET操作
	 * @param $name string 数组键名
	 * @return mixed
	 */
	public function get($name);

	/**
	 * 设置操作
	 * @param $name string 数组键名
	 * @param $value string 对应的值
	 * @return bool
	 */
	public function set($name, $value);

	/**
	 * 删除操作
	 * @param $name string 数组键名
	 * @return bool
	 */
	public function delete($name);

	/**
	 * 彻底删除SESSION
	 * @return void
	 */
	public function destroy();
}

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
	 * @throws \Exception
	 */
	function __construct($drive_name = 'Local') {
		c_lib()->load('session/' . $drive_name);
		$drive_name = "CLib\\Session\\" . $drive_name;
		if(!class_exists($drive_name)){
			throw new \Exception(_("Session Drive Not Found"));
		}
		$this->drive = new $drive_name();
	}

	public function get($name) {
		// TODO: Implement get() method.
		return call_user_func_array([
			$this->drive,
			'get'
		], func_get_args());
	}

	public function set($name, $value) {
		// TODO: Implement set() method.
		return call_user_func_array([
			$this->drive,
			'set'
		], func_get_args());
	}

	public function delete($name) {
		// TODO: Implement delete() method.
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
		// TODO: Implement destroy() method.
		$this->drive->destroy();
	}
}