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
	$desc = mb_substr($config['content'], rand(0, $content_length - 200), rand(15, 200));
	$tags = [];
	$tag_length = rand(0, 8);
	for($i = 0; $i < $tag_length; $i++){
		$tags[] = mb_substr($config['content'], rand(0, $content_length - 5), rand(1, 5));
	}
	$tags = array_unique($tags);
	$data[] = compact('path', 'title', 'desc', 'tags');
}

