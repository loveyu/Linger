<?php
/**
 * User: loveyu
 * Date: 2016/4/29
 * Time: 1:26
 */
?>
<h3>图片流</h3>
<div id="H-Pic" class="main_box">
	<div id="PIC_CON" class="picture clearfix"></div>
</div>

<script type="text/javascript">
	jQuery(function ($) {
		var pf = new PicturesFlow("<?php echo get_url('DataApi', 'get_new_pics')?>", "#PIC_CON");
		pf.run();
	});
</script>