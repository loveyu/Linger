<?php
/**
 * User: loveyu
 * Date: 2016/4/26
 * Time: 1:46
 * @var array    $__list
 * @var string[] $__pager
 * @var int      $__number
 * @var string   $__tag_name
 */
?>
<h2>图片标签 - <span><?php echo $__tag_name ?></span>
	<?php if($__number > 1): ?>
		<small>第<?php echo $__number ?>页</small>
	<?php endif; ?>
</h2>
<div id="H-Pic" class="main_box hidden-print">
	<div class="picture  clearfix">
		<?php $i = 0;
		foreach($__list as $v):$i++; ?>
			<a class="<?php if($i > 6){
				echo "hidden-xs ";
			}
			if($i > 8){
				echo "hidden-sm";
			} ?>" href="<?php echo picture_link($v['pic_id']) ?>"
			   title="<?php echo mb_substr($v['pic_description'], 0, 20, "UTF-8") ?>">
				<img src="<?php echo $v['pic_thumbnails_url'] ?>"
					 alt="<?php echo $v['pic_description'] ?>"></a>
		<?php endforeach; ?>
	</div>
	<?php if($__pager['previous'] != NULL || $__pager['next'] != NULL): ?>
		<ul class="pager">
			<?php if($__pager['previous'] != NULL): ?>
				<li class="previous"><a href="<?php echo $__pager['previous'] ?>">&larr; 上一页</a></li>
			<?php endif;
			if($__pager['next'] != NULL): ?>
				<li class="next"><a href="<?php echo $__pager['next'] ?>">下一页 &rarr;</a></li>
			<?php endif; ?>
		</ul>
	<?php endif; ?>
</div>