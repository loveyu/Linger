<?php $user = login_user(); ?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">修改头像</h2>
	</div>
	<div class="panel-body">
		<blockquote>
			<p><img class="img-rounded user_edit_avatar" src="<?php echo $__avatar; ?>" alt="avatar"></p>
			<small>当前头像: <span class="text-primary"><?php echo $__avatar; ?></span></small>
			<small>头像是一种象征，不要轻易去换，更不要换来换去。</small>
		</blockquote>
		<div class="panel-group" id="accordion">

			<div class="panel panel-<?php echo($__type === "{default}" ? "info" : "default") ?>">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-toggle="collapse" data-parent="#accordion" href="#collapseZero">
							自动选择
						</a>
					</h4>
				</div>
				<div id="collapseZero" class="panel-collapse collapse<?php echo($__type === "{default}" ? " in" : "") ?>">
					<div class="panel-body">
						<p class="well well-sm text-warning">网站会设置一个默认头像，可能是网站上传的，也可能是Gravatar的头像，由网站管理员来确定。</p>
						<button class="btn btn-success user_change_set">设置为自动选择头像</button>
					</div>
				</div>
			</div>
			<div class="panel panel-<?php echo($__type === "{gravatar}" ? "info" : "default") ?>">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
							Gravatar在线头像
						</a>
					</h4>
				</div>
				<div id="collapseOne" class="panel-collapse collapse<?php echo($__type === "{gravatar}" ? " in" : "") ?>">
					<div class="panel-body">
						<p class="well well-sm text-warning">
							Gravatar 是一项用于提供在全球范围内使用的头像服务。
							访问<a href="https://cn.gravatar.com/">https://cn.gravatar.com/</a>来设置自己的头像。
							网站会根据你提供的邮箱生成相应的头像地址，此方式不会泄露你的邮箱信息，是一种安全的做法，推荐使用此方式。
						</p>

						<p class="well well-sm text-danger">此方式必须正确设置你的邮箱，并且你上传了头像。</p>
						<blockquote>
							<p><img class="img-rounded" src="http://1.gravatar.com/avatar/<?php echo md5("") ?>" width="100" height="100" alt=""></p>
							<small>这是该服务提供的默认头像。</small>
						</blockquote>
						<button class="btn btn-success user_change_set">使用Gravatar的头像</button>
					</div>
				</div>
			</div>
			<div class="panel panel-<?php echo($__type === "{site_avatar}" ? "info" : "default") ?>">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
							默认网站头像
						</a>
					</h4>
				</div>
				<div id="collapseTwo" class="panel-collapse collapse<?php echo($__type === "{site_avatar}" ? " in" : "") ?>">
					<div class="panel-body">
						<p class="well well-sm text-warning">网站上传了一张默认头头像到服务器上，你可以选择使用这张图片作为你的头像。</p>
						<blockquote>
							<p><img class="img-rounded" src="<?php echo \ULib\Avatar::default_avatar(); ?>" width="100" height="100" alt=""></p>
							<small>这是网站默认上传的头像。</small>
						</blockquote>
						<button class="btn btn-success user_change_set">使用该头像作为自己的头像</button>
					</div>
				</div>
			</div>
			<div class="panel panel-<?php echo($__type === "{user_upload}" ? "info" : "default") ?>">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
							自定义上上传
						</a>
					</h4>
				</div>
				<div id="collapseThree" class="panel-collapse collapse<?php echo($__type === "{user_upload}" ? " in" : "") ?>">
					<div class="panel-body">
						<p class="well well-sm text-warning">自己上传图片到服务器上，然后显示你的个性头像。建议上传400*400以上的图片，网站会将图片压缩到此尺寸。</p>
						<blockquote>
							<?php $u_a = \ULib\Avatar::upload_avatar(login_user());
							if(!empty($u_a)): ?>
								<p><img class="img-rounded" src="<?php echo $u_a ?>?rand=<?php echo time() ?>" alt="avatar" width="200" height="200"></p>
								<small>当前上传的头像</small>
							<?php else: ?>
								<small>当前未上传图片到服务器</small>
							<?php endif; ?>
						</blockquote>
						<form id="User_avatar_upload" method="post" role="form" class="well well-sm" enctype="multipart/form-data"
						      action="<?php echo get_url("UserApi", "user_avatar_upload") ?>">
							<div class="form-group">
								<label for="InputAvatarFile">选择你的图片</label>
								<input type="file" name="avatar" id="InputAvatarFile">

								<p class="help-block">仅支持jpg,png格式图片,大小限制500KB</p>
							</div>
							<button class="btn btn-danger" type="submit">上传图片</button>
						</form>
						<button class="btn btn-success user_change_set">确定设置自己上传的头像</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	$(function () {
		var time_string = function () {
			var date = new Date();
			return "" + date.getDay() + "" + date.getMinutes() + "" + date.getSeconds() + "" + date.getMilliseconds();
		}
		if (html5_supports()) {
			$("#User_avatar_upload").submit(function () {
				$("#User_avatar_upload button[type=submit]").html("上传中....");
				$("#User_avatar_upload").ajaxSubmit({error: function (context, xhr, status, error, $form) {
					alert_error("服务器错误，请使用其他浏览器重试", "上传图片失败");
					$("#User_avatar_upload button[type=submit]").html("上传失败:" + status);
				}, success: function (data) {
					if (data['status']) {
						alert_notice("上传图片成功");
						$("#User_avatar_upload input[type=file]").val("");
						$("#collapseThree blockquote").html("");
						$("#collapseThree blockquote").
							html('<p><img class="img-rounded" src="' +
								data['content'] + '?reload=' + time_string() + '" alt="avatar" width="200" height="200"></p>' +
								'<small>当前上传的头像</small>');
					} else {
						alert_error(data['msg'], "上传图片失败");
					}
					$("#User_avatar_upload button[type=submit]").html("上传图片");
				}});
				return false;
			});
		}
		var callback = function (type) {
			$.post("<?php echo get_url("UserApi","edit_avatar_type")?>", {type: type}, function (data) {
				if (data['status']) {
					alert_notice("头像选择已更新");
					setTimeout(function () {
						location.reload();
					}, 1500);//延迟1.5秒刷新页面
				} else {
					alert_error(data['msg'], "切换失败");
				}
			});
		};
		$("#collapseZero button.user_change_set").click(function () {
			callback("{default}");
		});
		$("#collapseOne button.user_change_set").click(function () {
			callback("{gravatar}");
		});
		$("#collapseTwo button.user_change_set").click(function () {
			callback("{site_avatar}");

		});
		$("#collapseThree button.user_change_set").click(function () {
			callback("{user_upload}");
		});
	});
</script>