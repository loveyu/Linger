<?php
/**
 * @var array  $__list
 * @var int[]  $__count
 * @var string $__type
 */
?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">我的评论</h2>
	</div>

	<div class="panel-body">
		<?php if($__count['max'] > 1 || ($__count['page'] === -1 && $__count['count'] !== 0)): ?>
			<div class="well well-sm clearfix">
				<ul class="pagination" style="display: inline;">
					<?php echo theme()->createNav($__count['page'], $__count['max'], $__count['count'], get_url("Follow", 'comment', $__type) . "?page={number}"); ?></ul>
			</div>
		<?php endif;
		if($__count['count'] === 0): ?>
			<h3 class="text-danger">你没有可以显示的评论!</h3>
		<?php elseif($__count['page'] === -1): ?>
			<h3 class="text-danger">当前页面不存在！请返回上一页！</h3>
		<?php
		else: ?>
			<?php foreach($__list as $v): ?>
				<div class="well well-sm">
					<p><?php echo $v['comment_time'] ?> 在：<a href="<?php echo $v['info']['link'] ?>"><?php echo $v['info']['title'] ?></a>
						上</p>

					<div class="comment_show_content">
						<?php echo get_markdown($v['comment_content']) ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>