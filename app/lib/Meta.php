<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-9
 * Time: 下午7:13
 */

namespace ULib;


/**
 * 数据库表标签类
 * Class Meta
 * @package ULib
 */
class Meta{
	/**
	 * 表名
	 * @var string
	 */
	private $table;
	/**
	 * 外键名
	 * @var string
	 */
	private $out_key;
	/**
	 * 外键值
	 * @var int|string|array
	 */
	private $out_key_value;

	/**
	 * 标签列表
	 * @var array
	 */
	private $list = [];

	/**
	 * @param string $table
	 * @param string $out_key
	 * @param int    $out_key_value
	 */
	public function __construct($table, $out_key, $out_key_value){
		$this->out_key = $out_key;
		$this->out_key_value = $out_key_value;
		$this->table = $table;
	}

	/**
	 * 获取数据
	 * @param array $key_list 待获取的数据数组列表
	 * @param null  $implode  多个数据的连接字符，NULL时不连接
	 * @return array 二维数组或三维数组
	 */
	public function get($key_list, $implode = NULL){
		$s = [];
		$rt = [];
		$conn = is_string($implode);
		foreach($key_list as $k){
			if(!isset($this->list[$k])){
				$s[] = $k;
			} else{
				$rt[$k] = $conn ? implode($implode, $this->list[$k]) : $this->list[$k];
			}
		}
		if(!empty($s)){
			$this->select($s);
			foreach($s as $k){
				$rt[$k] = $conn ? implode($implode, $this->list[$k]) : $this->list[$k];
			}
		}
		return $rt;
	}

	/**
	 * 获取所有标签
	 * @return array
	 */
	public function get_all(){
		$this->select(NULL);
		return $this->list;
	}

	/**
	 * @param $list
	 *            $list = [
	 *            'key'=>value,
	 *            'key2'=>value2,
	 *            'key3'=>[vk31,vk32,vk33],
	 *            ]
	 * @return int
	 * @throws \Exception
	 */
	public function insert($list){
		$pdo = db()->getWriter()->pdo;
		$table = substr($pdo->quote($this->table), 1, -1);
		$out_key = substr($pdo->quote($this->out_key), 1, -1);
		$values = [];
		$map = ['out_key_value' => $this->out_key_value];
		foreach($list as $key => $v){
			$md5_key = md5($key);
			$map[":meta_key_$md5_key"] = $key;
			if(is_array($v)){
				foreach($v as $id2 => $v2){
					$values[] = "(:out_key_value,:meta_key_$md5_key,:value_$md5_key$id2)";
					$map[":value_$md5_key$id2"] = $v2;
				}
			} else{
				$values[] = "(:out_key_value,:meta_key_$md5_key,:value_$md5_key)";
				$map[":value_$md5_key"] = $v;
			}
		}
		if(count($values) < 0){
			throw new \Exception(_("Meta insert no found data"));
		}
		$stmt = db()->getWriter()->prepare("Insert into `$table`(`$out_key`,`meta_key`,`meta_value`)values" . implode(', ', $values) . ";");
		if(!$stmt->execute($map)){
			throw new \Exception(_("Meta insert Sql error.") . debug("ERROR:" . implode(", ", $stmt->errorInfo())));
		}
		return $stmt->rowCount();
	}

	/**
	 * 设置数据，不存在时插入
	 * @param $list
	 *            $list = [
	 *            'key'=>value,
	 *            'key2'=>value2,
	 *            ]
	 * @throws \Exception
	 */
	public function set($list){
		$pdo = db()->getWriter()->pdo;
		$table = substr($pdo->quote($this->table), 1, -1);
		$out_key = substr($pdo->quote($this->out_key), 1, -1);
		$out_key_value = $pdo->quote($this->out_key_value);
		$pdo->beginTransaction();
		foreach($list as $name => $value){
			$arr = [
				':meta_value' => $value,
				':out_key_value' => $this->out_key_value,
				':meta_key' => $name
			];
			$stmt = $pdo->prepare("Update `$table` set `meta_value`=:meta_value where `$out_key`=:out_key_value AND `meta_key`=:meta_key;");
			if(!$stmt->execute($arr)){
				$pdo->rollBack();
				throw new \Exception(_("Meta set Error on Update.") . debug("ERROR:" . implode(", ", $stmt->errorInfo())));
			}
			if($stmt->rowCount() == 0){
				$tmp = $pdo->quote($name);
				$stmt = $pdo->query("SELECT EXISTS(SELECT 1 FROM `$table` where `$out_key`=$out_key_value AND `meta_key`=$tmp);");
				if($stmt === false){
					$pdo->rollBack();
					throw new \Exception(_("Meta set Error on EXISTS.") . debug("ERROR:" . implode(", ", $stmt->errorInfo())));
				}
				if($stmt->fetchColumn() !== '1'){
					$stmt = $pdo->prepare("Insert into `$table`(`$out_key`,`meta_key`,`meta_value`)values(:out_key_value,:meta_key, :meta_value);");
					if(!$stmt->execute($arr)){
						$pdo->rollBack();
						throw new \Exception(_("Meta set Error on Insert.") . debug("ERROR:" . implode(", ", $stmt->errorInfo())));
					}
				}
			}
		}
		$pdo->commit();
	}

	/**
	 * 删除Meta
	 * @param array $key_list
	 * @throws \Exception
	 */
	public function delete($key_list){
		foreach($key_list as $v){
			unset($this->list[$v]);
		}
		if(db()->delete($this->table, [
				"AND" => [
					$this->out_key => $this->out_key_value,
					"meta_key" => $key_list
				]
			]) === false
		){
			throw new \Exception(_("Meta key delete error on SQL.") . debug("SQL MSG:" . implode(",", db()->error()['write'])));
		}
	}

	/**
	 * 从数据库中选值到list中
	 * @param array|string $key_list
	 * @throws \Exception 数据库出错
	 */
	private function select($key_list){
		$data = false;
		if($key_list === NULL){
			$data = db()->select($this->table, [
				'meta_key',
				'meta_value'
			], [$this->out_key => $this->out_key_value]);
		} else{
			$data = db()->select($this->table, [
				'meta_key',
				'meta_value'
			], [
				"AND" => [
					$this->out_key => $this->out_key_value,
					'meta_key' => $key_list
				]
			]);
		}
		if($data === false){
			throw new \Exception(_("Get meta on ") . $this->table . " error." . debug(implode(', ', db()->error()['read'])));
		}
		//print_r($data);
		//预防该标签不存在的情况
		if(!empty($key_list)){
			foreach($key_list as $k){
				$this->list[$k] = [];
			}
		}
		foreach($data as $v){
			$this->list[$v['meta_key']][] = $v['meta_value'];
		}
	}

} 