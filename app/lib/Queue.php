<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-2-25
 * Time: 下午12:15
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace ULib;

use Core\Core;
use Core\Log;

/**
 * 队列处理
 * Class Queue
 * @package ULib
 */
class Queue{
	/**
	 * 开始执行队列
	 */
	public function run($type = NULL){
		$type = trim($type);
		if(substr(php_sapi_name(), 0, 3) === "cli"){
			//命令行模式循环等待执行
			while(true){
				$this->run_list($this->get_list($type));
				sleep(5);
			}
		} else{
			$this->run_list($this->get_list($type));
		}
	}

	/**
	 * 执行指定的队列列表
	 * @param array $list
	 */
	public function run_list($list){
		for($i = 0, $l = count($list); $i < $l; $i++){
			$v = $list[$i];
			$status = intval($v['status']);
			$message = NULL;
			try{
				//执行回调函数，如果没有返回异常则为成功执行
				$this->exec(intval($v['id']), $v['callback'], $v['param'], $v['library']);
				$status = 1;
			} catch(\Exception $ex){
				--$status;
				$message = $ex->getMessage();
				Log::write(___("Queue Error:") . $message, Log::ERR);
			}
			//更新队列信息
			if($status < 1 || _Debug_){
				//写入状态
				db()->update("queue", [
					'status' => $status,
					'up_time' => date("Y-m-d H:i:s"),
					'message' => $message
				], ['id' => $v['id']]);
			} else{
				//删除队列
				db()->delete("queue", ['id' => $v['id']]);
			}
			$list[$i] = NULL;
		}
	}

	/**
	 * 获取队列
	 * @param string $type
	 * @return array
	 */
	private function get_list($type = NULL){
		if(empty($type)){
			$case = ['status[<]' => 1];
		} else{
			$case = [
				'AND' => [
					'status[<]' => 1,
					'type' => $type
				]
			];
		}
		return db()->select("queue", [
			'id',
			'callback',
			'param',
			'library',
			'status'
		], [
			$case,
			'ORDER' => 'up_time DESC'
		]);
	}

	/**
	 * @param QueueCallback $call  回调类
	 * @param mixed         $param 参数
	 * @param string        $lib   Lib名称
	 */
	public function add(QueueCallback $call, $param, $lib){
		$time = date("Y-m-d H:i:s");
		//新将对应的数据序列化后存储到数据库中
		if(db()->insert("queue", [
				'time' => $time,
				'up_time' => $time,
				'callback' => serialize($call),
				'param' => serialize($param),
				'library' => serialize($lib)
			]) < 0
		){
			//添加错误记录
			$this->record_error(___("Add queue error on sql.") . debug("SQL error:" . implode(",", db()->error()['write'])));
		}
	}

	/**
	 * 记录错误信息
	 * @param $err
	 */
	private function record_error($err){
		Log::write($err, Log::ALERT);
	}

	/**
	 * 执行回调
	 * @param int    $id 队列的ID
	 * @param string $callback
	 * @param string $param
	 * @param string $library
	 * @throws \Exception
	 */
	public function exec($id, $callback, $param, $library){
		$lib = @unserialize($library);
		//首先加载反序列化所需的类库
		if(isset($lib['lib']) && is_array($lib['lib'])){
			call_user_func_array([
				lib(),
				'load'
			], $lib['lib']);
		}
		if(isset($lib['c_lib']) && is_array($lib['c_lib'])){
			call_user_func_array([
				c_lib(),
				'load'
			], $lib['c_lib']);
		}
		/**
		 * 对回调函数反序列化
		 * @var QueueCallback $call
		 */
		$call = @unserialize($callback);
		if(!is_object($call)){
			//初步判断是否为对象
			throw new \Exception(___("unserialize error"));
		}
		$ref = new \ReflectionClass($call);
		if(!in_array("ULib\\QueueCallback", $ref->getInterfaceNames())){
			//检测是否为正确的实现了接口
			throw new \Exception(___("callback class error."));
		}
		//最后执行,并使用对应的参数
		@$call->run($id, @unserialize($param));
	}
}

/**
 * 队列的接口
 * Interface QueueCallback
 * @package ULib
 */
interface QueueCallback{
	/**
	 * 执行回调函数
	 * @param int   $id    队列ID
	 * @param mixed $param 自定义参数
	 * @return mixed
	 */
	public function run($id, $param);
}