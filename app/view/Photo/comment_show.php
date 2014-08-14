<?php
/**
 * @var array $__list
 * @var int[] $__count
 */
?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">评论信息列表</h2>
	</div>

	<div class="panel-body">
		<?php if($__count['max'] > 1 || ($__count['page'] === -1 && $__count['count'] !== 0)): ?>
			<div class="well well-sm clearfix">
				<ul class="pagination" style="display: inline;">
					<?php echo theme()->createNav($__count['page'], $__count['max'], $__count['count'], get_url("Posts", 'comment') . "?page={number}"); ?></ul>
			</div>
		<?php endif;
		if($__count['count'] === 0): ?>
			<h3 class="text-danger">你没有可以显示的评论!</h3>
		<?php elseif($__count['page'] === -1): ?>
			<h3 class="text-danger">当前页面不存在！请返回上一页！</h3>
		<?php
		else: ?>
			<table class="table">
				<?php foreach($__list as &$v):
					/**
					 * @var \ULib\User $user
					 */
					$user = $v['user_object']
					?>
					<tr>
						<td>
							<div>
								<p><a href="<?php echo user_link($user->getName()) ?>"><?php echo $user->getAliases() ?></a> 在：<a
										href="<?php echo $v['info']['link'] ?>"><?php echo $v['info']['title'] ?></a>
									上</p>

								<p>时间：<?php echo $v['comment_time'] ?></p>

								<div class="comment_show_content">
									<?php echo get_markdown($v['comment_content']) ?>
								</div>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>
	</div>
</div>