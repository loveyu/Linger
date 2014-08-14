<?php $user = login_user(); ?>
<div class="panel panel-success">
	<div class="panel-heading">
		<h2 class="panel-title">用户激活</h2>
	</div>
	<div class="panel-body form-horizontal">
		<div class="form-group">
			<label for="inputEmail" class="col-sm-2 control-label">当前邮箱</label>

			<div class="col-sm-10">
				<input type="text" class="form-control" readonly value="<?php echo $user->getEmail() ?>" id="inputEmail">
			</div>
		</div>
		<div class="form-group">
			<label for="inputStatus" class="col-sm-2 control-label">当前状态</label>

			<div class="col-sm-10">
				<input type="text" class="form-control" readonly value="<?php echo $user->getStatusInfo($user->getStatus()) ?>" id="inputStatus">
			</div>
		</div>
		<?php if($user->getStatus() == 0): ?>
			<div class="form-group">
				<label for="inputCode" class="col-sm-2 control-label">激活码</label>

				<div class="col-sm-7">
					<input type="text" class="form-control" value="<?php echo $__code; ?>" id="inputCode">
				</div>
				<div class="col-sm-3">
					<button id="User_activation_send_mail" class="btn btn-warning">重发激活邮件<span class="hidden badge pull-right"></span></button>
				</div>
			</div>
			<script>
				$(function () {
					$("#User_activation_send_mail").click(function () {
						$.get("<?php echo get_url("UserApi","send_activation_mail")?>", function (data) {
							if (data['status']) {
								alert_notice("成功发送邮件");
								$("#User_activation_send_mail span").removeClass("hidden");
								var count = 60;
								$("#User_activation_send_mail span").html("");
								var call = function () {
									if (count > 0) {
										$("#User_activation_send_mail span").html(count--);
										setTimeout(call, 1000);
									} else {
										$("#User_activation_send_mail span").addClass("hidden");
										$("#User_activation_send_mail").removeAttr("disabled");
									}
								};
								setTimeout(call, 1000);
							} else {
								alert_error(data['msg'], "邮件发送失败");
								$("#User_activation_send_mail").removeAttr("disabled");
							}
						});
						this.disabled = true;
					});
				});
			</script>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" id="User_activation_active" class="btn btn-primary">确认激活</button>
				</div>
			</div>
			<script>
				$(function () {
					$("#User_activation_active").click(function () {
						$.get("<?php echo get_url("UserApi","user_activation")?>/" + $("#inputCode").val(), function (data) {
							if (data['status']) {
								alert_notice("激活成功");
								setTimeout(function () {
									location.reload();
								}, 200);//延迟2秒刷新页面
							} else {
								alert_error(data['msg'], "激活出错");
							}
						});
					});
				});

			</script>
		<?php endif; ?>
	</div>
</div>