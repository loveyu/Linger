<?php
/**
 * User: loveyu
 * Date: 2015/1/28
 * Time: 0:36
 */


namespace CLib;

/**
 * Socket 日志记录类，使用无状态的UDP协议
 * Class SocketLog
 * @package CLib
 */
class SocketLog{
	/**
	 * 单实例
	 * @var SocketLog
	 */
	private static $instance = NULL;

	/**
	 * 当前状态
	 * @var bool
	 */
	private static $status = false;

	/**
	 * 日志写入Socket
	 * @var resource
	 */
	private $socket;

	/**
	 * 配置文件临时路径
	 * @var string
	 */
	protected $tmp_file;

	/**
	 * 初始化
	 */
	function __construct(){
		$this->tmp_file = _Cache_ . "/c_lib_socket_log_config.tmp";
	}

	/**
	 * 开始监听日志输出信息
	 * @param $ip
	 * @param $port
	 */
	public function Listen($ip, $port){
		$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP) or $this->error_abort();
		socket_bind($socket, $ip, $port) or $this->error_abort();
		$this->config_write($ip, $port);
		echo "Udp bind on {$ip}:{$port}\n";
		while(true){
			//单模式监听
			if(false === ($buf = socket_read($socket, 4096))){
				echo "ERROR on socket_read: " . socket_strerror(socket_last_error()) . "\n";
				break;
			}
			echo $buf;
		}
		//关闭服务，虽然到不了这步，可使用Ctrl+C结束
		socket_close($socket);
	}

	/**
	 * 终止程序，并输出错误
	 */
	private function error_abort(){
		$error_code = socket_last_error();
		$error_msg = socket_strerror($error_code);
		die("Socket error: [$error_code] $error_msg");
	}

	/**
	 * 获取实例
	 * @return SocketLog
	 */
	private static function getInstance(){
		if(self::$instance === NULL){
			//初始化链接，并对配置做相应操作
			self::$instance = new SocketLog();
			if(!self::$instance->Connect()){
				self::$instance->config_del();
			}
		}
		return self::$instance;
	}

	/**
	 * 写入配置文件
	 * @param $ip
	 * @param $port
	 */
	private function config_write($ip, $port){
		file_put_contents($this->tmp_file, serialize([
			$ip,
			$port
		]));
	}

	/**
	 * 读取配置文件
	 * @return mixed
	 */
	protected function config_read(){
		if(is_file($this->tmp_file) && is_readable($this->tmp_file)){
			return unserialize(file_get_contents($this->tmp_file));
		}
		return NULL;
	}

	/**
	 * 如果链接失败，可删除配置文件
	 */
	public function config_del(){
		if(is_file($this->tmp_file) && is_writeable($this->tmp_file)){
			unlink($this->tmp_file);
		}
	}

	/**
	 * 链接到Socket服务器
	 */
	private function Connect(){
		if(defined('SOCKET_LOG_STATUS') && !SOCKET_LOG_STATUS){
			return false;
		}
		list($ip, $port) = $this->config_read();
		if(!$ip || !$port){
			return false;
		}
		$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if(!$socket){
			return false;
		}
		if(!socket_connect($socket, $ip, $port)){
			return false;
		}
		self::$status = true;
		$this->socket = $socket;
		return true;
	}

	/**
	 * 向socket写入字符串
	 * @param string $data
	 */
	public function write($data){
		$data = date("[Y-m-d H:i:s] ", NOW_TIME) . $data;
		socket_write($this->socket, $data);
	}

	/**
	 * 开始记录信息
	 * @param mixed $msg
	 */
	public static function log($msg){
		$obj = self::getInstance();
		if(self::$status){
			if(is_string($msg)){
				$obj->write($msg . "\n");
			} else{
				$obj->write(print_r($msg, true) . "\n");
			}
		}
	}
}