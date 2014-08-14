<?php
/**
 * @var \ULib\User $__user
 * @var array      $__count
 */
?>
<h1><?php echo $__user->getAliases(), " - ", $__user->getName() ?><?php if(is_login() && $__user->getId() === login_user()->getId()): ?>
		<a class="hidden-print" href="<?php echo get_url("User", "edit_info") ?>">
			<small class="glyphicon glyphicon-edit">编辑</small>
		</a>
	<?php endif; ?></h1>
<div class="row user_profile">
	<div class="col-md-3">
		<div class="text-center user_description">
			<img src="<?php echo $__user->getAvatar(200) ?>" alt="<?php echo $__user->getAliases(); ?>" class="img-circle">
		</div>

		<p class="well-sm well">个性名称：<span class="text-info"><?php echo $__user->getAliases() ?></span></p>
		<?php $url = $__user->getUrl();
		if(!empty($url)): ?>
			<p class="well-sm well" style="overflow: hidden">个人主页：<br><a href="<?php echo $url ?>"><?php echo $url ?></a></p>
		<?php endif; ?>
		<p class="well-sm well">注册时间：<span class="text-info"><?php echo convert_time($__user->getRegisteredTime()) ?></span></p>

		<p class="well-sm well">
			<a href="<?php echo user_gallery_list_link($__user->getName()) ?>">
				图集数量：<span class="text-info"><?php echo isset($__count['gallery_count']) ? $__count['gallery_count'] . " 个" : "无"; ?></span></a><br>
			上传图片：<span class="text-info"><?php echo isset($__count['picture_count']) ? $__count['picture_count'] . " 张" : "无"; ?></span><br>
			Ta的评论：<span class="text-info"><?php echo isset($__count['comment_count']) ? $__count['comment_count'] . " 条" : "无"; ?></span><br>
			Ta关注：<span class="text-info"><?php echo isset($__count['user_follow_count']) ? $__count['user_follow_count'] . " 人" : "无"; ?></span><br>
			Ta的粉丝：<span class="text-info"><?php echo isset($__count['user_fans_count']) ? $__count['user_fans_count'] . " 人" : "无"; ?></span><br>
			关注的图集：<span
				class="text-info"><?php echo isset($__count['follow_gallery_count']) ? $__count['follow_gallery_count'] . " 个" : "无"; ?></span><br>
		</p>
	</div>
	<div class="col-md-9">
		<p class="text-right">
			<button class="btn btn-success hidden-print" onclick="follow_user('<?php echo $__user->getId() ?>',this);">关注Ta</button>
		</p>
		<?php $pm = $__user->profile_message();
		if(!empty($pm)):?>
			<div class="profile"><?php echo get_markdown($pm); ?></div>
		<?php endif;
		$msg = "";
		$i = 1;
		foreach(convert_video_code($__user->profile_video()) as $v){
			$rt = convert_video_param($v['name'], $v['param']);
			if(filter_var($rt, FILTER_VALIDATE_URL)){
				$msg .= "<embed class='profile_video' id='Profile_video_{$i}' src='{$rt}' />\n";
				$i++;
			}
		}
		if($i > 1){
			?>
			<h2><?php echo $__user->getAliases() ?> 的视频展示</h2>
		<?php echo $msg; ?>
			<script type="text/javascript">
				var video = function () {
					$('.profile_video').each(function (i, e) {
						$e = $(e);
						$e.height($e.width() * 0.65);
					});
				};
				$(video);
				$(window).resize(video);
			</script>
		<?php
		}
		if(empty($pm) && empty($msg)):
			?>
			<h2>这家伙很懒，没有留下一丁点详细信息！</h2>
		<?php endif; ?>
	</div>
</div>
<script>
	/* 关注用户 */
	function follow_user(id, elem) {
		if (!IS_LOGIN) {
			location.href = '<?php echo redirect_to_login(true);?>';
			return;
		}
		id = +id;
		if (id > 0) {
			$.post(SITE_URL + "UserApi/follow_user", {id: id}, function (data) {
				if (data['status']) {
					$(elem).html("已关注").removeClass("btn-success").addClass("btn-primary");
				} else {
					alert("关注失败:" + data['msg']);
				}
			});
		} else {
			alert("用户关注有误");
		}
	}
</script>