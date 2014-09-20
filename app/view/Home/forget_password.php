<script src="<?php echo get_file_url("js/jquery.form.js")?>"></script>
<form method="post" class="form-horizontal" action="<?php echo get_url("UserApi", "forget_password"); ?>" style="max-width: 500px">
	<fieldset>
		<legend>找回用户密码</legend>
		<p class="well well-sm text-warning">请认真回忆你的邮箱地址。</p>

		<div class="form-group">
			<label for="inputEmail" class="col-sm-2 control-label">邮箱</label>

			<div class="col-sm-10">
				<input type="email" class="form-control" name="email" value="" id="inputEmail" placeholder="邮箱地址">
			</div>
		</div>
		<div class="form-group">
			<label for="inputPassword" class="col-sm-2 control-label">验证码</label>

			<div class="col-sm-6">
				<input type="text" class="form-control" name="captcha" value="" id="inputCaptcha" placeholder="验证码">
			</div>
			<div class="col-sm-2">
				<img id="Forget_password_captcha" src="<?php echo get_url("Tool", "captcha") ?>" title="点击刷新" height="32" alt="captcha"/>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" class="btn btn-danger">发送验证邮件</button>
			</div>
		</div>
	</fieldset>
</form>
<script>
	var i = 0;
	var captcha_refresh = function () {
		var s = $("#Forget_password_captcha");
		s[0].src = "<?php echo get_url("Tool","captcha")?>?refresh=" + (i++);
		$("#Forget_password_captcha form input[name=captcha]").val("");
	};
	$("#Forget_password_captcha").click(captcha_refresh);
	$("form").submit(function(){
		$("form .well").html("<span class='text-warning'>邮件发送中....</span>");
	});
	$("form").ajaxForm(function (data) {
		var s = $("form .well");
		if (data['status']) {
			s.html("<span class='text-success'>重置邮件发送成功，注意查收。</span>");
		} else {
			s.html("发送重置邮件出错: " + data['msg']);
		}
		if(data['code']!==-3){
			captcha_refresh();
		}
		s.fadeOut('fast',function(){
			s.fadeIn("slow");
		});
	});
</script>