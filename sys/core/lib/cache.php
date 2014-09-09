<?php
namespace CLib;

/**
 * 缓存驱动接口
 * Interface CacheInterface
 * @package CLib
 */
interface CacheInterface{
	/**
	 * 读取缓存内容
	 * @param string $name 名称
	 * @param int    $exp  超时，秒
	 * @return string|false 返回缓存内容
	 */
	public function read($name, $exp);

	/**
	 * 写入缓存内容
	 * @param string $name
	 * @param string $content
	 * @param int    $exp 超时
	 * @return void
	 */
	public function write($name, $content, $exp);
}

/**
 * 缓存类
 * Class Cache
 * @package CLib
 */
class Cache{

	/**
	 * @var CacheInterface
	 */
	private $drive;
	/**
	 * @var bool 是否启用缓存的状态
	 */
	private $status;
	/**
	 * @var int 设置的超时时间
	 */
	private $exp = 0;

	/**
	 * 构造方法
	 * @param string $drive_name   驱动名称
	 * @param array  $drive_config 驱动构造配置文件
	 * @throws \Exception 抛出驱动未找到的异常
	 */
	function __construct($drive_name = 'File', $drive_config = []){
		if(hook()->apply("Cache_set", true)){
			//只有当缓存启用时才调用页面缓存
			$this->status = true;
			if(empty($drive_name)){
				$drive_name = "File";
			}
			c_lib()->load('cache/' . $drive_name);
			$drive_name = "CLib\\Cache\\" . $drive_name;
			if(!class_exists($drive_name)){
				throw new \Exception(_("Cache Drive Not Found"));
			}
			$this->drive = new $drive_name($drive_config);
			hook()->add("Uri_load_begin", [
				$this,
				'hook_begin'
			]);
			hook()->add("Uri_load_end", [
				$this,
				'hook_end'
			]);
		} else{
			$this->status = false;
		}
	}

	/**
	 * 设置控制器的页面缓存。当存在缓存时该方法调用die()结束，并加载钩子
	 * @param float $min 缓存的时间
	 */
	public function set($min){
		if(!$this->status){
			return;
		}
		$this->exp = $min * 60;
		$content = $this->drive->read(md5(URL_NOW), $this->exp);
		if($content !== false){
			echo $content;
			if(ob_get_length()){
				//刷新缓冲区
				@ob_flush();
				@flush();
				@ob_end_flush();
			}
			hook()->apply("Cache_set_success", NULL);//缓存设置结束前钩子
			exit;
		}
	}

	/**
	 * 开始钩子
	 */
	public function hook_begin(){
		if($this->status){
			ob_start();
		}
	}

	/**
	 * 结束钩子
	 */
	public function hook_end(){
		if($this->status && $this->exp > 0){
			$content = ob_get_contents();
			$this->drive->write(md5(URL_NOW), $content, $this->exp);
		}
	}
}