<h2>配置系统信息,最后一步</h2>
<form method="post" action="install.php?setup=system">
	<div class="form-group">
		<label class="control-label" for="DEBUG">是否启用调试模式</label>
		<select name="_Debug_" class="form-control" id="DEBUG">
			<option value="0">关闭</option>
			<option value="1">启用</option>
		</select>

		<p class="help-block">启用调试模式后会输出所有错误信息，否则将不输出任何错误信息</p>
	</div>
	<div class="form-group">
		<label class="control-label" for="COOKIE_PRE">COOKIE前缀</label>
		<input name="COOKIE_PREFIX" class="form-control" id="COOKIE_PRE" value="<?php echo htmlspecialchars(COOKIE_PREFIX) ?>">

		<p class="help-block">可以增对整个应用程序设置一个COOKIE前缀，避免冲突</p>
	</div>
	<div class="form-group">
		<label class="control-label" for="COOKIE_KEY">COOKIE密钥</label>
		<input name="COOKIE_KEY" class="form-control" id="COOKIE_KEY" value="<?php echo htmlspecialchars(salt(40)) ?>">

		<p class="help-block">用户COOKIE加密的字符串</p>
	</div>
	<div class="form-group">
		<label class="control-label" for="INSTALL_REMOVE">删除整个安装文件目录</label>
		<input name="INSTALL_REMOVE" type="checkbox" id="INSTALL_REMOVE" value="1">

		<p class="help-block">勾选该选项，安装完成后会删除所有的安装文件，不勾选将把安装文件移动到限制目录</p>
	</div>

	<div class="form-group">
		<button class="btn btn-primary" type="submit">提交信息</button>
	</div>
</form>
<script src="<?php echo get_js_url("jquery.form.js")?>"></script>
<script>
	$(function(){
		$("form").ajaxForm(function(data){
			if(data!="true"){
				alert("最后配置异常:"+data);
			}else{
				alert("安装完成，去首页！");
				location.href="<?php echo $session->get("home_url")?>";
			}
		});
	});
</script>