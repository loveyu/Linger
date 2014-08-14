<?php
$config = file_get_contents(_SysPath_ . "/config.php");
header("Content-Type: text/plain; charset=utf-8");
if(isset($_POST['_Debug_'])){
	$config = preg_replace("/define\\('_Debug_',([a-z0-9_ ]+?)\\);/", "define('_Debug_', " . var_export($_POST['_Debug_'] == 1, true) . ");", $config);
}
if(isset($_POST['COOKIE_PREFIX'])){
	$config = preg_replace("/define\\('COOKIE_PREFIX',([\\s\\S]+?)\\);/", "define('COOKIE_PREFIX', " . var_export($_POST['COOKIE_PREFIX'], true) . ");", $config);
}
if(isset($_POST['COOKIE_KEY']) && strlen($_POST['COOKIE_KEY']) > 5){
	$config = preg_replace("/define\\('COOKIE_KEY',([\\s\\S]+?)\\);/", "define('COOKIE_KEY', " . var_export($_POST['COOKIE_KEY'], true) . ");", $config);
} else{
	die("Cookie密钥不合法");
}

file_put_contents(_SysPath_ . "/config.php", $config);

if(!rename(_BasePath_ . "/install.php", dirname(_BasePath_) . "/install/install.php")){
	die("文件移动失败");
}

if(isset($_POST['INSTALL_REMOVE']) && $_POST['INSTALL_REMOVE'] == "1"){
	c_lib()->load('file');
	$f = new \CLib\File();
	$f->path_remove(dirname(_BasePath_) . "/install", true);
}
file_put_contents(dirname(_BasePath_) . "/config/install.lock", "");
echo "true";