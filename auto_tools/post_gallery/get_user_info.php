<?php
/**
 * 读取用户信息
 * User: loveyu
 * Date: 2016/4/15
 * Time: 1:00
 */
$config = array_merge([
	'user' => '',
	'password' => '',
	'token' => '',
	'url' => ''
], include __DIR__ . "/config.php");
print_r($config);
$user_info_api = $config['url'] . "UserApi/user_info";
$ch = curl_init($user_info_api);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "{$config['user']}:{$config['password']}");
$header = array(
	"token:{$config['token']}",
	'X-REQUESTED-WITH:XMLHTTPREQUEST'
);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
echo curl_exec($ch);
curl_close($ch);