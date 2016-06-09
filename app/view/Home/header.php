<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo theme()->getTitle() ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link rel="stylesheet" type="text/css" href="<?php echo get_bootstrap_url('css/bootstrap.min.css'); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo get_style('style.css') ?>"/>
	<script src="<?php echo get_js_url('jquery.min.js') ?>" type="text/javascript"></script>
	<script src="<?php echo get_bootstrap_url('js/bootstrap.min.js') ?>" type="text/javascript"></script>
	<script src="<?php echo get_style('home.js') ?>" type="text/javascript"></script>

	<!--[if lt IE 9]>
	<script src="<?php echo get_js_url('ie8-responsive-file-warning.js');?>"></script>
	<script src="<?php echo get_js_url('html5shiv.min.js');?>"></script>
	<script src="<?php echo get_js_url('respond.min.js');?>"></script>
	<![endif]-->
	<?php header_hook(); ?>
	<style>
		@media (min-width: 768px) {
			body {
				background: url('<?php echo get_static_style_url('skin/skin_01.jpg'); ?>') top center fixed;
				background-size: cover;
			}
		}
	</style>
</head>
<body>
<header class="navbar navbar-default navbar-fixed-top">
	<nav class="container" role="navigation">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#home_navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo get_url(); ?>" title="返回 <?php echo site_title() ?> 首页"><?php echo site_title() ?></a>
		</div>
		<div class="collapse navbar-collapse" id="home_navbar">
			<ul class="nav navbar-nav">
				<?php
				echo create_menu_link(get_url(), "首页", NULL, []);
				echo create_menu_link(time_line_link(), "时间线", NULL, [
					'Show',
					'time_line'
				]);
				echo create_menu_link(gallery_list_link(), "图集", NULL, [
					'Show',
					'gallery_list'
				]);
				echo create_menu_link(pictures_list_link(), "图片流", NULL, [
					'Show',
					'pictures'
				]);
				echo create_menu_link(post_list_link(), "文章列表", NULL, [
					'Show',
					'post_list'
				]);
				?>
				<?php if(search_func_is_open()):?>
				<li class="visible-lg-block">
					<form class="navbar-form" role="search" action="<?php echo get_search_link() ?>" method="get">
						<div class="input-group">
							<input type="text" class="form-control" value="<?php
							echo isset($__key_word)?$__key_word:"";
							?>" placeholder="Search" name="q" id="search-term">
							<div class="input-group-btn">
								<button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
							</div>
						</div>
					</form>
				</li>
				<?php endif;?>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<?php if(!is_login()): ?>
					<?php
					echo create_menu_link(get_url("Home", "login"), '<span class="glyphicon glyphicon-log-in"></span> 登录', NULL, [
						"Home",
						"login"
					]);
					echo create_menu_link(get_url("Home", "register"), '注册', NULL, [
						"Home",
						"register"
					]);
					?>
				<?php else: ?>
					<li class="dropdown">
						<a class="glyphicon-user glyphicon" id="dLabel" role="button" data-toggle="dropdown"
						   data-target=""
						   href="<?php echo get_url("User") ?>"><?php echo login_user()->getAliases() ?><?php $un_m_c = get_unread_message_count();
							if($un_m_c > 0){
								echo "<span class=\"badge\">{$un_m_c}</span>";
							} ?><span class="caret"></span>
						</a>
						<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
							<li><a href="<?php echo get_url("Photo") ?>">图片中心</a></li>
							<li><a href="<?php echo get_url("User") ?>">用户中心</a></li>
							<?php if($un_m_c > 0): ?>
								<li><a href="<?php echo get_url("Message", "inbox") ?>"><span class="badge"><?php echo $un_m_c ?></span>未读信息</a></li>
							<?php endif; ?>
							<?php echo create_menu_link(user_link(login_user()->getName()), "个人主页", NULL, [
								'Show',
								'user',
								login_user()->getName()
							]) ?>
							<?php if(login_user()->Permission("Control")): ?>
								<li><a href="<?php echo get_url("Control") ?>">网站控制中心</a></li>
							<?php endif; ?>
							<li><a href="<?php echo get_url("Home", "logout") ?>"><span class="glyphicon glyphicon-log-in"></span> 退出</a></li>
						</ul>
					</li>
				<?php endif; ?>
			</ul>

		</div>
	</nav>
</header>
<div class="container">
	<!--顶部结束-->
