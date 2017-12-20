<?php
set_time_limit(0);
if(is_file(__DIR__."/../config/install.lock")){
	die("Please try again to delete the file /config/install.lock.");
}
require_once(__DIR__."/../sys/config.php");
cfg()->load(__DIR__.'/../config/all.php'); //加载其他配置文件
l_h("system.php", "theme.php");
c_lib()->load('session');
$session = new \CLib\Session();
$info = [
	'number' => 1,
	'list' => []
];
$s = $session->get('install');
if(isset($s['number'])){
	$info = $s;
}
$install_path = __DIR__."/../install/";
$setup = isset($_GET['setup']) ? $_GET['setup'] : "1";
if ($setup == "import_sql" && $info['number'] == '2'){
	include("{$install_path}import_sql.php");
	exit;
} else if ($setup == "setting" && $info['number'] == '3'){
	include("{$install_path}setting.php");
	exit;
}else if ($setup == "system" && $info['number'] == '4'){
	include("{$install_path}system.php");
	exit;
}else
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>程序安装</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link rel="stylesheet" type="text/css" href="<?php echo get_bootstrap_url('css/bootstrap.min.css'); ?>"/>
	<script src="<?php echo get_js_url('jquery.js') ?>" type="text/javascript"></script>
	<script src="<?php echo get_bootstrap_url('js/bootstrap.min.js') ?>" type="text/javascript"></script>

	<!--[if lt IE 9]>
	<script src="<?php echo get_js_url('ie8-responsive-file-warning.js');?>"></script>
	<script src="<?php echo get_js_url('html5shiv.min.js');?>"></script>
	<script src="<?php echo get_js_url('respond.min.js');?>"></script>
	<![endif]-->
</head>
<body>
<div class="container">
	<?php
	switch($info['number']){
		case 1:
			include("{$install_path}/setup1.php");
			break;
		case 2:
			if($setup == "2"){
				include("{$install_path}/setup2.php");
			} else{
				include("{$install_path}/setup1.php");
			}
			break;
		case 3:
			if($setup == "3"){
				include("{$install_path}/setup3.php");
			} else{
				include("{$install_path}/setup1.php");
			}
			break;
		case 4:
			if($setup == "4"){
				include("{$install_path}/setup4.php");
			} else{
				include("{$install_path}/setup1.php");
			}
			break;
	} ?>
</div>
</body>
</html>