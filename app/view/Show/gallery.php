<?php
/**
 * @var array            $__info        信息数组
 * @var ULib\Gallery     $__gallery     图集对象
 * @var ULib\CommentData $__CommentData 评论对象
 */
?>
	<div class="single">
		<h2><a href="<?php echo gallery_link($__info['gallery_id'])?>"><?php echo $__info['gallery_title'] ?></a></h2>

		<div class="text-right user">
			<a href="<?php echo user_link($__info['user_name']) ?>" title="<?php echo $__info['user_aliases'] ?>">
				<img class="img-circle" alt="<?php echo $__info['user_aliases'] ?>" src="<?php echo $__info['user_avatar'] ?>">
			</a>
		</div>
		<?php if(empty($__info['gallery_pictures'])): ?>
			<h4 class="text-danger">还没有添加任何图片</h4>
		<?php else: ?>
			<div id="Gallery_slider" class="carousel slide" data-ride="carousel">
				<ol class="carousel-indicators">
					<li data-target="#Gallery_slider" data-slide-to="0" class="active"></li>
					<?php for($i = 1, $l = count($__info['gallery_pictures']); $i < $l; $i++): ?>
						<li data-target="#Gallery_slider" data-slide-to="<?php echo $i ?>"></li>
					<?php endfor; ?>
				</ol>
				<div class="carousel-inner">
					<?php for($i = 0, $l = count($__info['gallery_pictures']); $i < $l; $i++): ?>
						<div class="item<?php echo ($i === 0) ? " active" : ""; ?>">
							<div class="text-center">
								<a href="<?php echo picture_link($__info['gallery_pictures'][$i]['pic_id']) ?>">
									<img src="<?php echo $__info['gallery_pictures'][$i]['pic_display_url'] ?>"
										 alt="<?php echo $__info['gallery_pictures'][$i]['pic_description'] ?>">
								</a>
							</div>
							<div class="carousel-caption">
								<p><?php echo $__info['gallery_pictures'][$i]['pic_description'] ?></p>
							</div>
						</div>
					<?php endfor; ?>
				</div>
				<a class="left carousel-control" href="#Gallery_slider" data-slide="prev">
					<span class="glyphicon glyphicon-chevron-left"></span>
				</a>
				<a class="right carousel-control" href="#Gallery_slider" data-slide="next">
					<span class="glyphicon glyphicon-chevron-right"></span>
				</a>
			</div>
		<?php endif; ?>
		<div class="desc">
			<?php if(strlen($__info['gallery_description'])): ?>
				<p class="desc"><?php echo $__info['gallery_description'] ?></p>
			<?php endif; ?>
			<?php $more = $__gallery->more_info();
			if(!empty($more)):?>
				<div id="Gallery-More-Info" class="more_info">
					<?php echo get_markdown($more); ?>
				</div>
			<?php endif; ?>
			<p class="tags">
				<span
					class="glyphicon glyphicon-tags"><span><?php echo tag($__info['gallery_tags'])?></span></span>
				<?php if(empty($__info['gallery_like_time'])): ?>
					<a href="#" class="like_gallery"><span
							class="glyphicon glyphicon-heart text-danger"><span>喜欢[<?php echo $__info['gallery_like_count'] ?>]</span></span></a>
				<?php else: ?>
					<a href="#" class="like_gallery"><span
							class="glyphicon glyphicon-heart-empty text-danger"><span>取消喜欢[<?php echo $__info['gallery_like_count'] ?>]</span></span></a>
				<?php endif; ?>
				<a href="#Comment" class="glyphicon glyphicon-comment"><span>评论[<?php echo $__info['gallery_comment_count'] ?>]</span></a>
				<?php if(is_login() && $__info['user_id'] == login_user()->getId()): ?>
					<a href="<?php echo get_url("Photo", 'edit_gallery'), '?id=', $__info['gallery_id'] ?>"><span
							class="glyphicon glyphicon-edit"><span>编辑</span></span></a>
				<?php endif; ?>
				<a href="#" class="glyphicon glyphicon-flash follow_gallery">关注图集</a>
				<a href="#" class="share_gallery text-warning"><span class="glyphicon glyphicon-share">分享</span></a>
			</p>
		</div>
		<?php if(isset($__info['previous_and_next'])): ?>
			<ul class="pager left">
				<?php if($__info['previous_and_next']['previous']['id'] > 0 && $__info['previous_and_next']['previous']['id'] < $__info['gallery_id']): ?>
					<li class="previous"><a title="<?php echo $__info['previous_and_next']['previous']['gallery_title']; ?>"
											href="<?php echo gallery_link($__info['previous_and_next']['previous']['id']) ?>">&larr; 上一个图集</a></li>
				<?php endif;
				if($__info['previous_and_next']['next']['id'] > 0 && $__info['previous_and_next']['next']['id'] > $__info['gallery_id']): ?>
					<li class="next"><a title="<?php echo $__info['previous_and_next']['next']['gallery_title']; ?>"
										href="<?php echo gallery_link($__info['previous_and_next']['next']['id']) ?>">下一个图集 &rarr;</a></li>
				<?php endif; ?>
			</ul>
		<?php endif;
		display_comment($__CommentData); ?>
	</div>
	<script>
		$(function () {
			$(".follow_gallery").click(function () {
				$.post("<?php echo get_url("UserApi","follow_gallery")?>", {id:<?php echo $__info['gallery_id']?>}, function (data) {
					if (data['status']) {
						alert("关注成功！");
					} else {
						alert("关注失败:" + data['msg']);
					}
				});
				return false;
			});
			$(".single .like_gallery").click(function () {
				var s_o = this;
				$.post('<?php echo get_url('UserApi','gallery_like')?>', {id:<?php echo $__info['gallery_id']?>}, function (data) {
					if (data['status']) {
						var now_number = $(s_o).find("span span").text();
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
			$(".single .share_gallery").click(function () {
				$.post('<?php echo get_url('UserApi','share_gallery')?>', {id:<?php echo $__info['gallery_id']?>}, function (data) {
					if (data['status']) {
						modal_show("<span class='text-success'>分享成功</span>", "<p>去时间线查看：<a href='<?php echo $tl = time_line_link()?>'><?php echo $tl?></a> </p>");
					} else {
						modal_show("<span class='text-danger'>出错了，分享失败</span>", data['msg']);
					}
				});
				return false;
			});
		});
		views_add('gallery', <?php echo $__info['gallery_id']?>);
	</script>
<?php //var_dump($__info); ?>