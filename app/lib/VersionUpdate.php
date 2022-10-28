<?php
/**
 * User: loveyu
 * Date: 2014/9/30
 * Time: 8:28
 */

namespace ULib;

lib()->load('third/CheckUpdate');

use ULib\third\CheckUpdate;

class VersionUpdate{
	private $cu;
	private $lock_file;
	private $check_flag = NULL;
	private $update_info = NULL;

	/**
	 * 构造函数
	 */
	function __construct(){
		$lib = lib();
		if(is_object($lib->using('version_update'))){
			throw new \Exception(___("Only allow run a update."));
		}
		$this->cu = new CheckUpdate(_UPDATE_URL_, ['version' => _VERSION_]);
		$lib->add("version_update", $this);
		$this->lock_file = _SysPath_ . "/update.lock";
	}

	/**
	 * 获取更新信息
	 * @return array | false
	 */
	public function get_update_info(){
		if($this->update_info !== NULL){
			return $this->update_info;
		}
		if(is_file($this->lock_file)){
			$this->update_info = json_decode(file_get_contents($this->lock_file), true);
		} else{
			$this->update_info = false;
		}
		return $this->update_info;
	}

	/**
	 * 判断是否需要更新检测
	 * @return bool
	 */
	public function need_update(){
		if($this->check_flag === NULL){
			if(!is_file($this->lock_file)){
				$this->check_flag = false;
				file_put_contents($this->lock_file, "");
			} else{
				$this->check_flag = filemtime($this->lock_file) + 14400 < NOW_TIME;
			}
		}
		return $this->check_flag;
	}

	/**
	 * 更新脚本输出
	 */
	public function update_script(){
		$info = $this->get_update_info();
		$hook = hook();
		if(!empty($info['version']) && version_compare($info['version'], _VERSION_, ">")){
			//输出更新提醒信息
			$hook->add("view_control_main", function (){
				/**
				 * @var $obj VersionUpdate
				 */
				$obj = lib()->using("version_update");
				$info = $obj->get_update_info();
				echo "<script>jQuery(function(){update_show(" . json_encode($info['version']) . ")})</script>";
			});
		}
		if($this->need_update()){
			//添加异步更新检测脚本
			$hook->add("view_control_main", function (){
				echo "<script>jQuery(function(){update_check();})</script>";
			});
		}
	}

	/**
	 * 回写数据
	 * @param $data
	 */
	public function write_data($data){
		$data_arr = json_decode($data, true);
		file_put_contents($this->lock_file, isset($data_arr['version']) ? $data : NULL);
	}

	/**
	 * 远程调用
	 * @param bool $force 是否强制检测
	 * @return string 新版本的版本号，如果没有新版本，返回空
	 */
	public function check($force = false){
		$data = $this->cu->exec($force ? function (){ return true; } : [
			$this,
			'need_update'
		], function (){ }, [
			$this,
			'write_data'
		]);
		$data = json_decode($data, true);
		if(isset($data['version']) && version_compare($data['version'], _VERSION_, ">")){
			return $data['version'];
		}
		return "";
	}
}