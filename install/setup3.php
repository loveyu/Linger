<h2>第三步，设置站点信息</h2>
<form action="install.php?setup=setting" method="post">
	<div class="form-group">
		<label class="control-label" for="I_title">网站标题</label>
		<input type="text" class="form-control" name="s[title]" value="又见回忆" id="I_title">
	</div>
	<div class="form-group">
		<label class="control-label" for="I_desc">网站描述</label>
		<input type="text" class="form-control" name="s[desc]" value="曾记否我们一切安好" id="I_desc">
	</div>

	<div class="form-group">
		<label class="control-label" for="I_url">网站地址</label>
		<input type="text" class="form-control" name="s[url]" value="<?php echo get_file_url()?>" id="I_url">
		<p class="help-block">注意：如果网站使用PATH_INFO的形式请在结尾处添加<code>index.php/</code>，如若修正指定文件请手动修改。</p>
	</div>

	<div class="form-group">
		<label class="control-label" for="I_static_url">网站静态地址</label>
		<input type="text" class="form-control" name="s[static_url]" value="<?php echo get_file_url()?>" id="I_static_url">
	</div>

	<div class="form-group">
		<label class="control-label" for="I_email">管理员邮箱</label>
		<input type="text" class="form-control" name="s[email]" placeholder="你必须提交你的管理员邮箱" value="" id="I_email">
	</div>

	<hr>


	<div class="form-group">
		<label class="control-label" for="I_user">管理员账户</label>
		<input type="text" class="form-control" name="u[name]" placeholder="你的用户名，最低6位" value="" id="I_user">
		<p class="help-block">只允许包含，字母数字下划线和点号，且不允许点和数字开头</p>
	</div>

	<div class="form-group">
		<label class="control-label" for="I_user_p">管理员密码</label>
		<input type="password" class="form-control" name="u[pwd]" placeholder="输入一个密码" value="" id="I_user_p">
		<p class="help-block">长度建议6位</p>
	</div>

	<div class="form-group">
		<button type="submit" class="btn btn-primary">提交信息</button>
	</div>
</form>
<script src="<?php echo get_js_url("jquery.form.js")?>"></script>
<script>
	$(function(){
		$("form").ajaxForm(function(data){
			if(data!="true"){
				alert(data);
			}else{
				location.href="install.php?setup=4";
			}
		});
	});
</script>