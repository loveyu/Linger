<?php
c_lib()->load('sql');
$sql = new \CLib\Sql(cfg()->get('sql', 'write'), cfg()->get('sql', 'read'));
$write = $sql->getWriter();
if(!is_object($write)){
	die("数据库连接失败");
}
$database_name = cfg()->get('sql', 'write', 'database_name');
//header("Content-Type: text/plain; charset=utf-8");

//创建表操作
$sql_file = file_get_contents(__DIR__."/table.sql");
$list = preg_split("/-- [-]+/", $sql_file);
foreach($list as $v){
	$v = trim($v);
	if(!isset($v[0]) || $v[0] == '-'){
		continue;
	}
	$write->pdo->exec($v);
	error_check($write->pdo);
}
error_check($write->pdo);

//创建存储过程操作
$sql_file = file_get_contents(__DIR__."/procedure.sql");
$sql_file = str_replace("`{database_name}`", "`$database_name`", $sql_file);
$list = preg_split("/-- [-]+/", $sql_file);
foreach($list as $v){
	$v = trim($v);
	if(!isset($v[0]) || $v[0] == '-'){
		continue;
	}
	$write->pdo->exec($v);
	error_check($write->pdo);
}

//创建触发器
$sql_file = file_get_contents(__DIR__."/trigger.sql");
$list = preg_split("/-- [-]+/", $sql_file);
foreach($list as $v){
	$v = trim($v);
	if(!isset($v[0]) || $v[0] == '-'){
		continue;
	}
	$write->pdo->exec($v);
	error_check($write->pdo);
}


$session->set('install', [
	'number' => '3',
	'list' => []
]);
echo "true";

function error_check(PDO $pdo){
	if($pdo->errorCode() !== "00000"){
		global $v;
		echo "ERROR:";
		echo implode(",", $pdo->errorInfo());
		echo "\n-----------------SQL----------------\n";
		echo $v;
		exit;
	}
}
