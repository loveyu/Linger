<?php
/**
 * User: loveyu
 * Date: 2016/5/30
 * Time: 0:44
 */

namespace ULib;
use Core\Log;

/**
 * elasticsearch 简单操作类
 * 中文文档 http://es.xiaoleilu.com/index.html
 * https://github.com/looly/elasticsearch-definitive-guide-cn
 * Class ElasticsearchBase
 * @package ULib
 */
class ElasticsearchBase{
	/**
	 * @var string 服务器地址必须以斜杠结尾
	 */
	private $server_url;

	/**
	 * @var string 前缀名称
	 */
	private $prefix;

	/**
	 * @var array 错误信息,可以是一个数组error,code
	 */
	private $error_msg;

	/**
	 * 初始化
	 * @param string $server_url 服务器地址
	 * @param string $pre_name   索引前缀名称，默认无
	 */
	public function __construct($server_url, $pre_name = ''){
		if(empty($server_url)){
			Log::write("ElasticsearchBase Server Url Is Empty", Log::ERR);
		}
		$this->server_url = $server_url;
		$this->prefix = $pre_name;
	}

	/**
	 * 创建索引（建数据库）
	 * @param string     $index_name
	 * @param array|null $config 传入配置文件，如果为空则默认只创建库
	 * @return bool
	 */
	public function create_index($index_name, $config = NULL){
		$data = $this->curl("{$this->server_url}{$this->prefix}{$index_name}", "PUT", $config);
		return $this->parse_action($data);
	}


	/**
	 * 创建类型（建表）
	 * @param string $index_name 索引名（数据库名）
	 * @param string $types_name 类型名（表名）
	 * @param array  $cfg        配置信息，如下，详见 https://www.elastic.co/guide/en/elasticsearch/reference/1.4/mapping-core-types.html
	 *                           {
	 *                           "_all": {
	 *                           "analyzer": "ik_max_word",
	 *                           "search_analyzer": "ik_max_word",
	 *                           "term_vector": "no",
	 *                           "store": "false"
	 *                           },
	 *                           "properties": {
	 *                           "content": {
	 *                           "type": "string",
	 *                           "store": "no",
	 *                           "term_vector": "with_positions_offsets",
	 *                           "analyzer": "ik_max_word",
	 *                           "search_analyzer": "ik_max_word",
	 *                           "include_in_all": "true",
	 *                           "boost": 8
	 *                           }
	 *                           }
	 *                           }
	 * @return bool
	 */
	public function create_types($index_name, $types_name, $cfg){
		$cfg = array(
			$types_name => $cfg,
		);
		$data = $this->curl("{$this->server_url}{$this->prefix}{$index_name}/{$types_name}/_mapping", "PUT", $cfg);
		return $this->parse_action($data);
	}

	/**
	 * 解析操作结果
	 * @param array $data
	 * @return bool
	 */
	protected function parse_action($data){
		if(isset($data['acknowledged'])){
			$rt = (bool)$data['acknowledged'];
			if(!$rt){
				$this->set_error("未知错误", -1);
			}
			return $rt;
		}
		if(isset($data['error'])){
			$this->set_error("[{$data['error']['type']}]{$data['error']['reason']}", $data['status']);
			return false;
		}
		$this->set_error("未知错误" . json_encode($data), -2);
		return false;
	}


	/**
	 * 删除索引
	 * @param string $name
	 * @return bool
	 */
	public function delete_index($name){
		$data = $this->curl("{$this->server_url}{$this->prefix}{$name}", "DELETE", NULL);
		return $this->parse_action($data);
	}

	/**
	 * 更新一个文档
	 * @param string $index    索引
	 * @param string $type     类型
	 * @param string $key      主键
	 * @param array  $document 文档数据
	 * @return bool
	 */
	public function put_document($index, $type, $key, $document){
		$data = $this->curl("{$this->server_url}{$this->prefix}{$index}/{$type}/{$key}", "PUT", $document);
		if(array_key_exists('created', $data)){
			return (int)$data['created'];
		} else{
			$this->set_error($data['error']['reason'], (int)$data['status']);
			return false;
		}
	}

	/**
	 * 批量删除一批数据
	 * @param string $index
	 * @param string $type
	 * @param array  $keys 键名列表
	 * @return int
	 */
	public function bulk_delete($index, $type, $keys){
		if(empty($keys)){
			return false;
		}
		$path = "{$this->server_url}{$this->prefix}{$index}/{$type}/_bulk";
		$data = array();
		foreach($keys as $v){
			$data[] = json_encode(array("delete" => array("_id" => $v)));
		}
		//这里必须多一个\n，否者会报错，但直接使用工具操作无异常
		$result = $this->curl($path, "POST", implode("\n", $data) . "\n");
		if(!isset($result['items']) || empty($result['items'])){
			return false;
		}
		$num = 0;
		foreach($result['items'] as $v){
			if(isset($v['delete']) && isset($v['delete']['found']) && $v['delete']['found']){
				$num++;
			}
		}
		return $num;
	}

	/**
	 * 删除一个文档
	 * @param string $index 索引
	 * @param string $type  类型
	 * @param string $key   主键
	 * @return bool
	 */
	public function delete_document($index, $type, $key){
		$data = $this->curl("{$this->server_url}{$this->prefix}{$index}/{$type}/{$key}", "DELETE");
		if(array_key_exists('found', $data)){
			return (bool)$data['found'];
		} else{
			$this->set_error("数据异常", -1);
			return false;
		}
	}

	/**
	 * 提交一个原始POST
	 * @param string $index 索引
	 * @param string $path  跟在索引后面的数据
	 * @param array  $data
	 * @return array
	 */
	public function post($index, $path, $data){
		return $this->curl("{$this->server_url}{$this->prefix}{$index}/{$path}", "POST", $data);
	}

	/**
	 * 创建一个CURL请求
	 * @param string       $url
	 * @param string       $method
	 * @param string|array $payload
	 * @return mixed
	 */
	protected function curl($url, $method, $payload = NULL){
		if(isset($_GET['_search_debug']) && $_GET['_search_debug']){
			if(is_object($payload) || is_array($payload)){
				echo json_encode($payload);
			}
		}
		$conn = curl_init();
		curl_setopt($conn, CURLOPT_URL, $url);
		curl_setopt($conn, CURLOPT_TIMEOUT, 30);
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($conn, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt($conn, CURLOPT_FORBID_REUSE, 0);
		if(is_array($payload) && count($payload) > 0){
			curl_setopt($conn, CURLOPT_POSTFIELDS, json_encode($payload));
		} elseif($payload !== NULL){
			curl_setopt($conn, CURLOPT_POSTFIELDS, $payload);
		}
		$response = curl_exec($conn);
		if($response !== false){
			$data = json_decode($response, true);
			if(!$data){
				$this->set_error($response, curl_getinfo($conn, CURLINFO_HTTP_CODE));
				return false;
			}
		} else{
			$errno = curl_errno($conn);
			$this->set_error(curl_error($conn), $errno);
			return false;
		}
		return $data;
	}

	/**
	 * 设置错误信息
	 * @param string $msg  错误信息
	 * @param string $code 错误代码
	 */
	protected function set_error($msg, $code){
		$this->error_msg = array(
			'error' => $msg,
			'code' => $code,
		);
	}

	/**
	 * 返回错误信息
	 * @return array {error,code}
	 */
	public function get_error_msg(){
		return $this->error_msg;
	}
}