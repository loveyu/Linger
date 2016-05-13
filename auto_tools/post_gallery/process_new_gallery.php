<?php
/**
 * 处理新的图集
 * User: loveyu
 * Date: 2016/5/13
 * Time: 1:30
 */
$config = array_merge([
	'dir_path' => '',
	'url' => '',
], include __DIR__ . "/config.php");
require_once __DIR__ . "/../../sys/config.php";
require_once __DIR__ . "/../../sys/core/lib/interface/SqlInterface.php";
require_once __DIR__ . "/../../sys/core/lib/medoo.php";
$db = new medoo([
	'database_type' => 'mysql',
	'server' => 'localhost',
	'username' => 'root',
	'password' => '123456',
	'charset' => 'utf8',
	'database_name' => 'zzc',
	'option' => [ //PDO选项
		PDO::ATTR_CASE => PDO::CASE_NATURAL,
		PDO::ATTR_TIMEOUT => 5
	],
]);

$user_map = [
	'1' => 'loveyu',
];


$all_count = $db->count("pic", [
	'AND' => [
		'download_lock' => 0,
		'user_id[>]' => 0
	]
]);
$run_count = 0;

while(1){
	$count = count($user_map);
	if($count < 1){
		break;
	}
	$user_id = array_rand($user_map, 1);
	$user_name = $user_map[$user_id];
	$user_id = (int)$user_id;

	$city_ids = $db->select("pic", ['city_id'], [
		'AND' => [
			'user_id' => $user_id,
			'download_lock' => 0
		],
		'ORDER BY city_id'
	]);
	if(empty($city_ids)){
		unset($user_map[$user_id]);
		continue;
	}
	$rd = array_rand($city_ids, 1);
	process_user_city($user_id, $user_name, $city_ids[$rd]['city_id']);
}

function process_user_city($user_id, $user_name, $city_id){
	global $db;
	$download_lock = 0;
	$list = $db->select("pic", "*", [
		'AND' => compact('user_id', 'city_id', 'download_lock')
	]);
	$count_pic_num = function ($item){
		return !empty($item['pic_id1']) + !empty($item['pic_id2']) + !empty($item['pic_id3']);
	};
	$new = true;
	$count = 0;
	$items = [];
	foreach($list as $item){
		if($new){
			$items = [];
			$count = 0;
			$new = false;
		}
		$count += $count_pic_num($item);
		$items[] = $item;
		if($count >= 9){
			process_items($items, $user_name);
			$new = true;
		}
	}
	if(!$new){
		process_items($items, $user_name);
	}
}

function process_items($items, $user_name){
	print_r($items);
	show_count(count($items));
	$desc = [];
	$tags = [];
	$name = "";
	$more = [];
	$pics = [];
	$data_ids = [];
	foreach($items as $v){
		if($name === ""){
			$name = $v['cn_name'] . " {$v['en_name']}";
		}
		$tags[$v['cn_country']] = $v['cn_country'];
		$tags[$v['cn_city']] = $v['cn_city'];
		$tags2 = explode("/", $v['catagory']);
		foreach($tags2 as $k2){
			$k2 = trim($k2);
			if(!empty($k2)){
				$tags[$k2] = $k2;
			}
		}
		$data_ids[] = (int)$v['id'];
		$desc[] = $v['cn_name'];
		$more[] = $v['memo'];
		foreach([1, 2, 3] as $i){
			if($v["pic_id{$i}"] > 0){
				$pics[] = $v["pic_id{$i}"];
			}
		}
	}
	if(!empty($pics)){
		$data = [
			'name' => $name,
			'tags' => implode(",", $tags),
			'pics' => implode(",", $pics),
			'cover_id' => $pics[array_rand($pics)],
			'desc' => implode(", ", $desc),
			'more' => implode("\n", array_map('strip_tags', $more))
		];
		send_data($data, $user_name);
	}
	global $db;
	$db->update("pic", ['download_lock' => 1], ['id' => $data_ids]);
}

function send_data($data, $user_name){
	$output = function ($data){
		echo mb_convert_encoding(print_r($data, true), "GBK", "UTF-8");
	};
	$create_curl = function ($api, $fields = array()) use ($user_name){
		$str_to_pwd_hash = function ($str){
			$arr = mb_split("/(?<!^)(?!$)/u", $str);
			sort($arr);
			return sha1($str . md5(implode('', $arr)));
		};
		$pwd = $str_to_pwd_hash("123456");
		$ch = curl_init("http://pitus.loc/UserApi/" . $api);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, "{$user_name}:{$pwd}");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		$header = array(
			"token:afOtaLDgmzCfhngPGsrkjghgiBiWaxvLAElEIACA",
			'X-REQUESTED-WITH:XMLHTTPREQUEST'
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$rt = curl_exec($ch);
		curl_close($ch);
		return json_decode($rt, true);
	};

	$msg = $create_curl("gallery_add", [
		'gallery_title' => $data['name']
	]);
	if(!is_array($msg)){
		return;
	}
	if(!$msg['status']){
		$output($msg['msg']);
		exit;
	}
	$gallery_id = (int)$msg['content'];

	//添加图片
	$msg = $create_curl("gallery_add_pic", [
		'gallery_id' => $gallery_id,
		'list' => $data['pics']
	]);
	if(!is_array($msg) || !$msg['status']){
		$output($msg['msg']);
		exit;
	}
	//设置封面
	$msg = $create_curl("gallery_set_front_cover", [
		'gallery_id' => $gallery_id,
		'pic_id' => $data['cover_id']
	]);
	if(!is_array($msg) || !$msg['status']){
		$output($msg['msg']);
		exit;
	}

	//更新图集信息
	$update_info = [
		'gallery_id' => $gallery_id,
		'gallery_title' => $data['name'],
		'gallery_description' => $data['desc'],
		'gallery_comment_status' => 1,
		'meta[more_info]' => $data['more'],
	];
	$msg = $create_curl("gallery_edit_info", $update_info);
	if(!is_array($msg) || !$msg['status']){
		$output("gallery_edit_info");
		$output($msg['msg']);
		exit;
	}

	//添加标签
	$msg = $create_curl("gallery_add_tag", [
		'id' => $gallery_id,
		'tag' => $data['tags']
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

function show_count($count){
	global $all_count, $run_count;
	$run_count += $count;
	echo "RUN:{$run_count}/$all_count\n";
}