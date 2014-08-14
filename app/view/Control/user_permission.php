<form id="Control_user_permission_form" class="form-horizontal" style="max-width: 800px" action="<?php echo get_url("UserControlApi", "permission") ?>" method="post">
	<fieldset>
		<div id="legend">
			<legend class="">编辑用户权限</legend>
		</div>
		<div class="form-group">
			<label for="inputSiteTitle" class="col-sm-2 control-label">权限列表</label>

			<div class="col-sm-10">
				<textarea class="form-control" name="permission" rows="10"><?php echo htmlspecialchars($__info)?></textarea>
				<p class="help-block">每行一个权限，重复数据将被过滤</p>
			</div>
		</div>
		<input type="hidden" name="id" value="<?php echo $__user_id?>">
		<input type="hidden" name="operate" value="set">
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" class="btn btn-primary">保存更改</button>
			</div>
		</div>
	</fieldset>
</form>
<script>
	$("#Control_user_permission_form").ajaxForm(function(data){
		if(data['status']){
			alert_notice("用户权限已更新");
		}else{
			alert_error(data['msg'],"权限更新失败");
		}
	});
</script>