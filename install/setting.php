<?php
c_lib()->load('sql');
$sql = new \CLib\Sql(cfg()->get('sql', 'write'), cfg()->get('sql', 'read'));
c_lib()->add("sql", $sql);
$option_setting = "INSERT INTO `options` (`option_name`,`option_value`,`option_autoload`)
VALUES
('site_title', '{title}', '1'),
('site_desc', '{desc}', '1'),
('site_url', '{url}', '1'),
('admin_email', '{email}', '1'),
('allowed_register', 'yes', '1'),
('allowed_comment', 'yes', '1'),
('default_avatar', 'default', '1'),
('email_notice', 'yes', '1'),
('site_style', 'default', '1'),
('login_captcha', 'no', '1'),
('picture_server', 'local', '1'),
('image_thumbnail_width', '400', '1'),
('image_thumbnail_height', '300', '1'),
('image_hd_width', '1600', '1'),
('image_display_width', '900', '1'),
('comment_one_page', '5', '1'),
('comment_order_desc', 'yes', '1'),
('comment_deep', '8', '1'),
('router_list', 'a:0:{}', '1'),
('site_static_url', '{static_url}', '1'),
('cdn', '', '1');";

$req = req()->_escape();
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$post = $req->post('s');

foreach([
	'title',
	'desc',
	'url',
	'email',
	'static_url'
] as $v){

	if(!isset($post[$v]) || empty($post[$v])){
		die("系统设置有空字段:" . $v);
	}
}

$user = $req->post('u');
lib()->load('UserCheck');
if(!\ULib\UserCheck::CheckUsernameChar($user['name'])){
	die("用户名称检测错误");
}
$user['pwd'] = \ULib\UserCheck::MakeHashChar($user['pwd']);
$user['email'] = $post['email'];
if(!\ULib\UserCheck::CheckEmailChar($user['email'])){
	die("管理员邮箱格式不正确");
}

$option_setting = str_replace([
	'{title}',
	'{desc}',
	'{url}',
	'{email}',
	'{static_url}'
], [
	$post['title'],
	$post['desc'],
	$post['url'],
	$post['email'],
	$post['static_url']
], $option_setting);

$pdo = $sql->getWriter();
$pdo->exec("delete from `options` where `id` > 0");
$pdo->exec("alter table `options` auto_increment=1;");
$pdo->exec($option_setting);

lib()->load('UserRegister', 'UserCheck', 'User');
hook()->add('UserRegister_Captcha', function (){
	//通过钩子去掉用户注册验证码
	return true;
});
hook()->add('MailTemplate_mailSend', function (){
	//去掉发送邮件发送功能
	return false;
});
$ur = new \ULib\UserRegister();
if(($code = $ur->Register($user['email'], $user['pwd'], $user['name'], "244")) <= 0){
	die("注册失败:" . $ur->CodeMsg($code));
}
$pdo->exec("delete from `user_meta` where `id` > 0");
$pdo->exec("alter table `user_meta` auto_increment=1;");
$insert = "INSERT INTO `user_meta` (`users_id`, `meta_key`, `meta_value`) VALUES ('{$code}', 'Permission', 'Control\nMessageSystem\nPosts');";
$pdo->exec($insert);
$pdo->exec("delete from `server` where `id` > 0");
$pdo->exec("alter table `server` auto_increment=1;");
$insert = "INSERT INTO `server` (`name`, `url`, `meta`) VALUES ('local', '{$post['static_url']}images/'," . $pdo->quote(serialize([
		'server_root_path' => _BasePath_."/images/",
		'_Lib' => 'Local',
	])).");";
$pdo->exec($insert);

$session->set('install', [
	'number' => '4',
	'list' => []
]);
$session->set("home_url",$post['url']);
echo "true";