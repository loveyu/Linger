<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>系统初始化错误，请重新安装程序</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link rel="stylesheet" type="text/css" href="<?php echo get_file_url('bootstrap/css/bootstrap.min.css'); ?>"/>
</head>
<body class="container">
<h1 class="text-danger">系统初始化错误</h1>

<p class="well well-lg text-warning">请重新安装系统，或检测数据库配置，<code>install.php</code>文件可能在<code>install</code>目录下，请先移动，如被删除请下载新版本。<a href="<?php echo get_file_url("install.php") ?>" class="btn btn-primary">前往安装界面</a></p>
</body>
</html>