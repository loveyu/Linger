<form id="User_add_form" class="form-horizontal" method="post" action="<?php echo get_url("UserControlApi", "user_add") ?>" style="max-width: 800px">
	<fieldset>
		<div id="legend">
			<legend class="">添加新用户</legend>
		</div>
		<div class="form-group">
			<label for="inputUsername" class="col-sm-2 control-label">用户名</label>

			<div class="col-sm-10">
				<input type="text" name="name" class="form-control" value="" id="inputUsername" placeholder="name">
			</div>
		</div>
		<div class="form-group">
			<label for="inputEmail" class="col-sm-2 control-label">邮箱</label>

			<div class="col-sm-10">
				<input type="text" name="email" class="form-control" value="" id="inputEmail" placeholder="email">
			</div>
		</div>
		<div class="form-group">
			<label for="inputPassword" class="col-sm-2 control-label">密码</label>

			<div class="col-sm-10">
				<input type="text" name="passwpord" class="form-control" value="" id="inputPassword" placeholder="password">
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" class="btn btn-primary">添加用户</button>
			</div>
		</div>
	</fieldset>
</form>
<script>
	$("#User_add_form").ajaxForm(function (data) {
		if(data['status']){
			alert_notice("新用户已添加,ID"+data['content']);
		}else{
			alert_error(data['msg'],'用户添加失败');
		}
	});
</script>