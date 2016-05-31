<?php
/**
 * User: loveyu
 * Date: 2016/6/1
 * Time: 5:10
 */

namespace ULib;

/**
 * 简单的数组工具类
 * Class ArrayUtil
 * @package ULib
 */
final class ArrayUtil{
	/**
	 * 将一个键值数组转换为一个列表数据，将键名和键值分别设置为不同的字段值
	 * @param array  $map 键值数组
	 * @param string $key_name
	 * @param string $value_name
	 * @return array
	 */
	public static function kv2list($map, $key_name = 'name', $value_name = 'value'){
		if(!is_array($map) && !is_object($map)){
			return $map;
		}
		$rt = array();
		foreach($map as $k => $v){
			$rt[] = array(
				$key_name => $k,
				$value_name => $v,
			);
		}
		return $rt;
	}

	/**
	 * 从一个列表中提取一个KV数组
	 * @param array  $list
	 * @param string $key_name     键名字段
	 * @param string $value_name   值字段
	 * @param bool   $filter_empty 是否过滤掉空值
	 * @return array
	 */
	public static function list2kv($list, $key_name, $value_name, $filter_empty = false){
		$rt = array();
		if(!is_array($list)){
			return $rt;
		}
		foreach($list as $v){
			if($filter_empty && empty($v[$value_name])){
				continue;
			}
			$rt[$v[$key_name]] = $v[$value_name];
		}
		return $rt;
	}

	/**
	 * 从列表中提取某一键名的单独数组
	 * @param array  $list       列表
	 * @param string $value_name 键名
	 * @return array
	 */
	public static function list2v($list, $value_name){
		$rt = array();
		if(!is_array($list)){
			return $rt;
		}
		foreach($list as $v){
			$rt[] = $v[$value_name];
		}
		return $rt;
	}


	/**
	 * 找到一个键值数组，通过值在values中的数据
	 * @param array $kv_list
	 * @param array $values
	 * @return array
	 */
	public static function find_KV_by_values($kv_list, $values){
		$rt = array();
		foreach($kv_list as $k => $v){
			if(in_array($v, $values)){
				$rt[$k] = $v;
			}
		}
		return $rt;
	}

	/**
	 * 将一组数组从值中设置一个唯一主键
	 * @param array  $list    列表
	 * @param string $uni_key 唯一主键
	 * @return array
	 */
	public static function list2map_list($list, $uni_key){
		$rt = array();
		foreach($list as $v){
			$rt[$v[$uni_key]] = $v;
		}
		return $rt;
	}

	/**
	 * 提取一个数组中的几个值，并将对应对应的键值返回
	 * @param array $map     一个数组对象，非列表
	 * @param array $key_map 键名列表，如果非数字序列键名，将转为别名方式,这里只要判断是int类型就当做键值索引
	 * @return array
	 */
	public static function get_map_kv($map, $key_map){
		$rt = array();
		foreach($key_map as $k => $v){
			if(is_int($k)){
				$rt[$v] = array_key_exists($v, $map) ? $map[$v] : NULL;
			} else{
				$rt[$v] = array_key_exists($k, $map) ? $map[$k] : NULL;
			}
		}
		return $rt;
	}

	/**
	 * 提取一个数组中的几个值，并将对设置为对应的键值，并返回
	 * get_map_kv的数组版
	 * @param array $list    一组对象列表
	 * @param array $key_map 键名列表，如果非数字序列键名，将转为别名方式,这里只要判断是int类型就当做键值索引
	 * @return array
	 */
	public static function rebuild_list_map_kv($list, $key_map){
		$rt = array();
		foreach($list as $_k => $_v){
			if(!isset($rt[$_k])){
				$rt[$_k] = array();
			}
			foreach($key_map as $k => $v){
				if(is_int($k)){
					$rt[$_k][$v] = array_key_exists($v, $_v) ? $_v[$v] : NULL;
				} else{
					$rt[$_k][$v] = array_key_exists($k, $_v) ? $_v[$k] : NULL;
				}
			}
		}
		return $rt;
	}

	/**
	 * 将列表转换为一个键名关联的二维数组，提取field的公共键名，值一致的作为一组
	 * @param array  $list
	 * @param string $filed
	 * @return array
	 */
	public static function list2v_array($list, $filed){
		$rt = array();
		if(!is_array($list)){
			return $list;
		}
		foreach($list as $v){
			$key = $v[$filed];
			if(!isset($rt[$key])){
				$rt[$key] = array();
			}
			$rt[$key][] = $v;
		}
		return $rt;
	}

	/**
	 * 将一个数组列表对象中的几个字段转换为数值类型
	 * @param array           $list        列表
	 * @param array           $field_list  字段列表
	 * @param string|callable $number_call 自定义的转义函数
	 */
	public static function list_filed_to_number(&$list, $field_list, $number_call = 'doubleval'){
		if(!is_array($list)){
			return;
		}
		foreach($list as &$item){
			self::map_filed_to_number($item, $field_list, $number_call);
		}
	}

	/**
	 * 将一个对象中的几个字段的值转为数字类型
	 * @param array           $map         对象
	 * @param array           $field_list  字段列表
	 * @param string|callable $number_call 自定义转换函数
	 */
	public static function map_filed_to_number(&$map, $field_list, $number_call = 'doubleval'){
		if(!is_callable($number_call) || !is_array($map)){
			return;
		}
		foreach($field_list as $k){
			if(array_key_exists($k, $map)){
				$map[$k] = call_user_func($number_call, $map[$k]);
			}
		}
	}

	/**
	 * 对一个数组进行键名排序，排序方式保持和sort_arr的一致，如果不再sort_arr中的放在后面
	 * @param array $map      集合列表
	 * @param array $sort_arr 值列表
	 * @return array
	 */
	public static function sort_key_by_custom_arr($map, $sort_arr){
		$rt = array();
		foreach($sort_arr as $k){
			if(array_key_exists($k, $map)){
				$rt[$k] = $map[$k];
				unset($map[$k]);
			}
		}
		return array_merge($rt, $map);
	}
}