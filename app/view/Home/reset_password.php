<?php if($__status === true): ?>
	<script src="<?php echo get_file_url("js/md5_sha1.js") ?>"></script>
	<form method="post" class="form-horizontal" action="<?php echo get_url("UserApi", "reset_password") ?>" style="max-width: 600px;">
		<fieldset>
			<legend><?php echo $__user ?> 请输入新的密码。</legend>
			<p class="well well-sm text-danger">请尽量使用复杂并且容易记忆的密码。</p>

			<div class="form-group">
				<label for="inputPassword" class="col-sm-2 control-label">密码</label>

				<div class="col-sm-10">
					<input type="password" class="form-control" name="password" value="" id="inputPassword">
				</div>
			</div>
			<div class="form-group">
				<label for="inputPassword2" class="col-sm-2 control-label">确认密码</label>

				<div class="col-sm-10">
					<input type="password" class="form-control" name="password2" value="" id="inputPassword2">
				</div>
			</div>
			<input type="hidden" name="user" value="<?php echo $__user ?>">
			<input type="hidden" name="code" value="<?php echo $__code ?>">

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-danger">确定修改为当前密码</button>
				</div>
			</div>
		</fieldset>
	</form>
	<script>
		$("form").submit(function () {
			var error = function (text) {
				var s = $("form .well");
				s.html(text);
				s.fadeIn("slow");
			};
			var param = {
				user: $("form input[name=user]").val(),
				password: password($("form input[name=password]").val()),
				code: $("form input[name=code]").val()
			};
			var pwd2 = $("form input[name=password2]").val();
			if (pwd2.length < 6) {
				error("建议密码长度不小于6");
				return false;
			}
			if (password(pwd2) !== param.password) {
				error("两次密码不一致");
				return false;
			}
			$.post(this.action, param, function (data) {
				if (data['status']) {
					error("<strong class='text-success'>恭喜你，成功修改了密码。" +
						"<a href='<?php echo get_url("Home","login");?>?redirect=<?php echo urlencode(get_url("User"))?>&account=<?php echo $__user;?>'>去登录。</a></strong>");
					$(".form-group").remove();
				} else {
					error("服务器返回错误: " + data['msg']);
				}
			});
			$("form .well").fadeOut('slow');
			return false;
		});
	</script>
<?php else: ?>
	<h3 class="text-danger">重置密码信息验证失败</h3>
	<p class="well well-lg text-warning"><?php echo $__status; ?></p>
<?php endif;