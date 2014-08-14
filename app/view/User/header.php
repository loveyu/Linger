<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo theme()->getTitle(); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link rel="stylesheet" type="text/css" href="<?php echo get_bootstrap_url('css/bootstrap.min.css'); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo get_style('user.css'); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo get_js_url('pnotify/jquery.pnotify.default.css'); ?>"/>
	<script src="<?php echo get_js_url('jquery-1.11.0.js') ?>" type="text/javascript"></script>
	<script src="<?php echo get_bootstrap_url('js/bootstrap.min.js') ?>" type="text/javascript"></script>
	<script src="<?php echo get_js_url('pnotify/jquery.pnotify.js'); ?>"></script>
	<script src="<?php echo get_style('home.js'); ?>"></script>
	<script src="<?php echo get_style('user.js'); ?>"></script>
	<script src="<?php echo get_js_url('jquery.form.js'); ?>"></script>

	<!--[if lt IE 9]>
	<script src="<?php echo get_js_url('ie8-responsive-file-warning.js');?>"></script>
	<script src="<?php echo get_js_url('html5shiv.min.js');?>"></script>
	<script src="<?php echo get_js_url('respond.min.js');?>"></script>
	<![endif]-->
	<script>
		$.pnotify.defaults.styling = "bootstrap3";
	</script>
	<?php header_hook(); ?>
</head>
<body>
<header class="navbar navbar-default">
	<nav class="container" role="navigation">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo site_url(); ?>" title="返回 <?php echo site_title() ?> 首页"><?php echo site_title() ?></a>
		</div>
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav">
				<li><a href="<?php echo site_url(); ?>">首页</a></li>
				<li><a href="<?php echo time_line_link(); ?>">时间线</a></li>
				<li><a href="<?php echo gallery_list_link(); ?>">图集</a></li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
					<a id="dLabel" role="button" data-toggle="dropdown" data-target="#" href="<?php echo get_url("User") ?>">
						<?php echo login_user()->getAliases(); ?>
						<span class="badge"><?php echo $un_m_c = get_unread_message_count() ?></span>
						<span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
						<?php
						echo create_menu_link(get_url('Photo'), '图片中心', null, ['Photo']);
						echo create_menu_link(get_url('User'), '用户中心', null, ['User']);

						if($un_m_c > 0){
							echo create_menu_link(get_url('Message', 'inbox'), "<span class=\"badge\">{$un_m_c}</span>收信箱", NULL, [
								'Message',
								'inbox'
							]);
						}
						?>
						<li><a href="<?php echo user_link(login_user()->getName()) ?>">个人主页</a></li>
						<?php if(login_user()->Permission("Control")): ?>
							<li><a href="<?php echo get_url("Control") ?>">网站控制中心</a></li>
						<?php endif; ?>
						<li><a href="<?php echo get_url("Home", "logout") ?>">退出</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</nav>
</header>
<div class="container">
	<ol class="breadcrumb">
		<?php echo theme()->getBreadcrumb(); ?>
	</ol>
	<div class="row">
		<div class="col-md-2 hidden-print">
			<ul class="user_menu">
				<?php echo theme()->get_user_menu(); ?>
			</ul>
		</div>
		<div class="col-md-10">
			<!--顶部结束-->
