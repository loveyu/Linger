<div class="panel panel-danger">
	<div class="panel-heading">
		<h2 class="panel-title">消息通知选项</h2>
	</div>

	<div class="panel-body">
		<form method="post" id="MessageOptionForm" action="<?php echo get_url("UserApi", "message_option") ?>">
			<fieldset>
				<legend>消息通知</legend>
				<?php $notice = notice();
				foreach($notice->getMessageList() as $k => $v): ?>
					<div class="checkbox">
						<label class="control-label">
							<input type="checkbox" name="message[<?php echo $k ?>]" value="1"<?php echo !$notice->getOptionMessage($k) ? : " checked" ?>>
							<?php echo $v ?>
						</label>
					</div>
				<?php endforeach; ?>
			</fieldset>
			<fieldset>
				<legend>邮件通知</legend>
				<?php foreach($notice->getMailList() as $k => $v): ?>
					<div class="checkbox">
						<label class="control-label">
							<input type="checkbox" name="mail[<?php echo $k ?>]" value="1"<?php echo !$notice->getOptionMail($k) ? : " checked" ?>>
							<?php echo $v ?>
						</label>
					</div>
				<?php endforeach; ?>
			</fieldset>
			<button type="submit" class="btn btn-primary">更新选项</button>
		</form>
		<script>
			$(function () {
				$("#MessageOptionForm").ajaxForm(function (data) {
					if (data['status']) {
						alert_notice("更新成功");
					} else {
						alert_error(data['msg'], "更新出错");
					}
				});
			});
		</script>
	</div>
</div>