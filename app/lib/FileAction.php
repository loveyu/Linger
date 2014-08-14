<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-1
 * Time: 下午12:16
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace ULib;


use Core\Log;

class FileAction{
	private $status = false;
	private $root = "";

	function __construct($meta){
		if(isset($meta['server_root_path']) && is_dir($meta['server_root_path']) && is_writable($meta['server_root_path'])){
			$this->root = $meta['server_root_path'] . "/";
			$this->status = true;
		}
	}

	public function delete(){
		if($this->status){
			$list = array_flip(array_flip(func_get_args()));
			foreach($list as $v){
				$path = $this->root . $v;
				if(is_file($path) && is_writable($path)){
					if(!unlink($path)){
						Log::write(_("Image file delete fail:") . $path);
					}
				}
			}
		} else{

		}
	}
}