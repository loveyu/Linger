<?php
$user = login_user();
$new_email = $user->getMeta()->get(['edit_email_add'], '')['edit_email_add'];
?>
<div class="panel panel-danger">
	<div class="panel-heading">
		<h2 class="panel-title">修改用户邮箱</h2>
	</div>
	<div class="panel-body form-horizontal">
		<div class="form-group">
			<label for="inputEmail" class="col-sm-2 control-label">当前邮箱</label>

			<div class="col-sm-10">
				<input type="text" class="form-control" readonly value="<?php echo $user->getEmail() ?>" id="inputEmail">
			</div>
		</div>
		<div class="form-group">
			<label for="inputPassword" class="col-sm-2 control-label">当前用户密码</label>

			<div class="col-sm-10">
				<input type="password" class="form-control" value="" id="inputPassword">
			</div>
		</div>
		<div class="form-group">
			<label for="inputNewEmail" class="col-sm-2 control-label">新邮箱</label>

			<div class="col-sm-7">
				<input type="email" class="form-control" value="<?php echo $new_email; ?>" id="inputNewEmail">
			</div>
			<div class="col-sm-3">
				<button id="User_edit_email_send_mail" class="btn btn-warning">发送验证邮件<span class="hidden badge pull-right"></span></button>
			</div>
		</div>
		<div class="form-group">
			<label for="inputCode" class="col-sm-2 control-label">邮箱验证码</label>

			<div class="col-sm-10">
				<input type="text" class="form-control" value="" id="inputCode">
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" id="User_edit_email_submit" class="btn btn-primary">确认修改邮箱</button>
			</div>
		</div>
	</div>
</div>
<script src="<?php echo get_file_url("js/md5_sha1.js"); ?>"></script>
<script>
	$(function () {
		$("#User_edit_email_submit").click(function () {
			var pwd = $("#inputPassword").val();
			if (pwd.length < 6) {
				alert_error("密码长度在6位及以上", "密码不符");
				return;
			}
			var email = $("#inputNewEmail").val().toLowerCase();
			if (/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,}){1,})$/.test(email) === false) {
				alert_error("邮箱不符合规则");
				return;
			}
			var code = $("#inputCode").val().toLowerCase();
			if (code.length !== 40 || /^[0-9a-f]+$/.test(code) === false) {
				alert_error("验证码不符合规则");
				return;
			}
			$.post("<?php echo get_url("UserApi","edit_email")?>", {email: email, password: password(pwd), code: code}, function (data) {
				if(data['status']){
					alert_notice("修改用户邮箱成功");
				}else{
					alert_error(data['msg'],"邮箱修改失败");
				}
			});
		});
		$("#User_edit_email_send_mail").click(function () {
			var pwd = $("#inputPassword").val();
			if (pwd.length < 6) {
				alert_error("密码长度在6位及以上", "密码不符");
				return;
			}
			var email = $("#inputNewEmail").val();
			if (/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,}){1,})$/.test(email) === false) {
				alert_error("邮箱不符合规则");
				return;
			}
			$.post("<?php echo get_url("UserApi","edit_email_send_mail")?>", {email: email, password: password(pwd)}, function (data) {
				if (data['status']) {
					alert_notice("邮件发送成功");
					$("#User_edit_email_send_mail span").removeClass("hidden");
					var count = 10;
					$("#User_edit_email_send_mail span").html("");
					var call = function () {
						if (count > 0) {
							$("#User_edit_email_send_mail span").html(count--);
							setTimeout(call, 1000);
						} else {
							$("#User_edit_email_send_mail span").addClass("hidden");
							$("#User_edit_email_send_mail").removeAttr("disabled");
						}
					};
					setTimeout(call, 1000);
				} else {
					alert_error(data['msg'], "邮件发送失败");
					$("#User_edit_email_send_mail").removeAttr("disabled");
				}
			});
			this.disabled = true;
		});
	});
</script>