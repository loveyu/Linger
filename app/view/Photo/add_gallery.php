<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">添加图集</h2>
	</div>
	<div class="panel-body">
		<form method="post" style="max-width: 600px" action="<?php echo get_url("UserApi", "gallery_add"); ?>">
			<fieldset>
				<div class="form-group">
					<label class="control-label">图集的标题</label>
					<input class="form-control" name="gallery_title" value="" type="text">

					<p class="help-block">比需填写该信息来创建一个图集,任意字符，美观简洁就好</p>
				</div>
				<button class="btn btn-primary" type="submit">创建</button>
			</fieldset>
		</form>
	</div>
</div>
<script>
	$(function () {
		$("form").ajaxForm(function (data) {
			if (data['status']) {
				var id = +data['content'];
				if (id > 0) {
					location.href = '<?php echo get_url("Photo","edit_gallery");?>?id=' + id;
				} else {
					alert_notice("成功，但有异常，请查看图集列表");
				}
			} else {
				alert_error(data['msg'], "创建出错");
			}
		});
	});
</script>