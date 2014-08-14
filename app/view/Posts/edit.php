<?php
/**
 * @var \ULib\Post $__post
 * @var array      $__info
 */
?>
<form id="Edit_form" action="<?php echo get_url("UserApi", "post_edit") ?>" method="post">
	<div class="row">
		<div class="col-sm-9">
			<div class="form-group">
				<label class="control-label" for="InputTitle">标题</label>
				<input id="InputTitle" name="title" value="<?php echo $__info['post_title'] ?>" type="text" class="form-control input-lg">

				<p class="help-block">访问：<a rel="external" href="<?php echo post_link($__info['post_name']) ?>"><?php echo post_link($__info['post_name']) ?></a></p>
			</div>
			<div class="form-group">
				<label class="control-label" for="InputContent">内容</label>
				<textarea class="form-control" name="content" rows="10" id="InputContent"><?php echo $__info['post_content'] ?></textarea>
			</div>
			<div class="form-group">
				<label class="control-label" for="InputKeyword">关键字</label>
				<input type="text" class="form-control" name="keyword" value="<?php echo $__info['post_keyword'] ?>" id="InputKeyword">
			</div>
			<div class="form-group">
				<label class="control-label" for="InputDescription">摘要</label>
				<textarea class="form-control" id="InputDescription" rows="3" name="description"><?php echo $__info['post_description'] ?></textarea>
			</div>
		</div>
		<div class="col-sm-3">
			<div class="form-group">
				<label class="control-label" for="InputName">唯一名称</label>
				<input type="text" class="form-control" name="name" id="InputName" value="<?php echo $__info['post_name'] ?>">
			</div>
			<div class="form-group">
				<label class="control-label" for="InputCategory">分类</label>
				<select id="InputCategory" class="form-control" name="category">
					<?php echo html_option(array_combine($__post->getCategory(), $__post->getCategory()), $__info['post_category']); ?>
				</select>
			</div>
			<div class="form-group">
				<label class="control-label">发布时间</label>
				<input class="form-control" value="<?php echo $__info['post_time'] ?>" disabled>
			</div>
			<div class="form-group">
				<label class="control-label">更新时间</label>
				<input class="form-control" value="<?php echo $__info['post_update_time'] ?>" disabled>
			</div>
			<div class="form-group">
				<label class="control-label" for="InputStatus">状态</label>
				<select id="InputStatus" class="form-control" name="status">
					<?php echo html_option([
						'0' => '草稿',
						'1' => '已发布'
					], $__info['post_status']); ?>
				</select>
			</div>
			<div class="form-group">
				<label class="control-label" for="InputCommentStatus">评论状态</label>
				<select id="InputCommentStatus" class="form-control" name="allow_comment">
					<?php echo html_option([
						'0' => '不允许',
						'1' => '允许评论'
					], $__info['post_allow_comment']); ?>
				</select>
			</div>
			<button class="btn btn-primary">更新</button>
		</div>
	</div>
	<input type="hidden" name="id" value="<?php echo $__info['post_id'] ?>">
</form>
<script>
	$("#InputContent").markdown({onPreview: function (e) {
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
	$("#Edit_form").ajaxForm(function (data) {
		if (data['status']) {
			alert_notice("更新成功");
			setTimeout(function () {
				location.reload();
			}, 1000);
		} else {
			alert_error(data['msg'], "更新失败，请检查");
		}
	});
</script>