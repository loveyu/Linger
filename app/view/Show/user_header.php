<?php
/**
 * @var \ULib\User $__user
 */
?>
<div class="media" id="User-header">
	<a href="<?php echo $link = user_link($__user->getName()); ?>" class="pull-left">
		<img class="media-object img-circle" src="<?php echo $__user->getAvatar(100) ?>" alt="<?php echo $__user->getName() ?>"
			 title="<?php echo $__user->getAliases() ?>">
	</a>

	<div class="media-body">
		<h4>名称：<a href="<?php echo $link ?>"><?php echo $__user->getAliases() ?> (<?php echo $__user->getName() ?>)</a></h4>

		<?php if($__user->getUrl() != NULL): ?>
			<p>站点：<a href="<?php echo $__user->getUrl() ?>"><?php echo $__user->getUrl() ?></a></p>
		<?php endif; ?>
	</div>
</div>