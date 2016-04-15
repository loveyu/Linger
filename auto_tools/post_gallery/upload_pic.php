<?php
/**
 * 自动图集发布
 * User: loveyu
 * Date: 2016/4/11
 * Time: 0:20
 */
$config = array_merge([
	'dir_path' => '',
	'url' => '',
	'content' => ''
], include __DIR__ . "/config.php");
require_once __DIR__ . "/../../sys/config.php";
c_lib()->load('file');
$file = new \CLib\File();
$list = $file->read_dir_files($config['dir_path']);
$content_length = mb_strlen($config['content']);
$data = array();
foreach($list as $v){
	$path = $config['dir_path'] . "/" . $v;
	$size = filesize($file::utf82sys($path));
	if($size < 1000 || $size > 5 * 1024 * 1024){
		//数据无效
		continue;
	}
	$ext = strtolower(pathinfo($v, PATHINFO_EXTENSION));
	if(!in_array($ext, ['jpg', 'png', 'gif'])){
		continue;
	}
	$title = mb_substr($config['content'], rand(0, $content_length - 30), rand(15, 30));
	$desc = mb_substr($config['content'], rand(0, $content_length - 120), rand(15, 120));
	$tags = [];
	$tag_length = rand(0, 8);
	for($i = 0; $i < $tag_length; $i++){
		$tags[] = mb_substr($config['content'], rand(0, $content_length - 5), rand(1, 5));
	}
	$tags = array_unique($tags);
	$data[] = compact('path', 'title', 'desc', 'tags');
}
$api = $config['url'] . "UserApi/picture_upload";
$count = count($data);
foreach($data as $i => $item){
	$i += 1;
	if($i < 68){
		continue;
	}
	echo "\nRun:{$i}/{$count}\n";
	$ch = curl_init($api);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_USERPWD, "{$config['user']}:{$config['password']}");
	if(class_exists('CURLFile')){//new method
		curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
	} elseif(defined('CURLOPT_SAFE_UPLOAD')){//may be defined in old method.
		curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
	}
	$file_path = realpath($file::utf82sys($item['path']));
	$fields = [
		'name[]' => $item['title'],
		'tag[]' => implode(",", $item['tags']),
		'desc[]' => $item['desc'],
		'files[]' => curl_file_create($file_path, "image/jpeg", date("YmdHis" . sprintf("%05d", (int)$i)) . ".jpg")
	];
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	$header = array(
		"token:{$config['token']}",
		'X-REQUESTED-WITH:XMLHTTPREQUEST'
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	echo mb_convert_encoding(curl_exec($ch), "GBK", 'UTF-8');
	curl_close($ch);
}
