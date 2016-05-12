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
	'database_name' => 'pic',
	'option' => [ //PDO选项
		PDO::ATTR_CASE => PDO::CASE_NATURAL,
		PDO::ATTR_TIMEOUT => 5
	],
]);
c_lib()->load('file');
$file = new \CLib\File();
$i = 1;
do{
	$ids = $db->select("pic_tbl", "*", array(
		'download_lock' => 0,
		'ORDER' => 'id ASC',
		'LIMIT' => [0, 100]
	));
	if(count($ids) == 0){
		echo $db->last_query();
		break;
	}
	foreach($ids as $v){
		$config['user'] = $v['user_name'];
		$config['password'] = $str_to_pwd_hash("123456");

		foreach([1, 2, 3] as $index_2){
			echo "{$i}:";
			$i++;
			if(!empty($v["pic{$index_2}_download"]) && is_file($v["pic{$index_2}_download"]) && empty($v['pic_id' . $index_2])){
				$item = array(
					'title' => $v['cn_name'] . " " . $v['en_name'],
					'desc' => "",
					'tags' => explode("/", $v['catagory']),
					'path' => $v["pic{$index_2}_download"],
				);
				$pic_id = run($config, $item, $i);
				echo "{$index_2}=>{$pic_id};";
				$db->update("qyer_attractions_tbl", [
					'pic_id' . $index_2 => $pic_id,
				], ['id' => $v['id']]);
				//exit;
			}
			echo "\n";
		}

		$db->update("pic_tbl", [
			'download_lock' => 1,
		], ['id' => $v['id']]);
	}
} while(1);

function run($config, $item, $i){
	global $file;
	$api = $config['url'] . "UserApi/picture_upload";
	$ch = curl_init($api);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
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
	$rt = curl_exec($ch);
	$data = json_decode($rt, true);
	curl_close($ch);
	//echo mb_convert_encoding(print_r($data, true), "GBK", "UTF-8");
	return isset($data['content']['list'][0]) ? (int)$data['content']['list'][0] : 0;
}