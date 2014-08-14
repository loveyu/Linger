<div class="panel panel-warning">
	<div class="panel-heading">
		<h3 class="panel-title">修改用户密码</h3>
	</div>
	<div class="panel-body">
		<?php $user = login_user(); ?>
		<form id="User_edit_password_form" class="form-horizontal" method="post" action="<?php echo get_url("UserApi", "edit_password") ?>">
			<div class="form-group">
				<label for="inputOldPassword" class="col-sm-2 control-label">原始密码</label>

				<div class="col-sm-10">
					<input type="password" name="old_password" class="form-control" value="" id="inputOldPassword" placeholder="之前的密码">
				</div>
			</div>
			<div class="form-group">
				<label for="inputNewPassword" class="col-sm-2 control-label">新密码</label>

				<div class="col-sm-10">
					<input type="password" name="new_password" class="form-control" value="" id="inputNewPassword" placeholder="新的密码">
				</div>
			</div>
			<div class="form-group">
				<label for="inputNewPasswordConfirm" class="col-sm-2 control-label">确认新密码</label>

				<div class="col-sm-10">
					<input type="password" name="confirm_password" class="form-control" value="" id="inputNewPasswordConfirm" placeholder="再输入一次新的密码">
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-primary">修改</button>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="panel panel-danger">
	<div class="panel-heading">
		<h3 class="panel-title">Cookie 安全</h3>
	</div>
	<div class="panel-body">
		<button id="U_reset_cookie" class="btn btn-danger">重置登录COOKIE字符串</button>
		<button id="U_reset_cookie_salt" class="btn btn-danger">重置COOKIE加密字符</button>
	</div>
</div>
<?php
if(!empty($__Reset_password_code)):?>
	<div id="Reset_password_request" class="panel panel-danger">
		<div class="panel-heading">
			<h3 class="panel-title">存在密码重置请求</h3>
		</div>
		<div class="panel-body">
			<div class="well well-lg">
				<p class="text-warning">创建时间:<?php echo $__Reset_password_time; ?></p>
				<p class="text-danger">如果该邮件泄露可能导致账号被盗，此时你已登录账户，建议删除此信息。</p>
			</div>
			<button id="Reset_password_request" class="btn btn-danger">删除该请求</button>
		</div>
	</div>
<?php endif; ?>
<script src="<?php echo get_file_url("js/md5_sha1.js"); ?>"></script>
<script>
	var User_Reset_Cookie = function (type) {
		$.post("<?php echo get_url("UserApi","reset_cookie")?>", {type: type}, function (data) {
			if (data['status']) {
				alert_notice("重置COOKIE成功");
				if (type === "login") {
					if (confirm("该操作将导致账户须重新登录，请确认？")) {
						location.href = "<?php echo login_page();?>";
					}
				}
			} else {
				alert_error(data['msg'], "重置失败");
			}
		});
	}
	$("#U_reset_cookie").click(function () {
		User_Reset_Cookie("login");
	});
	$("#U_reset_cookie_salt").click(function () {
		User_Reset_Cookie("salt");
	});
	$("#Reset_password_request").click(function () {
		$.post("<?php echo get_url("UserApi","delete_reset_password_request")?>", {type: "OK"}, function (data) {
			if(data['status']){
				alert_notice("删除成功");
				$("#Reset_password_request").fadeOut("slow",function(){
					$("#Reset_password_request").remove();
				});
			}else{
				alert_error(data['msg'], "删除请求失败");
			}
		});
	});
	$("#User_edit_password_form").submit(function () {
		var old = $("form#User_edit_password_form input[name=old_password]").val();
		var new_pwd = $("form#User_edit_password_form input[name=new_password]").val();
		var confirm = $("form#User_edit_password_form input[name=confirm_password]").val();
		if (new_pwd.length < 6) {
			alert_error("新密码建议至少6位");
			return false;
		}
		if (new_pwd !== confirm) {
			alert_error("新的密码两次不一致");
			return false;
		}
		if (old === new_pwd) {
			alert_error("新旧密码不能一样");
			return false;
		}
		$.post(this.action, {new: password(new_pwd), old: password(old)}, function (data) {
			if (data['status']) {
				alert_notice("修改密码成功");
			} else {
				alert_error(data['msg'], '修改密码失败');
			}
		});
		return false;
	});
</script>