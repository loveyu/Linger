<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-28
 * Time: 下午12:39
 * Filename: AppException.php
 */

namespace ULib;


abstract class AppException{
	/**
	 * 抛出异常信息
	 * @param int $code
	 * @throws \Exception
	 */
	protected function throwMsg($code){
		$code = intval($code);
		throw new \Exception($this->getMsg($code), $code);
	}

	/**
	 * 获取异常信息
	 * @param int $code
	 * @return mixed
	 */
	public abstract function getMsg($code);
} 