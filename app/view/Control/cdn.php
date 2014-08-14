<form class="form-horizontal" action="<?php echo get_url("UserControlApi", "cdn") ?>" method="post" style="max-width: 1000px">
	<fieldset>
		<legend>CDN设置</legend>
		<div class="form-group">
			<label class="control-label col-sm-3" for="Status">是否开启CDN</label>

			<div class="col-sm-9">
				<select name="status" class="form-control" id="Status">
					<?php echo html_option([
						'0' => '关闭CDN',
						'1' => '启用CDN',
					], cdn_info('status')?'1':'0') ?></select>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-3" for="get_static_style_url">get_static_style_url</label>

			<div class="col-sm-9">
				<input type="text" name="list[get_static_style_url]" placeholder="静态样式地址" id="get_static_style_url" value="<?php echo cdn_info('filed','get_static_style_url')?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-3" for="get_bootstrap_url">get_bootstrap_url</label>

			<div class="col-sm-9">
				<input type="text" name="list[get_bootstrap_url]" placeholder="Bootstrap地址" id="get_bootstrap_url" value="<?php echo cdn_info('filed','get_bootstrap_url')?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-3" for="get_bootstrap_plugin_url">get_bootstrap_plugin_url</label>

			<div class="col-sm-9">
				<input type="text" name="list[get_bootstrap_plugin_url]" placeholder="Bootstrap插件地址" id="get_bootstrap_plugin_url" value="<?php echo cdn_info('filed','get_bootstrap_plugin_url')?>"
					   class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-3" for="get_js_url">get_js_url</label>

			<div class="col-sm-9">
				<input type="text" name="list[get_js_url]" placeholder="JS地址" id="get_js_url" value="<?php echo cdn_info('filed','get_js_url')?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-9 col-sm-offset-3">
				<button class="btn btn-primary" type="submit">修改</button>
			</div>
		</div>
	</fieldset>
</form>
<script>
	$(function () {
		$("form").ajaxForm(function (data) {
			if (data['status']) {
				alert_notice("更新成功");
			} else {
				alert_error(data['msg'], "更新失败");
			}
		});
	});
</script>