<div id="Register_form">
	<form class="form-horizontal" id="Register_form" action="<?php echo get_url('UserApi/register') ?>" method="post">
		<fieldset>
			<?php if(!req()->is_ajax()){?><legend>用户注册</legend><?php }?>
			<div class="well text-danger hidden"></div>
			<div class="form-group">
				<label for="inputName" class="col-sm-2 control-label">用户名</label>

				<div class="col-sm-10">
					<input type="text" class="form-control" name="name" value="" data-toggle="tooltip" title="用户名已存在或规则错误" id="inputName">

					<p class="help-block">一个唯一的名字，不允许重复，不区分大小写，6-20位。可用字符有
						<small class="text-warning">数字,大小写英文,下划线,点号，不允许数字和点开头</small>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label for="inputEmail" class="col-sm-2 control-label">邮箱</label>

				<div class="col-sm-10">
					<input type="email" class="form-control" name="email" data-toggle="tooltip" title="邮箱已存在或格式错误" value="" id="inputEmail">

					<p class="help-block text-danger">请填写可验证邮箱，否者无法上传图片。</p>
				</div>
			</div>
			<div class="form-group">
				<label for="inputPassword" class="col-sm-2 control-label">密码</label>

				<div class="col-sm-10">
					<input type="password" class="form-control" name="password" value="" id="inputPassword">

					<p class="help-block">任意密码，推荐6位以上，本地加密，服务器端不做验证。</p>
				</div>
			</div>
			<div class="form-group">
				<label for="inputPassword2" class="col-sm-2 control-label">确认密码</label>

				<div class="col-sm-10">
					<input type="password" class="form-control" name="password2" value="" id="inputPassword2">

					<p class="help-block">重复输入一遍密码</p>
				</div>
			</div>
			<div class="form-group">
				<label for="inputCaptcha" class="col-sm-2 control-label">验证码</label>

				<div class="col-sm-6">
					<input type="text" class="form-control" name="captcha" value="" id="inputCaptcha">
				</div>
				<div class="col-sm-2">
					<img id="Register_captcha" src="<?php echo get_url("Tool", "captcha") ?>" title="点击刷新" height="32" alt="captcha"/>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-primary">注册</button>
				</div>
			</div>
		</fieldset>
	</form>
</div>
<script src="<?php echo get_file_url("js/md5_sha1.js"); ?>"></script>
<script>
	$(function () {
		var i = 1;
		var captcha_refresh = function () {
			var s = $("#Register_captcha");
			s[0].src = "<?php echo get_url("Tool","captcha")?>?refresh=" + (i++);
			$("#Register_form form input[name=captcha]").val("");
		};
		$("#Register_captcha").click(captcha_refresh);
		var user_api_url = "<?php echo get_url("UserApi")?>";
		$("form input[name=name]").on("change", function () {
			this.value = this.value.replace(/[^A-Z0-9a-z_.]/g, '');
			if (this.value.length < 6) {
				$("form input[name=name]").attr("title", "必须大于5位");
				$("form input[name=name]").tooltip('show');
				$("form .form-group:first").removeClass("has-success").addClass("has-error");
				return false;
			}
			if (this.value.length > 20) {
				$("form input[name=name]").attr("title", "必须小于20位");
				$("form input[name=name]").tooltip('show');
				$("form .form-group:first").removeClass("has-success").addClass("has-error");
				return false;
			}
			$.post(user_api_url + "/user_check/" + this.value, {}, function (data) {
				$("form input[name=name]").attr("title", "用户已存在，试试其他名称");
				if (data['status']) {
					$("form input[name=name]").tooltip('destroy');
					$("form .form-group:first").removeClass("has-error").addClass("has-success");
				} else {
					$("form input[name=name]").tooltip('show');
					$("form .form-group:first").removeClass("has-success").addClass("has-error");
				}
			});
		});
		$("form input[name=email]").on("change", function () {
			$.post(user_api_url + "/email_check/" + this.value, {}, function (data) {
				if (data['status']) {
					$("form input[name=email]").tooltip('destroy');
					$("form .form-group:eq(1)").removeClass("has-error").addClass("has-success");
				} else {
					$("form input[name=email]").tooltip('show');
					$("form .form-group:eq(1)").removeClass("has-success").addClass("has-error");
				}
			});
		});
		$("#Register_form form").submit(function () {
			$("#Register_form form .well").fadeOut('slow');
			var error = function (text) {
				var s = $("#Register_form form .well");
				s.html(text);
				s.removeClass("hidden");
				s.fadeIn("slow");
			};
			var param = {
				name: $("#Register_form form input[name=name]").val(),
				password: password($("#Register_form form input[name=password]").val()),
				email: $("#Register_form form input[name=email]").val(),
				captcha: $("#Register_form form input[name=captcha]").val()
			};
			var pwd2 = $("#Register_form form input[name=password2]").val();
			if (param.captcha.length < 4) {
				error("验证码格式不正确");
				return false;
			}
			if (param.name.length < 6 || param.name.length > 20 || /^[_a-zA-Z]{1}[\w\d_.]{5,}$/.test(param.name) === false) {
				error("用户名不符合规则,6-20位，只能包含'_.'和字母数字，不能数字和点开头");
				return false;
			}
			if (/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,}){1,})$/.test(param.email) === false) {
				error("邮箱不符合规则");
				return false;
			}
			if (pwd2.length < 6) {
				error("建议密码长度不小于6");
				return false;
			}
			if (password(pwd2) !== param.password) {
				error("两次密码不一致");
				return false;
			}
			$("form button[type=submit]").addClass("disabled");
			$("form button[type=submit]").html("表单数据提交中，请稍等");
			$.post(this.action, param, function (data) {
				$("form button[type=submit]").html("开始注册");
				$("form button[type=submit]").removeClass('disabled');
				if (data['status']) {
					var l_u = "<?php echo get_url('Home','login');?>?redirect=<?php echo urlencode(get_url('User'));?>&account=" + param.name;
					error("<span class='text-success'>注册成功，5秒后将跳转到登录页面。</span><a href='" + l_u + "'>立即跳转</a> ");
					$(".form-group").fadeOut("slow");
					setTimeout(function () {
						location.href = l_u;
					}, 5000);//延迟5秒刷新页面
				} else {
					error("服务器返回错误: " + data['msg']);
				}
				if (data['code'] != -1) {
					captcha_refresh();
				}
			});
			return false;
		});
	});
</script>
</body>
</html>