<?php
/**
 * @var array             $__info
 * @var \ULib\User        $__user
 * @var \ULib\CommentData $__CommentData
 */
?>
	<article>
		<h1 id="Post-title"><a href="<?php echo post_link($__info['post_name']) ?>"><?php echo $__info['post_title'] ?></a></h1>

		<div id="Post-content">
			<?php echo get_markdown($__info['post_content']) ?>
		</div>

		<div id="Post-meta">
			<a href="<?php echo user_link($__user->getName()) ?>"><?php echo $__user->getAliases() ?></a>
			发布于:<span><?php echo $__info['post_time'] ?></span>
			分类：<span><?php echo $__info['post_category'] ?></span>
		</div>
	</article>
	<script>views_add('posts', <?php echo $__info['post_id']?>);</script>
<?php display_comment($__CommentData); ?>