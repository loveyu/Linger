<?php
/**
 * @var array        $__pic_list     图片信息列表
 * @var array        $__gallery_list 图集
 * @var \ULib\User[] $__user_list    新用户
 */
$user = login_user();
?>
<div class="row">
	<div class="col-sm-7">
		<div id="H-Title" data="<?php echo get_static_style_url("skin/"); ?>">
			<h1><?php echo site_title() ?></h1>
			<h4><?php echo site_desc() ?></h4>
		</div>
	</div>
	<div class="col-sm-5">
		<?php if(!is_object($user)): ?>
			<div id="H-Login">
				<p class="join_us">
					现在<a class="label label-success home_modal_link" href="<?php echo get_url("Home", "register") ?>">注册</a>或<a
						class=" label label-success home_modal_link"
						href="<?php echo get_url("Home", "login") ?>">登录</a>，和我们一起享受图片分享的喜悦！
				</p>

				<div class="new_user main_box hidden-print hidden-sm hidden-xs">
					<h5 class="text-warning">最新注册的用户</h5>

					<div class="avatar clearfix">
						<?php foreach($__user_list as &$v): ?>
							<a href="<?php echo user_link($v->getName()) ?>">
								<img src="<?php echo $v->getAvatar(64) ?>" width="64" height="64" alt="<?php echo $v->getName() ?>"
									 title="用户 <?php echo $v->getAliases() ?>">
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div id="H-User">
				<div class="avatar hidden-print hidden-xs text-center">
					<a href="<?php echo get_url("User") ?>" title="用户中心"><img class="img-rounded" src="<?php echo $user->getAvatar(120) ?>"
																			  alt="<?php echo $user->getName() ?>"></a>
				</div>
				<div class="action">
					<a class="label label-primary" href="<?php echo get_url("Photo") ?>">图片中心</a>
					<a class="label label-success" href="<?php echo get_url("Photo", "add_pic") ?>">上传图片</a>
					<a class="label label-info" href="<?php echo get_url("Photo", "add_gallery") ?>">创建图集</a>
					<a class="label label-success" href="<?php echo get_url("Message", "inbox") ?>">消息中心</a>
					<a class="label label-info" href="<?php echo user_gallery_list_link($user->getName()) ?>">我的图集</a>
					<a class="label label-primary" href="<?php echo time_line_link() ?>">在时间线</a>
				</div>
				<form class="talk hidden-print hidden-sm hidden-xs" action="<?php echo get_url("UserApi", "share_talk") ?>" method="post">
					<div class="form-group">
						<label class="control-label sr-only" for="TalkShare">分享：</label>
						<textarea class="form-control" name="content" rows="3" id="TalkShare" placeholder="分享点什么，Ctrl+Entry发布"></textarea>

						<div id="ShareTalk_Help" class="alert alert-success alert-dismissable" style="display: none">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<p></p>
						</div>
					</div>
				</form>
			</div>
		<?php endif; ?>
	</div>
</div>
<div id="H-Gallery">
	<div class="row">
		<?php $i = 0;
		foreach($__gallery_list as $v): ++$i; ?>
			<div class="col-sm-3">
				<div class="thumbnail">
					<a href="<?php echo $link = gallery_link($v['gallery_id']) ?>">
						<img src="<?php echo $v['pic_thumbnails_url'] ?>"
							 alt="<?php echo $v['pic_description'] ?>"></a>
					<h4><a href="<?php echo $link ?>"><?php echo $v['gallery_title'] ?></a></h4>

					<p><?php echo mb_substr($v['gallery_description'], 0, 50, "UTF-8") ?></p>
				</div>
			</div>
		<?php if($i%4==0){echo "<div class='clearfix'></div>";} endforeach; ?>
	</div>
</div>
<div id="H-Pic" class="main_box hidden-print">
	<h5>新的图片</h5>

	<div class="picture  clearfix">
		<?php $i = 0;
		foreach($__pic_list as $v):++$i; ?>
			<a class="<?php if($i > 6){
				echo "hidden-xs ";
			}
			if($i > 8){
				echo "hidden-sm";
			} ?>" href="<?php echo picture_link($v['pic_id']) ?>"
			   title="<?php echo mb_substr($v['pic_description'], 0, 20, "UTF-8") ?>">
				<img src="<?php echo $v['pic_thumbnails_url'] ?>"
					 alt="<?php echo $v['pic_description'] ?>"></a>
		<?php endforeach; ?>
	</div>
</div>
<div id="H-Link" class="main_box hidden-print">
	<h5>图库链接</h5>

	<p>
		<a href="http://weheartit.com/">We Heart It</a>
		<a href="http://www.behance.net/">Behance</a>
		<a href="http://vi.sualize.us/">VisualizeUs</a>
		<a href="http://www.socwall.com/">Social Wallpapering</a>
		<a href="http://www.deviantart.com/">DeviantART</a>
		<a href="http://www.desktopography.net/">Desktopography</a>
	</p>
</div>
<script>
	$(function () {
		new HomeAction();
	});
</script>