<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">创建文章</h2>
	</div>

	<div class="panel-body">
		<form id="PostCreate" action="<?php echo get_url("UserApi", "post_create") ?>" method="post">
			<div class="form-group">
				<label for="InputTitle">标题：</label>
				<input class="form-control input-lg" id="InputTitle" name="title" type="text">
			</div>
			<div class="form-group">
				<label for="InputTitle">唯一名称：</label>
				<input class="form-control" id="InputTitle" name="name" type="text">

				<p class="help-block">唯一的英文字符名称，用于访问标示</p>
			</div>
			<button class="btn btn-primary">去编辑</button>
		</form>
		<script>
			$("#PostCreate").ajaxForm(function (data) {
				if (data['status']) {
					alert_notice("创建成功");
					setTimeout(function () {
						location.href = '<?php echo get_url("Posts","edit")?>?id=' + data['content']['id'];
					}, 1000);
				} else {
					alert_error(data['msg'], "创建失败，请检查");
				}
			});
		</script>
	</div>
</div>