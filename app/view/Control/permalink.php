<?php
/**
 * @var ULib\Router $router
 */
$router = lib()->using('router');
?>
<form class="form-horizontal" action="<?php echo get_url("UserControlApi", "permalink") ?>" method="post" style="max-width: 800px">
	<fieldset>
		<legend>固定连接设置
			<small class="text-danger">谨慎修改，该操作会影响搜索引擎收录</small>
		</legend>
		<div class="well well-sm text-danger small">
			注意不要使用正则语法，否则会导致报错，将对参数做默认替换.如需自定义正则信息，使用%([0-9]+)%类似的语法，对于特殊字符会优先转义，如`.`。<br>
			%number% =&gt; 数字
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputPicture">图片页面</label>

			<div class="col-sm-10">
				<input type="text" name="picture" placeholder="" id="InputPicture" value="<?php echo $router->get('picture') ?>" class="form-control">

				<p class="help-block">包含一个数字参数,%number%作为图片ID</p>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputPicturePgaer">图片评论分页</label>

			<div class="col-sm-10">
				<input type="text" name="picture_pager" placeholder="" id="InputPicturePgaer" value="<?php echo $router->get('picture_pager') ?>"
					   class="form-control">

				<p class="help-block">包含两个数字参数,%number%，第一个为图片ID，第二个为评论页面</p>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputGallery">图集页面</label>

			<div class="col-sm-10">
				<input type="text" name="gallery" placeholder="" id="InputGallery" value="<?php echo $router->get('gallery') ?>" class="form-control">

				<p class="help-block">包含一个数字参数,%number%作为图集ID</p>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputGalleryPager">图集评论分页</label>

			<div class="col-sm-10">
				<input type="text" name="gallery_pager" placeholder="" id="InputGalleryPager" value="<?php echo $router->get('gallery_pager') ?>"
					   class="form-control">

				<p class="help-block">包含两个数字参数,%number%，第一个为图集ID，第二个为评论页面</p>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputUser">用户页面</label>

			<div class="col-sm-10">
				<input type="text" name="user" placeholder="" id="InputUser" value="<?php echo $router->get('user') ?>" class="form-control">

				<p class="help-block">包含一个参数,%user_name%，此为用户名的规则</p>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-sm-2" for="InputPost">文章页面</label>

			<div class="col-sm-10">
				<input type="text" name="post" placeholder="" id="InputPost" value="<?php echo $router->get('post') ?>" class="form-control">

				<p class="help-block">包含一个参数,%post_name%，该名称为文章唯一名称</p>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-sm-2" for="InputPostPager">文章页面评论分页</label>

			<div class="col-sm-10">
				<input type="text" name="post_pager" placeholder="" id="InputPostPager" value="<?php echo $router->get('post_pager') ?>"
					   class="form-control">

				<p class="help-block">包含两个参数,%post_name%，该名称为文章唯一名称，%number%为页面数字</p>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputPostList">文章列表页面</label>

			<div class="col-sm-10">
				<input type="text" name="post_list" placeholder="" id="InputPostList" value="<?php echo $router->get('post_list') ?>"
					   class="form-control">

				<p class="help-block">无参数</p>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputPostListPager">文章列表页面分页</label>

			<div class="col-sm-10">
				<input type="text" name="post_list_pager" placeholder="" id="InputPostListPager" value="<?php echo $router->get('post_list_pager') ?>"
					   class="form-control">

				<p class="help-block">包含一个参数,%number%为页面数字</p>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputTimeLine">时间线页面</label>

			<div class="col-sm-10">
				<input type="text" name="time_line" placeholder="" id="InputTimeLine" value="<?php echo $router->get('time_line') ?>"
					   class="form-control">

				<p class="help-block">无参数</p>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-sm-2" for="InputGalleryList">图集列表页面</label>

			<div class="col-sm-10">
				<input type="text" name="gallery_list" placeholder="" id="InputGalleryList" value="<?php echo $router->get('gallery_list') ?>"
					   class="form-control">

				<p class="help-block">无参数</p>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="InputGalleryListPager">图集列表分页</label>

			<div class="col-sm-10">
				<input type="text" name="gallery_list_pager" placeholder="" id="InputGalleryListPager"
					   value="<?php echo $router->get('gallery_list_pager') ?>" class="form-control">

				<p class="help-block">%number%，页面参数</p>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-sm-2" for="InputUserGalleryList">用户图集列表页面</label>

			<div class="col-sm-10">
				<input type="text" name="user_gallery_list" placeholder="" id="InputUserGalleryList"
					   value="<?php echo $router->get('user_gallery_list') ?>" class="form-control">

				<p class="help-block">%user_name%，用户名称</p>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-sm-2" for="InputUserGalleryListPager">用户图集列表分页</label>

			<div class="col-sm-10">
				<input type="text" name="user_gallery_list_pager" placeholder="" id="InputUserGalleryListPager"
					   value="<?php echo $router->get('user_gallery_list_pager') ?>" class="form-control">

				<p class="help-block">%user_name%，用户名称，%number%，页面参数</p>
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-6 col-sm-offset-2">
				<button class="btn btn-warning" type="submit">更新</button>
			</div>
			<div class="col-sm-4">
				<button class="btn btn-danger reset_setting" type="button">重置默认设置</button>
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
		$("button.reset_setting").click(function () {
			if (confirm("你确定重置固定链接选项吗？")) {
				$.post('<?php echo get_url("UserControlApi", "permalink_reset")?>', {time: time_string()}, function (data) {
					if (data['status']) {
						modal_show("重置成功", "关闭刷新界面", {type: 'hide', call: function () {
							location.reload();
						}});
					} else {
						alert_error(data['msg'], "重置失败");
					}
				});
			}
		});
	});
</script>