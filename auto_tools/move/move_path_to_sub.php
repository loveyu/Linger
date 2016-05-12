<?php
/**
 * User: loveyu
 * Date: 2016/5/12
 * Time: 20:15
 */
$path = realpath(__DIR__ . "/../../../../Data/linger_traver_image") . "/thumbnail/";
$handler = opendir($path . "2016/0512/");
$list = [];
while(($name = readdir($handler)) !== false){
	if($name == "." || $name == ".." || !is_file("{$path}/2016/0512/{$name}")){
		continue;
	}
	$sub = substr(md5($name), 0, 2);
	if(!is_dir("{$path}/2016/0512/{$sub}/")){
		mkdir("{$path}/2016/0512/{$sub}/", true);
	}
	$list[$path . "2016/0512/{$name}"] = $path . "2016/0512/{$sub}/{$name}";
}
closedir($handler);
$count = count($list);
$i = 1;
foreach($list as $old => $new){
	echo "{$i}/{$count}" . $old . "\n";
	if(!rename($old, $new)){
		die("Error.\n");
	}
	$i++;
}