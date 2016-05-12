<?php
/**
 * User: loveyu
 * Date: 2016/5/12
 * Time: 2:20
 */
require_once __DIR__ . "/../../sys/config.php";
require_once __DIR__ . "/../../sys/core/lib/interface/SqlInterface.php";
require_once __DIR__ . "/../../sys/core/lib/medoo.php";
$db = new medoo([
	'database_type' => 'mysql',
	'server' => 'localhost',
	'username' => 'root',
	'password' => '123456',
	'charset' => 'utf8',
	'database_name' => 'pic',
	'option' => [ //PDO选项
		PDO::ATTR_CASE => PDO::CASE_NATURAL,
		PDO::ATTR_TIMEOUT => 5
	],
]);
$db2 = new medoo([
	'database_type' => 'mysql',
	'server' => 'localhost',
	'username' => 'root',
	'password' => '123456',
	'charset' => 'utf8',
	'database_name' => 'linger_travel',
	'option' => [ //PDO选项
		PDO::ATTR_CASE => PDO::CASE_NATURAL,
		PDO::ATTR_TIMEOUT => 5
	],
]);
$user_list = $db2->select("users", array("user_name", "id"), array());
$user_count = count($user_list);
$page = 0;
$q_id = 0;
$i = 0;
do{
	$ids = $db->select("pic_tbl", array('id'), array(
		'AND' => [
			'id[>]' => $q_id,
			'user_id' => 0
		],
		'ORDER' => 'id ASC',
		'LIMIT' => [0, 100]
	));
	if(count($ids) == 0){
		echo $db->last_query();
		break;
	}
	foreach($ids as $v){
		echo "{$i}\n";
		$i++;
		$q_id = $v['id'];
		$rand = rand(0, $user_count - 1);
		$user = $user_list[$rand];
		$db->update("pic_tbl", [
			'user_id' => $user['id'],
			'user_name' => $user['user_name']
		], ['id' => $q_id]);
	}
	$page++;
} while(1);