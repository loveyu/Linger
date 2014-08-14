<?php
$lock_file = __DIR__ . "/config/queue.lock";
$fp = fopen($lock_file, 'w');//写模式打开，文件不存在直接创建
if(!flock($fp, LOCK_EX | LOCK_NB)){
	//如果当前文件无法锁定，表示被其他进程锁定，所以结束执行
	//LOCK_EX为独享锁，LOCK_NB为非阻塞
	fclose($fp);
	die("Queue must be a single run.\n");
} else{
	echo "LOCK\n";
}
set_time_limit(0);
require_once("sys/config.php");
cfg()->load('config/all.php'); //加载其他配置文件
lib()->load('Queue', 'Hook');
$hook = new \ULib\Hook();
if(db()->status()){
	$queue = new \ULib\Queue();
	$queue->run();
} else{
	echo("Cannot connect to the database.");
}
flock($fp, LOCK_UN);
fclose($fp);