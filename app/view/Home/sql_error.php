<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>数据库连接出错</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link rel="stylesheet" type="text/css" href="<?php echo get_file_url('bootstrap/css/bootstrap.min.css'); ?>"/>
</head>
<body class="container">
<h1 class="text-danger">数据库连接出错：</h1>

<p class="well well-lg text-warning">错误信息：<br><?php echo $__msg ?></p>
<?php if($__email): ?>
	<p class="well text-danger">如果你发现该错误，希望你能通知管理员。<br> Email: <a href="mailto:<?php echo $__email ?>">
			<strong class="text-primary"><?php echo $__email ?></strong></a></p>
<?php endif; ?>
</body>
</html>