<?php
/**
 * @var array    $__list
 * @var string[] $__pager
 * @var int      $__number
 * @var string   $__tag_name
 */
?>

	<h2>图集标签 - <span><?php echo $__tag_name ?></span>
		<?php if($__number > 1): ?>
			<small>第<?php echo $__number ?>页</small>
		<?php endif; ?>
	</h2>
	<div id="Gallery_list">
		<?php $i = 0;
		$count = count($__list);
		foreach($__list as $v):
			$pic_link = picture_link($v['pic_id']);
			$gallery_link = gallery_link($v['gallery_id']);
			$user = \ULib\User::getUser($v['users_id']);
			?>
			<?php if($i % 2 === 0): ?>
			<div class="row list_box">
		<?php endif; ?>
			<div class="media col-md-6">
				<div class="media-left">
					<a href="<?php echo $pic_link ?>" rel="external">
						<img class="media-object img-thumbnail" src="<?php echo $v['pic_thumbnails_url'] ?>" alt="第<?php echo $v['pic_id'] ?>号图片">
					</a>
				</div>

				<div class="media-body">
					<h3 class="media-heading">
						<a class="glyphicon glyphicon-link" href="<?php echo $gallery_link; ?>"><?php echo $v['gallery_title'] ?></a>
					</h3>
					<?php if(!empty($v['gallery_description'])): ?>
						<p class="desc"><?php echo $v['gallery_description'] ?></p>
					<?php endif; ?>
					<p class="time">创建于：<span class="glyphicon glyphicon-time"></span><span
							class="time"><?php echo explode(" ", $v['gallery_create_time'])[0] ?></span></p>

					<p class="tags"><?php echo tag($v['gallery_tags'], 'label label-success') ?></p>

					<p class="author">作者：
						<a href="<?php echo user_link($user->getName()) ?>">
							<span><?php echo $user->getAliases() ?> (<?php echo $user->getName() ?>)</span>
						</a>
					</p>
				</div>
			</div>
			<?php if($i % 2 === 1 || ($i + 1) === $count): ?>
			</div>
		<?php endif; ?>
			<?php $i++;endforeach; ?>
	</div>
<?php
if($__pager['previous'] != NULL || $__pager['next'] != NULL): ?>
	<ul class="pager">
		<?php if($__pager['previous'] != NULL): ?>
			<li class="previous"><a href="<?php echo $__pager['previous'] ?>">&larr; 上一页</a></li>
		<?php endif;
		if($__pager['next'] != NULL): ?>
			<li class="next"><a href="<?php echo $__pager['next'] ?>">下一页 &rarr;</a></li>
		<?php endif; ?>
	</ul>
<?php endif; ?>