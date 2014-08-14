<?php $user = login_user(); ?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">编辑个人基础信息</h2>
	</div>
	<div class="panel-body">
		<form id="User_edit_info_form" class="form-horizontal" method="post" action="<?php echo get_url("UserApi", "edit_info") ?>">
			<div class="form-group">
				<label for="inputAliases" class="col-sm-1 control-label">别名</label>

				<div class="col-sm-11">
					<input type="text" name="aliases" class="form-control" value="<?php echo $user->getAliases() ?>" id="inputAliases" placeholder="别名">
				</div>
			</div>
			<div class="form-group">
				<label for="inputAliases" class="col-sm-1 control-label">主页</label>

				<div class="col-sm-11">
					<input type="text" name="url" class="form-control" value="<?php echo $user->getUrl() ?>" id="inputAliases" placeholder="主页">
				</div>
			</div>
			<div class="form-group">
				<label for="inputAliases" class="col-sm-1 control-label">视频</label>

				<div class="col-sm-11">
					<p class="help-block">个人的视频介绍，支持视频网站{tudou|iqiyi|youku}，语法:[youku|id]，每行一个，详见:<a href="#">关于视频连接</a></p>
					<textarea name="video" class="form-control" rows="3"><?php echo $user->profile_video() ?></textarea>
				</div>
			</div>

			<div class="form-group">
				<label for="inputProfile" class="col-sm-1 control-label">介绍</label>

				<div class="col-sm-11">
					<p class="help-block">此处可使用<a title="查看详细语法说明" href="http://wowubuntu.com/markdown/">Markdown</a>语法编写，不允许HTML语法及字符。<br>链接请使用完整的语法，&lt;&gt;会被转义。</p>
					<textarea style="max-height: 600px;overflow-y:auto; height: 300px;" id="inputProfile" class="form-control" data-provide="markdown" name="profile_message"><?php echo $user->profile_message(); ?></textarea>
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-1 col-sm-5">
					<button type="submit" class="btn btn-primary">修改</button>
				</div>
				<div class="col-sm-offset-4 col-sm-2">
					<a href="<?php echo user_link($user->getName()) ?>" class="btn btn-info">查看个人信息</a>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
	$("#inputProfile").markdown({onPreview: function (e) {
		var previewContent;
		var originalContent = e.getContent();
		var status = $.ajaxSettings.async;
		$.ajaxSetup({
			async: false
		});
		$.post("<?php echo get_url("UserApi","markdown")?>", {content: originalContent}, function (data) {
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
	}});
	$("#User_edit_info_form").ajaxForm(function (data) {
		if (data['status']) {
			alert_notice("数据更新成功");
		} else {
			alert_error(data['msg'], '数据更新失败');
		}
	});
</script>