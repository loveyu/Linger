<?php
/**
 * @var string $__account
 */
?>
<div id="Login_form">
	<form class="form-horizontal" method="post" action="<?php echo get_url('UserApi', 'login') ?>">
		<fieldset>
			<?php if(!req()->is_ajax()){?><legend>用户登录</legend><?php }?>
			<div class="well text-danger hidden"></div>
			<div class="form-group">
				<label for="inputAccount" class="col-sm-2 control-label">账号</label>

				<div class="col-sm-10">
					<input type="text" class="form-control" name="account" value="<?php echo(!isset($__account) ? "" : $__account) ?>"
						   id="inputAccount" placeholder="用户名/邮箱/ID">
				</div>
			</div>
			<div class="form-group">
				<label for="inputPassword" class="col-sm-2 control-label">密码</label>

				<div class="col-sm-10">
					<input type="password" class="form-control" name="password" value="" id="inputPassword">
				</div>
			</div>
			<?php if(login_captcha()): ?>
				<div class="form-group">
					<label for="inputPassword" class="col-sm-2 control-label">验证码</label>

					<div class="col-sm-6">
						<input type="text" class="form-control" name="captcha" value="" id="inputCaptcha" placeholder="验证码">
					</div>
					<div class="col-sm-2">
						<img id="Login_captcha" src="<?php echo get_url("Tool", "captcha") ?>" onclick="this.location.reload();" title="点击刷新"
							 height="32" alt="captcha"/>
					</div>
				</div>
			<?php endif; ?>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<div class="checkbox">
						<label>
							<input name="save" value="1" type="checkbox"> 记住登录状态
						</label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-primary">登录</button>
					<a href="<?php echo get_url("Home", "forget_password") ?>">找回密码</a>
				</div>
			</div>
		</fieldset>
	</form>
</div>
<script src="<?php echo get_file_url("js/md5_sha1.js"); ?>"></script>
<script>
	$(function () {
		var redirect = "<?php echo get_login_redirect('redirect');?>";
		<?php if(login_captcha()):?>
		var captcha_refresh = function () {
			$("#Login_captcha")[0].src = "<?php echo get_url("Tool","captcha")?>?refresh=" + Math.random();
			$("#Login_captcha form input[name=captcha]").val("");
		};
		$("#Login_captcha").click(captcha_refresh);
		<?php endif;?>
		$("#Login_form form").submit(function () {
			var param = {
				account: $("form input[name=account]").val(),
				password: password($("form input[name=password]").val()),
				save: $("form input[name=save]")[0].checked ? 1 : 0,
				captcha: $("form input[name=captcha]").val()
			};
			$.post(this.action, param, function (data) {
				if (data['status']) {
					location.href = redirect;
				} else {
					var s = $("#Login_form .well");
					s.html("登录出错: " + data['msg']);
					s.removeClass("hidden");
					$("#Login_form .well").fadeIn('slow');
				}
				<?php if(login_captcha()):?>
				if (data['code'] !== -10 && data['code'] !== -5) {
					captcha_refresh();
				}
				<?php endif;?>
			});
			$("#Login_form .well").fadeOut('slow');
			return false;
		});
	});
</script>