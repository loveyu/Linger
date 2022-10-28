<?php
/**
 * User: loveyu
 * Date: 2016/4/17
 * Time: 17:30
 */

namespace Core\Exception;


use CLib\SqlInterface;
use Exception;

class SqlException extends \Exception{
	/**
	 * @var SqlInterface
	 */
	private $sql;

	public function __construct(SqlInterface $sql, $message, $code = 0, Exception $previous = NULL){
		$this->sql = $sql;
		parent::__construct($message, $code, $previous);
	}
}