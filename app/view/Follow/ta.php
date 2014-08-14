<?php
/**
 * @var array $__list
 * @var int[] $__count
 */
?>
<div class="panel panel-info">
	<div class="panel-heading">
		<h2 class="panel-title">我的粉丝</h2>
	</div>
	<div class="panel-body">
		<?php if($__count['max'] > 1 || ($__count['page'] === -1 && $__count['count'] !== 0)): ?>
			<div class="well well-sm clearfix">
				<ul class="pagination" style="display: inline;"><?php echo theme()->createNav($__count['page'], $__count['max'], $__count['count'], get_url("Follow", 'ta') . "?page={number}"); ?></ul>
			</div>
		<?php endif;
		if($__count['count'] === 0): ?>
			<h3 class="text-danger">还没有用户关注你哦！</h3>
		<?php
		elseif($__count['page'] === -1):?>
			<h3 class="text-danger">当前页面不存在！请返回上一页！</h3>
		<?php
		else: ?>
			<table class="table table-striped table-hover">
				<tbody>
				<?php foreach($__list as &$v):
					/**
					 * @var \ULib\User $user
					 */
					$user = $v['user'];
					$follow = $v['follow']; ?>
					<tr>
						<td style="width: 40px">
							<img src="<?php echo $user->getAvatar(40) ?>" width="40" height="40">
						</td>
						<td>
							<div>
								<p>用户：<a rel="external" href="<?php echo user_link($user->getName()) ?>"><?php echo $user->getAliases() ?></a> (<?php echo $user->getName() ?>)</p>

								<p>关注时间：<span><?php echo $follow['follow_time'] ?></span></p>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>