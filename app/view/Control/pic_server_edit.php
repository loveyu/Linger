<?php if($__list === false): ?>
	<h2 class="text-danger">对应的数据未找到</h2>
<?php else: ?>
	<form class="form-horizontal" action="<?php echo get_url("UserControlApi", "pic_server_edit") ?>" method="post" style="max-width: 800px">
		<fieldset>
			<legend>更新图片服务器信息</legend>
			<div class="form-group">
				<label for="inputName" class="col-sm-2 control-label">名称</label>

				<div class="col-sm-10">
					<input type="text" class="form-control" id="inputName" placeholder="名称" value="<?php echo $__list['name'] ?>" name="name">
				</div>
			</div>

			<div class="form-group">
				<label for="inputUrl" class="col-sm-2 control-label">网址</label>

				<div class="col-sm-10">
					<input type="url" class="form-control" id="inputUrl" placeholder="网址" value="<?php echo $__list['url'] ?>" name="url">
				</div>
			</div>

			<div id="Pic_server_meta">
				<?php if(is_array($__list['meta'])):
					foreach($__list['meta'] as $key => $v):?>
						<div class="form-group">
							<label class="col-sm-2 control-label">标签<span class="glyphicon glyphicon-remove"></span></label>

							<div class="col-sm-3">
								<input type="text" class="form-control" name="meta_key[]" placeholder="名称" value="<?php echo $key; ?>">
							</div>
							<div class="col-sm-7">
								<input type="text" class="form-control" name="meta_value[]" placeholder="值" value="<?php echo htmlspecialchars($v); ?>">
							</div>
						</div>
					<?php endforeach;
				endif; ?>
			</div>

			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<button type="submit" class="btn btn-primary">更新</button>
					<button type="button" class="pic_server_meta_add btn btn-default">添加一个标签</button>
				</div>
			</div>
		</fieldset>
	</form>
	<script>
		//@ sourceURL=Control_pic_server_edit
		$(function () {
			var remove_bind = function(){
				$(".glyphicon-remove").click(function(){
					$(this).parent().parent().remove();
				});
			};
			$(".pic_server_meta_add").click(function () {
				$("#Pic_server_meta").append('<div class="form-group">' +
					'<label class="col-sm-2 control-label">标签 <span class="glyphicon glyphicon-remove" style="cursor: pointer;"></span></label>' +
					'<div class="col-sm-3">' +
					'<input type="text" class="form-control" name="meta_key[]" value="" placeholder="名称">' +
					'</div>	<div class="col-sm-7">' +
					'<input type="text" class="form-control" name="meta_value[]" value="" placeholder="值">' +
					"</div>" +
					'</div>');
				remove_bind();
			});
			$("form").ajaxForm(function(data){
				if(data['status']){
					alert_notice("更新成功，3秒后刷新页面，查看最新信息。");
					setTimeout(function(){
						location.reload();
					},3000);
				}else{
					alert_error(data['msg']);
				}
			});
		});
	</script>
<?php endif; ?>