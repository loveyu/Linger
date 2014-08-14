<?php
namespace Core;
if(!defined('_CorePath_')){
	exit;
}

/**
 * 页面基础类
 */
class Page{
	/* 默认构造器 */
	function __construct(){
	}

	/**
	 * 加载页面
	 * @return bool
	 */
	protected function __load(){
		return call_user_func_array(array(
			Core::getInstance()->getUri(),
			'load'
		), func_get_args());
	}

	/**
	 * 加载404页面
	 */
	protected function __load_404(){
		Core::getInstance()->getUri()->load_404();
	}

	/**
	 * 加载类库
	 * @return \Core\Lib
	 */
	protected function __lib(){
		return call_user_func_array(array(
			Core::getInstance()->getLib(),
			'load'
		), func_get_args());
	}

	/**
	 * 加载视图
	 */
	protected function __view($file, $param = NULL){
		if(is_array($file)){
			foreach($file as $v){
				if(is_file(_ViewPath_ . "/$v")){
					$this->__view_f($v, $param);
				} else{
					Log::write(_("Can't load view file:") . $file, Log::NOTICE);
				}
			}
		} else{
			if(is_file(_ViewPath_ . "/$file")){
				$this->__view_f($file, $param);
			} else{
				Log::write(_("Can't load view file:") . $file, Log::NOTICE);
			}
		}
	}

	/**
	 * 包含存在的视图文件
	 * @param $file
	 * @param $param
	 */
	private function __view_f($file, $param){
		if(is_array($param)){
			foreach(array_keys($param) as $key){
				$tmp = "__" . $key;
				$$tmp = & $param[$key];
			}
			unset($key);
			unset($tmp);
		}
		include(_ViewPath_ . "/$file");
	}

	/**
	 * 获取调用该方法的类名，判断唯一性
	 * @return string
	 */
	public static function __class_name(){
		return get_called_class();
	}
}

?>