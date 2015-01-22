<form id="Option_Form" class="form-horizontal" role="form" method="post" action="<?php echo get_url("UserControlApi", "option_update") ?>"
	  style="max-width: 800px;margin: 10px 0;">
	<fieldset>
		<div id="legend">
			<legend class="">网站系统选项</legend>
		</div>
		<div class="form-group">
			<label for="inputSiteTitle" class="col-sm-2 control-label">网站标题</label>

			<div class="col-sm-10">
				<input type="text" name="site_title" class="form-control" value="<?php echo site_title(); ?>" id="inputSiteTitle" placeholder="一个短标题">

				<p class="help-block">用一个最简洁的短语来描述这个网站</p>
			</div>
		</div>
		<div class="form-group">
			<label for="inputSiteDesc" class="col-sm-2 control-label">网站描述</label>

			<div class="col-sm-10">
				<input type="text" name="site_desc" class="form-control" value="<?php echo site_desc(); ?>" id="inputSiteDesc" placeholder="一句描述性语句">

				<p class="help-block">用一句详细的话来描述你的网站的特点及特性</p>
			</div>
		</div>
		<div class="form-group">
			<label for="inputAdminEmail" class="col-sm-2 control-label">管理员邮箱</label>

			<div class="col-sm-10">
				<input type="email" name="admin_email" class="form-control" value="<?php echo admin_email(); ?>" id="inputAdminEmail"
					   placeholder="邮箱地址">

				<p class="help-block">用于网站的系统提示等通知</p>
			</div>
		</div>
		<div class="form-group">
			<label for="inputSiteMode" class="col-sm-2 control-label">网站模式</label>

			<div class="col-sm-10">
				<select name="site_mode" class="form-control" id="inputSiteMode">
					<?php echo html_option([
						'http' => 'http',
						'https' => 'https',
						'all' => '兼容两者'
					], site_mode()) ?>
				</select>

				<p class="help-block">网站的自定义网址前缀，必须以斜杠结尾</p>
			</div>
		</div>
		<div class="form-group">
			<label for="inputSiteUrl" class="col-sm-2 control-label">网站地址</label>

			<div class="col-sm-10">
				<input type="url" name="site_url" class="form-control" value="<?php echo site_url(); ?>" id="inputSiteUrl" placeholder="url">

				<p class="help-block">网站的自定义网址前缀，必须以斜杠结尾</p>
			</div>
		</div>
		<div class="form-group">
			<label for="inputSiteStaticUrl" class="col-sm-2 control-label">静态地址</label>

			<div class="col-sm-10">
				<input type="url" name="site_static_url" class="form-control" value="<?php echo site_static_url(); ?>" id="inputSiteStaticUrl"
					   placeholder="url">

				<p class="help-block">用于网站资源设置的静态访问地址，必须以斜杠结尾</p>
			</div>
		</div>
		<div class="form-group">
			<label for="inputSiteUrl_ssl" class="col-sm-2 control-label">HTTPS网站地址</label>

			<div class="col-sm-10">
				<input type="url" name="site_url_ssl" class="form-control" value="<?php echo site_url_ssl(); ?>" id="inputSiteUrl_ssl"
					   placeholder="url">

				<p class="help-block">网站的自定义网址前缀，必须以斜杠结尾</p>
			</div>
		</div>
		<div class="form-group">
			<label for="inputSiteStaticUrl_ssl" class="col-sm-2 control-label">HTTPS静态地址</label>

			<div class="col-sm-10">
				<input type="url" name="site_static_url_ssl" class="form-control" value="<?php echo site_static_url_ssl(); ?>"
					   id="inputSiteStaticUrl_ssl"
					   placeholder="url">

				<p class="help-block">用于网站资源设置的静态访问地址，必须以斜杠结尾</p>
			</div>
		</div>
		<div class="form-group">
			<label for="CommentOnePage" class="col-sm-2 control-label">每页评论数</label>

			<div class="col-sm-10">
				<input type="number" name="comment_one_page" class="form-control" value="<?php echo comment_one_page(); ?>" id="CommentOnePage"
					   placeholder="数量">

				<p class="help-block">评论每页显示的顶级评论数量</p>
			</div>
		</div>

		<div class="form-group">
			<label for="CommentOnePage" class="col-sm-2 control-label">评论嵌套数</label>

			<div class="col-sm-10">
				<input type="number" name="comment_deep" class="form-control" value="<?php echo comment_deep(); ?>" id="CommentDeep"
					   placeholder="最大层数">

				<p class="help-block">评论CSS最大的嵌套层数</p>
			</div>
		</div>

		<div class="form-group">
			<label for="inputAvatar" class="col-sm-2 control-label">网站默认头像</label>

			<div class="col-sm-10">
				<select name="default_avatar" class="form-control">
					<option value="default"<?php echo strtolower(default_avatar_config()) == "default" ? " selected" : "" ?>>网站默认头像</option>
					<option value="gravatar"<?php echo strtolower(default_avatar_config()) == "gravatar" ? " selected" : "" ?>>Gavatar头像</option>
				</select>

				<p class="help-block">针对用户创建一个默认的头像，访问<a href="http://en.gravatar.com/">Gavatar</a></p>
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">用户注册</label>

			<div class="col-sm-10">
				<div class="checkbox">
					<label>
						<input name="allowed_register" value="yes" type="checkbox"<?php echo allowed_register() ? " checked" : "" ?>> 是否允许新用户注册
					</label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">评论权限</label>

			<div class="col-sm-10">
				<div class="checkbox">
					<label>
						<input name="allowed_comment" value="yes" type="checkbox"<?php echo allowed_comment() ? " checked" : "" ?>> 是否允许用户评论
					</label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">评论排序</label>

			<div class="col-sm-10">
				<div class="checkbox">
					<label>
						<input name="comment_order_desc" value="yes" type="checkbox"<?php echo comment_order_desc() ? " checked" : "" ?>> 使用倒序排列评论
					</label>
				</div>
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">邮件提醒</label>

			<div class="col-sm-10">
				<div class="checkbox">
					<label>
						<input name="email_notice" value="yes" type="checkbox"<?php echo email_notice() ? " checked" : "" ?>> 新用户注册等事件的邮件提示
					</label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">登录验证码</label>

			<div class="col-sm-10">
				<div class="checkbox">
					<label>
						<input name="login_captcha" value="yes" type="checkbox"<?php echo login_captcha() ? " checked" : "" ?>> 登录时输入验证码
					</label>
				</div>
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" class="btn btn-primary">保存更改</button>
			</div>
		</div>
	</fieldset>
</form>
<script>
	$("#Option_Form").ajaxForm(function (data) {
		if (data['status']) {
			alert_notice("数据已更新");
		} else {
			alert_error(data['msg'], '数据更新失败');
		}
	});
</script>