<?php
/**
 * @var array $__info 获取到的信息
 */
?>
<div class="well well-sm">
	<p>当前版本：<span class="label label-info"><?php echo _VERSION_ ?></span></p>
	<?php if(isset($__info['version'])): ?>
		<p>检测最新：<span class="label label-danger"><?php echo htmlentities($__info['version']) ?></span></p>
	<?php endif; ?>
	<?php if(isset($__info['time'])): ?>
		<p>发布时间：<?php echo date("Y-m-d H:i:s", (int)$__info['time']) ?></p>
	<?php endif; ?>
	<?php if(isset($__info['msg']) && is_array($__info['msg'])): $i = 1;
		foreach($__info['msg'] as $v): ?>
			<p>更新信息<?php echo $i++ ?>：<?php echo htmlentities($v) ?></p>
		<?php endforeach; endif; ?>
	<?php if(isset($__info['detail_url']) && filter_var($__info['detail_url'], FILTER_VALIDATE_URL)): ?>
		<p>更新详情：<a href="<?php echo $__info['detail_url'] ?>"><?php echo $__info['detail_url'] ?></a></p>
	<?php endif; ?>
	<?php if(isset($__info['download_url']) && filter_var($__info['download_url'], FILTER_VALIDATE_URL)): ?>
		<p>下载地址：<a href="<?php echo $__info['download_url'] ?>"><?php echo $__info['download_url'] ?></a></p>
	<?php endif; ?>
	<button id="CheckUpdate" class="btn btn-primary btn-sm">检测更新</button>
</div>
<script>
	jQuery(function ($) {
		$("#CheckUpdate").click(function () {
			update_hide();
			$("#CheckUpdate").addClass("disabled");
			$.get(API_URL + "/checkUpdate?force=1", function (data) {
				$("#CheckUpdate").removeClass("disabled");
				if (data.status && data.content != "") {
//					update_show(data.content);
					location.reload();
				}
			});
		});
	});
</script>