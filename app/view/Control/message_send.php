<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">发送系统信息</h2>
	</div>

	<div class="panel-body">
		<form method="post" id="MessageSendForm" class="form-horizontal" action="<?php echo get_url("UserControlApi", "message_send") ?>">
			<fieldset>
				<div class="form-group">
					<label class="col-sm-1 control-label" for="InputTitle">标题</label>

					<div class="col-sm-11">
						<input id="InputTitle" name="title" placeholder="输入标题" value="" class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-1" for="InputUser">用户</label>

					<div class="col-sm-11">
						<input id="InputUser" name="users" class="form-control" value="" placeholder="用户的名称或ID，多个用户空白符分开">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-1" for="InputContent">内容</label>

					<div class="col-sm-11">
						<p class="help-block">Markdown语法支持</p>
						<textarea class="form-control" id="InputContent" name="content" rows="4"></textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-1 col-sm-11">
						<button class="btn btn-primary" type="submit">发送</button>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>
<script>
	var markdown_edit = null;
	$(function () {
		$("<link>")
			.attr({ rel: "stylesheet",
				type: "text/css",
				href: "<?php echo get_bootstrap_plugin_url('markdown/markdown.min.css') ?>"
			})
			.appendTo("head");
		$("#MessageSendForm").ajaxForm(function (data) {
			if (data['status']) {
				modal_show("<span class='text-warning'>发送状态！</span>",
					"<div><p class='text-success'>成功发送给：" + data['content']['ok'] + " 个用户</p>" +
						(data['content']['error'] > 0 ? "<p class='text-danger'>" + data['content']['error'] + " 个用户发送失败</p>" : "") +
						"<p>详情查看发信箱。</p></div>"
				);
				markdown_edit.setContent("");
			} else {
				alert_error(data['msg'], "发送错误！");
			}
		});
		$.getScript('<?php echo get_bootstrap_plugin_url('markdown/markdown.js') ?>', function () {
			$("#InputContent").markdown({
				onShow: function (e) {
					markdown_edit = e;
				}, onPreview: function (e) {
					var previewContent;
					var originalContent = e.getContent();
					var status = $.ajaxSettings.async;
					$.ajaxSetup({
						async: false
					});
					$.post(SITE_URL + "UserApi/markdown", {content: originalContent}, function (data) {
						if (data['status']) {
							previewContent = data['content'];
						} else {
							previewContent = "异常信息：" + data['msg'];
						}
						$.ajaxSetup({
							async: status
						});
					});
					return previewContent;
				}
			});
		});
	});
</script>