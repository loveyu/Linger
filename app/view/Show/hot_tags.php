<?php
/**
 * @var array    $__tags
 * @var callback $__font_size
 */
?>
<h3>热门标签</h3>

<div>
	<?php foreach($__tags as $v): ?>
		<a href="<?php echo tag_list_link($v['name']) ?>" class="btn btn-link" style="font-size: <?php echo $__font_size($v['count']) ?>px"><?php echo $v['name'] ?><span><sup><?php echo $v['count']?></sup></span></a>
	<?php endforeach; ?>
</div>