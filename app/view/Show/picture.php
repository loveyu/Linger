<?php
/**
 * @var array               $__info
 * @var ULib\PictureComment $__CommentData
 */
?>
<div class="single">
	<h2><a href="<?php echo picture_link($__info['pic_id']) ?>">第 <?php echo $__info['pic_id'] ?> 号图片</a></h2>

	<div class="text-right user hidden-print">
		<a href="<?php echo user_link($__info['user_name']) ?>" title="<?php echo $__info['user_aliases'] ?>">
			<img class="img-circle" alt="<?php echo $__info['user_aliases'] ?>" src="<?php echo $__info['user_avatar'] ?>">
		</a>
	</div>
	<div class="image_box">
		<a href="<?php echo $__info['pic_hd_url'] ?>">
			<img src="<?php echo $__info['pic_display_url'] ?>" title="<?php echo $__info['pic_description'] ?>" class="img-thumbnail img-responsive"
				 alt="第 <?php echo $__info['pic_id'] ?> 号图片"/>
		</a>
	</div>
	<div class="desc">
		<?php if(strlen($__info['pic_description'])): ?>
			<p class="desc"><?php echo $__info['pic_description'] ?></p>
		<?php endif; ?>
		<p class="tags">
			<span
				class="glyphicon glyphicon-tags"><span>标签：<?php echo count($__info['pic_tags']) > 0 ? "<span class='label label-info'>" . implode("</span><span class='label label-info'>", $__info['pic_tags']) . "</span>" : "无" ?></span></span>
			<?php if(empty($__info['pic_like_time'])): ?>
				<a href="#" class="like_picture glyphicon glyphicon-heart text-danger"><span>喜欢[<?php echo $__info['pic_like_count'] ?>]</span></a>
			<?php else: ?>
				<a href="#" class="like_picture"><span
						class="glyphicon glyphicon-heart-empty text-danger"><span>取消喜欢[<?php echo $__info['pic_like_count'] ?>]</span></span></a>
			<?php endif; ?>
			<a href="#Comment" class="glyphicon glyphicon-comment hidden-print"><span>评论[<?php echo $__info['pic_comment_count'] ?>]</span></a>
			<?php if(is_login() && $__info['user_id'] == login_user()->getId()): ?>
				<a class="hidden-print" href="<?php echo get_url("Photo", 'edit_pic'), '?id=', $__info['pic_id'] ?>"><span
						class="glyphicon glyphicon-edit"><span>编辑</span></span></a>
			<?php endif; ?>
			<a href="#" class="share_picture text-warning hidden-print"><span class="glyphicon glyphicon-share">分享</span></a>
			<?php if($__info['pic_hd_url'] != $__info['pic_display_url']): ?>
				<a class="label label-primary hidden-print" href="<?php echo $__info['pic_hd_url'] ?>">高清图</a>
			<?php endif;
			if($__info['pic_url'] != $__info['pic_hd_url']): ?>
				<a class="label label-primary hidden-print" href="<?php echo $__info['pic_url'] ?>">原图</a>
			<?php endif; ?>
			<a class="visible-print glyphicon glyphicon-user" href="<?php echo user_link($__info['user_name']) ?>"><?php echo $__info['user_aliases'] ?></a>
		</p>
	</div>
	<ul class="pager left">
		<?php if($__info['previous_id'] < $__info['pic_id'] && $__info['previous_id'] != 0): ?>
			<li class="previous">
				<a title="第 <?php echo $__info['previous_id'] ?> 号图片" href="<?php echo picture_link($__info['previous_id']) ?>">&larr;上一张</a></li>
		<?php endif;
		if($__info['next_id'] > $__info['pic_id']): ?>
			<li class="next"><a title="第 <?php echo $__info['next_id'] ?> 号图片" href="<?php echo picture_link($__info['next_id']) ?>">下一张&rarr;</a>
			</li>
		<?php endif; ?>
	</ul>
	<?php display_comment($__CommentData); ?>
</div>
<script>
	$(function () {
		$(".single .like_picture").click(function () {
			var s_o = this;
			$.post('<?php echo get_url('UserApi','picture_like')?>', {id:<?php echo $__info['pic_id']?>}, function (data) {
				if (data['status']) {
					var now_number = $(s_o).find("span").text();
					now_number = now_number.match(/\[([\d]+)\]/);
					if (now_number.hasOwnProperty('1')) {
						now_number = now_number[1];
					} else {
						now_number = 0;
					}
					if ($(s_o).find("span:first").hasClass('glyphicon-heart-empty')) {
						//改为喜欢
						now_number = now_number > 0 ? now_number - 1 : 0;
						$(s_o).find("span:first").removeClass('glyphicon-heart-empty').addClass('glyphicon-heart').html("<span>喜欢[" + now_number + "]</span>");
					} else {
						//改为不喜欢
						++now_number;
						$(s_o).find("span:first").removeClass('glyphicon-heart').addClass('glyphicon-heart-empty').html("<span>取消喜欢[" + now_number + "]</span>");
					}
				} else {
					alert(data['msg']);
				}
			});
			return false;
		});
		$(".single .share_picture").click(function () {
			$.post('<?php echo get_url('UserApi','share_picture')?>', {id:<?php echo $__info['pic_id']?>}, function (data) {
				if (data['status']) {
					modal_show("<span class='text-success'>分享成功</span>", "<p>去时间线查看：<a href='<?php echo $tl = time_line_link()?>'><?php echo $tl?></a> </p>");
				} else {
					modal_show("<span class='text-danger'>出错了，分享失败</span>", data['msg']);
				}
			});
			return false;
		});
	});
	views_add('pictures', <?php echo $__info['pic_id']?>);
</script>