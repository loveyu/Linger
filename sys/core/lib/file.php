<?php
namespace CLib;
/**
 * 文件操作类
 * Class File
 */
class File{
	public function path_remove($path, $r = false){
		if(!$r){
			return @rmdir($path);
		} else{
			$handle = opendir($path);
			if(!$handle){
				return false;
			}
			while($file = readdir($handle)){
				if(($file == ".") || ($file == "..")){
					continue;
				}
				if(is_file($path . "/" . $file)){
					if(!unlink($path . "/" . $file)){
						return false;
					}
				} elseif(is_dir($path . "/" . $file)){
					if(!$this->path_remove($path . "/" . $file, $r)){
						return false;
					}
				}
			}
			closedir($handle);
			if(!rmdir($path)){
				return false;
			}
			return true;
		}
	}


	/**
	 * 读取文件夹的列表
	 * @param string $path
	 * @param string $system_encoding 当前系统编码，需要将文件转换为UTF-8
	 * @return array
	 */
	public function read_dir_files($path, $system_encoding = NULL){
		$path = self::utf82sys($path, $system_encoding);
		$fp = opendir($path);
		$rt_arr = array();
		if(!$fp){
			return $rt_arr;
		}
		while(($name = readdir($fp)) !== false){
			if($name == "." || $name == ".."){
				continue;
			}
			if(is_file($path . "/" . $name)){
				$rt_arr[] = self::sys2utf8($name, $system_encoding);
			}
		}
		closedir($fp);
		sort($rt_arr);
		return $rt_arr;
	}

	/**
	 * 转换UTF8编码到系统文件编码
	 * @param string      $path
	 * @param string|null $sys_encode
	 * @return string
	 */
	public static function utf82sys($path, $sys_encode = NULL){
		if(is_null($sys_encode)){
			$sys_encode = self::sysDefaultEncoding();
		}
		if($sys_encode == "UTF-8"){
			return trim($path);
		} else{
			return trim(mb_convert_encoding($path, $sys_encode, "UTF-8"));
		}
	}

	/**
	 * 转换系统文件编码到UTF8
	 * @param string      $path
	 * @param string|null $sys_encode
	 * @return string
	 */
	public static function sys2utf8($path, $sys_encode = NULL){
		if(is_null($sys_encode)){
			$sys_encode = self::sysDefaultEncoding();
		}
		if($sys_encode == "UTF-8"){
			return trim($path);
		} else{
			return trim(mb_convert_encoding($path, "UTF-8", $sys_encode));
		}
	}

	/**
	 * 获取系统默认编码
	 * @return string
	 */
	public static function sysDefaultEncoding(){
		if(PHP_OS == 'Linux'){
			return "UTF-8";
		} elseif(in_array(PHP_OS, array('WIN32', 'WINNT'))){
			return "GBK";
		} else{
			return "UTF-8";
		}
	}
}