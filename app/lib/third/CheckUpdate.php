<?php
namespace ULib\third;

/**
 * Class CheckUpdate
 * @package ULib\third
 */
class CheckUpdate{
	/**
	 * @var string URl地址
	 */
	private $_uri = NULL;
	/**
	 * @var array
	 */
	private $_get = [];
	/**
	 * @var array
	 */
	private $_post = [];

	/**
	 * @param  string $update_uri
	 * @param array   $default_get_param
	 * @throws \Exception
	 */
	function __construct($update_uri, $default_get_param = []){
		if(!filter_var($update_uri, FILTER_VALIDATE_URL)){
			throw new \Exception(_("The update uri validate error."));
		}
		$this->_uri = $update_uri;
		$this->add_get_param($default_get_param);
		$this->add_post_param(['host' => $_SERVER['HTTP_HOST']]);
	}

	/**
	 * 添加GET参数
	 * @param array $arr
	 * @return int
	 */
	public function add_get_param($arr){
		if(is_array($arr)){
			$this->_get = array_merge($this->_get, $arr);
		}
		return count($this->_get);
	}

	/**
	 * 添加POST参数
	 * @param array $arr
	 * @return int
	 */
	public function add_post_param($arr){
		if(is_array($arr)){
			$this->_post = array_merge($this->_post, $arr);
		}
		return count($this->_post);
	}

	/**
	 * 更新执行检测
	 * @param callback $need_check 判断是否需要检测更新
	 * @param callback $before     更新之前的调用,参数为当前对象[$this]
	 * @param callback $end        更新之后的调用，参数为返回的数据[$data]
	 * @return null|string
	 */
	public function exec($need_check, $before, $end){
		$data = null;
		if(is_callable($need_check)){
			if(!call_user_func($need_check)){
				return null;
			}
		}
		if(is_callable($before)){
			call_user_func($before, $this);
		}
		if(is_callable($end)){
			$data = $this->get_data();
			call_user_func($end, $data);
		}
		return $data;
	}

	/**
	 * 获取数据返回
	 * @return mixed
	 */
	private function get_data(){
		$get = [];
		foreach($this->_get as $key => $value){
			$get[] = $key . "=" . urlencode($value);
		}
		if(!count($get)){
			$url = $this->_uri;
		} else{
			$url = $this->_uri . "?" . implode("&", $get);
		}
		$ch = curl_init($url);
		if(count($this->_post)){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_post);
		}
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$return = curl_exec($ch);
		curl_close($ch);
		return $return;
	}

}