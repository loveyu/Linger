<!DOCTYPE html>
<html>
<head>
	<title>控制中心 - <?php echo site_title(); ?></title>
	<!--<script src="--><?php //echo get_file_url("js/md5_sha1.js"); ?><!--"></script>-->
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link rel="stylesheet" type="text/css" href="<?php echo get_bootstrap_url('css/bootstrap.min.css'); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo get_style('control.css'); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo get_js_url('pnotify/jquery.pnotify.default.css'); ?>"/>
	<script src="<?php echo get_js_url('jquery-1.11.0.js') ?>" type="text/javascript"></script>
	<script src="<?php echo get_bootstrap_url('js/bootstrap.min.js') ?>" type="text/javascript"></script>
	<script src="<?php echo get_style('home.js'); ?>"></script>
	<script src="<?php echo get_style('control.js'); ?>"></script>
	<script src="<?php echo get_style('pagination.js'); ?>"></script>
	<script src="<?php echo get_js_url('pnotify/jquery.pnotify.js'); ?>"></script>
	<script src="<?php echo get_js_url('jquery.form.js'); ?>"></script>

	<!--[if lt IE 9]>
	<script src="<?php echo get_js_url('ie8-responsive-file-warning.js');?>"></script>
	<script src="<?php echo get_js_url('html5shiv.min.js');?>"></script>
	<script src="<?php echo get_js_url('respond.min.js');?>"></script>
	<![endif]-->
	<script>
		var API_URL = '<?php echo get_url("UserControlApi")?>';
		var SITE_URL = '<?php echo site_url();?>';
	</script>

</head>
<body>
<div class="container-fluid clearfix" id="warp">
	<div class="menu_left">

	</div>
	<div class="content_right">
		<div id="page_content_load">

		</div>
	</div>
</div>
<script>
	function fix_width_height() {
		var width = 120;
		$("#warp").height($(window).height());
		$("#warp .content_right").width($("#warp").width() - width - 20);
		$("#warp .menu_left").width(width);
	}
	$(window).resize(fix_width_height);
	$(function () {
		fix_width_height();
		$.ajaxSetup({cache: true});
		load_menu("#warp .menu_left", API_URL + '/menu_list');
		$.pnotify.defaults.styling = "bootstrap3";
	})
</script>
</body>
</html>