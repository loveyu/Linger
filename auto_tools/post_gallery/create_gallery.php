<?php
/**
 * 生成图集
 * User: loveyu
 * Date: 2016/4/16
 * Time: 3:12
 */
$config = require "config.php";
$output = function ($data){
	echo mb_convert_encoding(print_r($data, true), "GBK", "UTF-8");
};
$create_curl = function ($api, $fields = array(), $echo = false) use ($config, $output){
	$ch = curl_init($config['url'] . "UserApi/" . $api);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_USERPWD, "{$config['user']}:{$config['password']}");
	if($echo){
		$output($fields);
	}
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	$header = array(
		"token:{$config['token']}",
		'X-REQUESTED-WITH:XMLHTTPREQUEST'
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$rt = curl_exec($ch);
	curl_close($ch);
	if($echo){
		$output($rt);
	}
	return json_decode($rt, true);
};
$content = $config['content2'];
$max_length = mb_strlen($content);
$user_pic_ids = $create_curl("get_user_pic_ids")['content'];
for($__i = 1; $__i <= 2000; $__i++){
	echo "Run:{$__i}/2000\n";
	//创建图集
	$msg = $create_curl("gallery_add", [
		'gallery_title' => mb_substr($content, rand(0, $max_length - 100), rand(8, 20))
	]);
	if(!is_array($msg)){
		continue;
	}
	if(!$msg['status']){
		$output($msg['msg']);
		exit;
	}
	$gallery_id = (int)$msg['content'];

	//添加图片
	$rand_pic_ids = get_array_values_by_keys($user_pic_ids, array_rand($user_pic_ids, rand(8, 22)));
	$msg = $create_curl("gallery_add_pic", [
		'gallery_id' => $gallery_id,
		'list' => implode(",", $rand_pic_ids)
	]);
	if(!is_array($msg) || !$msg['status']){
		$output($msg['msg']);
		exit;
	}
	//设置封面
	$cover = $rand_pic_ids[rand(0, 5)];
	$msg = $create_curl("gallery_set_front_cover", [
		'gallery_id' => $gallery_id,
		'pic_id' => $cover
	]);
	if(!is_array($msg) || !$msg['status']){
		$output($msg['msg']);
		exit;
	}

	//更新图集信息
	$g1 = mb_substr($content, rand(0, $max_length - 100), rand(8, 20));
	$g2 = mb_substr($content, rand(0, $max_length - 100), rand(15, 50));
	$g3 = mb_substr($content, rand(0, $max_length - 150), rand(25, 150));
	$update_info = [
		'gallery_id' => $gallery_id,
		'gallery_title' => $g1,
		'gallery_description' => $g2,
		'gallery_comment_status' => 1,
		'meta[more_info]' => $g3,
	];
	$msg = $create_curl("gallery_edit_info", $update_info);
	if(!is_array($msg) || !$msg['status']){
		$output("gallery_edit_info");
		$output($msg['msg']);
		exit;
	}

	//添加标签
	$tags = [];
	$tag_length = rand(1, 8);
	for($i = 0; $i < $tag_length; $i++){
		$tags[] = mb_substr($content, rand(0, $max_length - 5), rand(1, 5));
	}
	$tags = array_unique($tags);
	$msg = $create_curl("gallery_add_tag", [
		'id' => $gallery_id,
		'tag' => implode(",", $tags)
	]);
	if(!is_array($msg) || !$msg['status']){
		$output("gallery_add_tag");
		$output($msg['msg']);
		exit;
	}

	//发布
	$msg = $create_curl("gallery_set_public", [
		'id' => $gallery_id
	]);
	if(!is_array($msg) || !$msg['status']){
		$output("gallery_set_public");
		$output($msg['msg']);
		exit;
	}
}

function get_array_values_by_keys($arr, $keys){
	$rt = [];
	foreach($keys as $v){
		$rt[] = $arr[$v];
	}
	return $rt;
}
