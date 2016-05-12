<?php
/**
 * User: loveyu
 * Date: 2016/5/12
 * Time: 20:44
 */
require_once __DIR__ . "/../../sys/config.php";
cfg()->load('../../config/all.php'); //加载其他配置文件
lib()->load('Queue', 'Hook');
$hook = new \ULib\Hook();
if(!db()->status()){
	die("DB error");
}
$db = db();
$id = 4614;
$page = 0;
$i = 0;
do{
	$list = $db->select("pictures", "*", [
		"id[>]" => $id,
		'ORDER' => 'id ASC',
		'LIMIT' => [0, 100]
	]);
	if(empty($list)){
		break;
	}
	foreach($list as $item){
		echo "{$item['id']}\n";
		$update = [];
		if(count(explode("/", $item["pic_path"])) != 3){
			die("Error: " . print_r($item, true));
		}
		foreach(['pic_path', 'pic_thumbnails_path', 'pic_hd_path', 'pic_display_path'] as $type){
			$pic_path = $item[$type];
			$base_name = basename($pic_path);
			$pic_path = dirname($pic_path);
			$pic_path .= "/" . substr(md5($base_name), 0, 2) . "/{$base_name}";
			$update[$type] = $pic_path;
		}
		$db->update("pictures", $update, ['id' => $item['id']]);
		$id = (int)$item['id'];
	}
	$page++;
} while(1);