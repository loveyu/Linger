<form id="Control_user_edit_form" class="form-horizontal" style="max-width: 800px" action="<?php echo get_url("UserControlApi", "user_edit") ?>" method="post">
	<fieldset>
		<div id="legend">
			<legend class="">编辑用户信息</legend>
		</div>
		<?php
		foreach($__info as $id => $v){
			$name = $id;
			$v = htmlspecialchars($v);
			echo '		<div class="form-group">
			<label for="inputEditInfo_' . $id . '" class="col-sm-2 control-label">' . $name . '</label>
			<div class="col-sm-10">
				<input type="text" name="' . $id . '" class="form-control" value="' . $v . '" id="inputEditInfo_' . $id . '">
			</div>
		</div>';
		}
		?>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" class="btn btn-primary">保存更改</button>
			</div>
		</div>
	</fieldset>
</form>
<script>
	$(function () {
		$("#Control_user_edit_form").ajaxForm(function (data) {
			if(data['status']){
				alert_notice("更新数据成功");
			}else{
				alert_error(data['msg'],'数据更新失败');
			}
		});
	});
</script>