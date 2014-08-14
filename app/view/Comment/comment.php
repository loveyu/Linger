<?php
/**
 * @var ULib\Comment $__comment
 */
?>
<div class="comment-msg">
	<div class="comment-head clearfix">
		<img class="img-rounded pull-left comment-avatar" alt="<?php echo $__comment->getUser()->getName() ?>" src="<?php
		echo $__comment->getUser()->getAvatar() ?>">

		<div class="pull-left">
			<p class="comment-info"><strong class="comment-aliases"><a href="<?php echo user_link($__comment->getUser()->getName()) ?>"><?php echo $__comment->getUser()->getAliases() ?></a></strong>
				<span class="comment-name"><?php echo $__comment->getUser()->getName() ?></span>
			</p>

			<p>
				<small class="comment-post-at">评论于：</small>
				<span class="comment-time"><?php echo convert_time($__comment->getCommentTime()); ?></span></p>
		</div>
	</div>
	<div class="comment-content">
		<?php echo get_markdown($__comment->getCommentContent()) ?>
	</div>
	<div class="comment-like">
		<?php if(!$__comment->userLikeComment()): ?>
			<a class="comment-action-like" href="#Comment-id-<?php echo $__comment->getCommentId() ?>"><span class="glyphicon glyphicon-heart text-danger">喜欢<span>[<?php echo $__comment->getCommentLikeCount() ?>]</span></span></a>
		<?php else: ?>
			<a class="comment-action-like" href="#Comment-id-<?php echo $__comment->getCommentId() ?>"><span class="glyphicon glyphicon-heart-empty text-danger">取消喜欢<span>[<?php echo $__comment->getCommentLikeCount() ?>]</span></span></a>
		<?php endif; ?>
		<a class="comment-action-top" href="#Comment-id-<?php echo $__comment->getCommentId() ?>"><span class="glyphicon glyphicon-hand-up">顶<span>[<?php echo $__comment->getCommentTop() ?>]</span></span></a>
		<?php if(is_login() && $__comment->getUser()->getId() == login_user()->getId()): ?>
			<a class="comment-action-del hidden-print" href="#Comment-id-<?php echo $__comment->getCommentId() ?>"><span class="glyphicon glyphicon-remove text-warning">删</a>
		<?php endif; ?>
		<a class="comment-reply text-success" href="#Comment-id-<?php echo $__comment->getCommentId() ?>"><span class="glyphicon glyphicon-share-alt"><span>回复[<?php echo $__comment->getSubCount() ?>]</a></div>
</div>
<div id="comment-reply-<?php echo $__comment->getCommentId(); ?>"></div>
