<?php
/**
 * @var int[] $__count
 * @var array $__list
 */
?>
	<h1>信息栏</h1>

<?php foreach($__list as $v): ?>
	<article class="post-entry">
		<div class="post-title">
			<h3><a class="glyphicon glyphicon-link" href="<?php echo post_link($v['post_name']) ?>"><?php echo $v['post_title'] ?></a></h3>

			<p class="glyphicon glyphicon-time">发表于<span class="text-success"><?php echo explode(" ", $v['post_time'])[0] ?></span></p>
		</div>
		<div class="post-content">
			<?php
			$s = preg_replace("/[\n\r]+/", "<br />", mb_substr(htmlspecialchars(strip_tags(preg_replace("/<pre[\\s\\S]*>[\\s\\S]+<\\/pre>/", "", get_markdown($v['post_content'])))), 0, 200, "UTF-8"));
			echo "<p>",$s,"</p>";
			?>
		</div>
		<div class="post-tag text-right">
			<p>
				<span class="glyphicon glyphicon-tag">分类：<span class="text-primary"><?php echo $v['post_category'] ?></span></span>&nbsp;&nbsp;
				<span class="glyphicon glyphicon-comment"><a href="<?php echo post_link($v['post_name']) ?>#Comment"><span
							class="text-success"><?php echo $v['post_comment_count'] ?></span>人评论</a></span>
			</p>
		</div>
	</article>
<?php endforeach; ?>
<?php if($__count['max'] > 1): ?>
	<ul class="pager">
		<?php if($__count['page'] > 1): ?>
			<?php if($__count['page'] == 2): ?>
				<li class="previous"><a href="<?php echo post_list_link() ?>">上一页</a></li>
			<?php else: ?>
				<li class="previous"><a href="<?php echo post_list_pager_link($__count['page'] - 1) ?>">上一页</a></li>
			<?php endif; ?>
		<?php endif; ?>
		<?php if($__count['page'] < $__count['max']): ?>
			<li class="next"><a href="<?php echo post_list_pager_link($__count['page'] + 1) ?>">下一页</a></li>
		<?php endif; ?>
	</ul>
<?php endif; ?>