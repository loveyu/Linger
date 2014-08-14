<form class="form-horizontal" action="<?php echo get_url("UserControlApi", "thumbnail") ?>" method="post" style="max-width: 800px">
	<fieldset>
		<legend>缩略图大小设置</legend>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputThumbnailWidth">默认缩略图宽</label>
			<div class="col-sm-10">
				<input type="number" name="image_thumbnail_width" placeholder="默认宽度400" id="InputThumbnailWidth" value="<?php echo image_thumbnail_width();?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputThumbnailHeight">默认缩略高度</label>
			<div class="col-sm-10">
				<input type="number" name="image_thumbnail_height" placeholder="默认宽度300" id="InputThumbnailHeight" value="<?php echo image_thumbnail_height();?>" class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputHdWidth">默认高清图宽</label>
			<div class="col-sm-10">
				<input type="number" name="image_hd_width" placeholder="默认宽度1600" id="InputHdWidth" value="<?php echo image_hd_width();?>" class="form-control">
				<p class="help-block">这里关于高清图只有一个选项，就是最小宽度，根据最小宽度生成高清图。</p>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputDisplayWidth">默认显示图宽</label>
			<div class="col-sm-10">
				<input type="number" name="image_display_width" placeholder="默认宽度900" id="InputDisplayWidth" value="<?php echo image_display_width();?>" class="form-control">
				<p class="help-block">和高清图类似，依据该宽度生成默认大小图片，小于此尺寸不做修改。</p>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-10 col-sm-offset-2">
				<button class="btn btn-primary" type="submit">修改</button>
			</div>
		</div>
	</fieldset>
</form>
<script>
	$(function(){
		$("form").ajaxForm(function(data){
			if(data['status']){
				alert_notice("更新成功");
			}else{
				alert_error(data['msg'],"更新失败");
			}
		});
	});
</script>