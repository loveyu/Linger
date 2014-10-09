<form class="form" action="<?php echo get_url("UserControlApi", "footer") ?>" method="post" style="max-width: 1000px">
	<fieldset>
		<legend>底部设置</legend>
		<div class="form-group">
			<label class="control-label" for="html_input">Html 文档</label>
			<textarea id="html_input" class="form-control" name="footer" rows="10"><?php echo htmlentities(cfg()->get('option','footer'))?></textarea>
		</div>
		<div class="form-group">
			<button class="btn btn-primary" type="submit">修改</button>
		</div>
	</fieldset>
</form>
<script>
	$(function () {
		$("form").ajaxForm(function (data) {
			if (data['status']) {
				alert_notice("更新成功");
			} else {
				alert_error(data['msg'], "更新失败");
			}
		});
	});
</script>