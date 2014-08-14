<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">编辑图片信息</h2>
	</div>
	<div id="Edit_pic" class="panel-body">
		<?php
		/**
		 * @var array $__list
		 */
		foreach($__list as $data):if(empty($data)){
			continue;
		} ?>
			<div id="Pic_edit_<?php echo $data['pic_id'] ?>" class="pic_edit_list row">
				<div class="thumbnail_img col-md-5">
					<a href="<?php echo picture_link($data['pic_id']); ?>"><img class="thumbnail" src="<?php echo $data['pic_thumbnails_url'] ?>" alt="thumbnail"></a>

					<p class="well well-sm">
						<span class="label label-danger">ID:<?php echo $data['pic_id'] ?></span>
						<a class="label label-primary" href="<?php echo $data['pic_display_url'] ?>">默认图</a>
						<a class="label label-primary" href="<?php echo $data['pic_hd_url'] ?>">高清图</a>
						<a class="label label-primary" href="<?php echo $data['pic_url'] ?>">原图</a>
					</p>
				</div>
				<div class="col-md-7">
					<form class="form-horizontal" action="<?php echo get_url("UserApi", "picture_edit_info"); ?>" method="post">
						<div class="form-group">
							<label class="col-sm-2 control-label">名称：</label>

							<div class="col-sm-10">
								<input type="text" name="name" class="form-control" value="<?php echo $data['pic_name'] ?>"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">标签:</label>

							<div class="col-sm-7">
								<input class="form-control" onkeypress="if(event.keyCode==13) {picture_add_tag(<?php echo $data['pic_id'] ?>);return false;}"
								       type="text" id="Pic_add_tag_<?php echo $data['pic_id'] ?>" placeholder="添加标签">

								<p class="help-block">当前标签:<?php foreach($data['pic_tags'] as $tag){ ?>
										<span class="label label-info"><?php echo $tag ?>
											<span onclick="picture_remove_tag(<?php echo $data['pic_id'], ",'", $tag, "'"; ?>,this)" style="cursor: pointer;"
											      class="glyphicon glyphicon-remove"></span></span>
									<?php } ?></p>
							</div>
							<div class="col-sm-3">
								<button class="btn btn-info" onclick="picture_add_tag(<?php echo $data['pic_id'] ?>);" type="button">添加</button>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">描述</label>

							<div class="col-sm-10">
								<textarea rows="3" name="desc" class="form-control"><?php echo $data['pic_description'] ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">图片状态</label>

							<div class="col-sm-10">
								<select name="status" class="form-control">
									<?php echo html_option([
										'0' => '关闭状态',
										'1' => '正常分享状态'
									], $data['pic_status']); ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">评论数</label>

							<div class="col-sm-10">
								<input class="form-control" type="number" readonly value="<?php echo $data['pic_comment_count'] ?>"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">操作</label>

							<div class="col-sm-10">
								<button class="btn btn-primary" type="submit">更新信息</button>
								<button class="btn btn-danger col-sm-offset-1" type="button" onclick="picture_delete(<?php echo $data['pic_id']; ?>);">删除该图片</button>
							</div>
						</div>
						<input name="pic_id" value="<?php echo $data['pic_id'] ?>" type="hidden">
					</form>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<script>
	$(function () {
		$("form").ajaxForm(function (data) {
			if (data['status']) {
				alert_notice("更新成功");
			} else {
				alert_error(data['msg'], "更新信息出错");
			}
		});
	});
	function picture_add_tag(pic_id) {
		var select = $("#Pic_add_tag_" + pic_id);
		var tag = select.val();
		if (tag.length > 0) {
			$.post('<?php echo get_url("UserApi","picture_add_tag")?>', {id: pic_id, tag: tag}, function (data) {
				if (data['status']) {
					alert_notice("标签添加成功");
					select.val("");
					select = select.parent().find(".help-block");
					for (var i in data['content']) {
						var tag_name = data['content'][i];
						select.append('<span class="label label-info">' + tag_name +
							'<span onclick="picture_remove_tag(' + pic_id + ',\'' + tag_name + '\',this)" style="cursor: pointer;" class="glyphicon glyphicon-remove"></span></span>');
					}
				} else {
					alert_error(data['msg'], '添加标签失败');
				}
			});
		}
	}
	function picture_delete(pic_id) {
		if (confirm("你确定删除ID为 " + pic_id + " 的图片?")) {
			$.post('<?php echo get_url("UserApi","picture_delete")?>', {id: pic_id}, function (data) {
				if (data['status']) {
					alert_notice("删除成功");
					$("#Pic_edit_" + pic_id).hide("slow", function () {
						$(this).remove();
						var s2 = $(".pic_edit_list");
						if (s2.length < 1) {
							location.href = '<?php echo get_url("Photo")?>';
						}
					});
				} else {
					alert_error(data['msg']);
				}
			});
		}
	}
	function picture_remove_tag(id, tag, elem) {
		$.post('<?php echo get_url("UserApi","picture_remove_tag")?>', {id: id, tag: tag}, function (data) {
			if (data['status']) {
				alert_notice("删除[" + tag + "]标签成功");
				$(elem).parent().remove();
			} else {
				alert_error(data['msg']);
			}
		});
	}
</script>