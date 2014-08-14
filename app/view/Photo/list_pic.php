<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">图片管理</h2>
	</div>
	<div class="panel-body">
		<?php
		/**
		 * @var bool|string $__error
		 * @var int         $__now
		 * @var int         $__max
		 * @var int         $__count
		 * @var int         $__number
		 * @var array       $__list
		 */
		if($__error !== false): ?>
			<h2 class="text-danger">加载错误：<?php echo $__error ?></h2>

		<?php else: ?>
			<div class="well well-sm clearfix">
				<a href="<?php echo get_url("Photo", "add_pic") ?>" style="margin-right: 10px;" class="btn btn-primary pull-left">添加新图片</a>
				<?php if($__max > 1): ?>
					<ul class="pagination" style="display: inline;"><?php echo theme()->createNav($__now, $__max, $__count, get_url("Photo", 'list_pic') . "?page={number}&number=" . $__number); ?></ul>
				<?php endif; ?>
				<button id="Picture_delete" class="btn btn-danger btn-sm pull-right" type="button">删除选中</button>
			</div>
		<?php if (count($__list) < 1): ?>
			<h2 class="text-danger">当前页面不存在</h2>
		<?php else: ?>
			<table id="Pictures_list" class="table table-striped table-hover">
				<thead>
				<tr>
					<th style="width: 10%">图片</th>
					<th>内容</th>
					<th class="text-right" style="width: 4em"><input name="check_all" type="checkbox"></th>
				</tr>
				</thead>
				<tbody>
				<?php $status = [
					'0' => '关闭状态',
					'1' => '正常分享和评论'
				];
				foreach($__list as $v): ?>
					<tr>
						<td><a class="image_link" href="<?php echo get_url("Photo", "edit_pic") ?>?id=<?php echo $v['pic_id'] ?>">
								<img class="img-thumbnail" src="<?php echo $v['pic_thumbnails_url'] ?>" alt="<?php echo $v['pic_id'] ?>"></a></td>
						<td class="content">
							<p>名称：<span class="text-info"><?php echo $v['pic_name'] ?></span></p>

							<p>描述：<span class="text-info"><?php echo $v['pic_description'] ?></span></p>


							<p>状态：<span class="text-info"><?php echo $status[$v['pic_status']] ?></span>&nbsp;&nbsp;&nbsp;评论数：<span class="text-info"><?php echo $v['pic_comment_count'] ?></span></p>


							<p>查看图片：<?php $link = picture_link($v['pic_id']); ?><a href="<?php echo $link; ?>"><?php echo $link; ?></a></p>

							<p>创建时间：<span class="text-info"><?php echo $v['pic_create_time'] ?></span> 标签：<?php if(count($v['pic_tags']) > 0){
									echo "<span class='label label-info'>", implode("</span><span class='label label-info'>", $v['pic_tags']), "</span>";
								} else{
									echo "无";
								} ?></p>

						</td>
						<td class="text-right"><input type="checkbox" name="pictures_id_checked[]" value="<?php echo $v['pic_id']; ?>"></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<script>
				$(function () {
					$("input[name='check_all']").click(function () {
						if (this.checked) {
							$("input[name='pictures_id_checked[]']").attr("checked", "true");
							$("input[name='pictures_id_checked[]']").prop("checked", true);
						} else {
							$("input[name='pictures_id_checked[]']").removeAttr("checked");
							$("input[name='pictures_id_checked[]']").prop("checked", false);
						}
					});
					$("tbody tr td.content").click(function () {
						var s = $(this).parent().find("[name='pictures_id_checked[]']")[0];
						if (!s.checked) {
							$(s).attr("checked", "true");
							$(s).prop("checked", true);
						} else {
							$(s).removeAttr("checked");
							$(s).prop("checked", false);
						}
					});
					$("#Picture_delete").click(function () {
						var ids = [];
						$("input[name='pictures_id_checked[]']:checked").each(function () {
							ids.push(this.value);
						})
						if (ids.length > 0) {
							if (confirm("你确定删除ID为 " + ids + " 的图片？")) {
								$.post('<?php echo get_url("UserApi","picture_more_delete")?>', {id: ids.join(",")}, function (data) {
									if (data['status']) {
										alert_notice("删除成功");
										setTimeout(function () {
											location.reload();
										}, 1000);
									} else {
										alert_error(data['msg'], "删除失败");
										var n = 0 + data['content'];
										if (n > 0 && n !== ids.length) {
											alert_error("你删除了:" + (ids.length - n) + " 张图片,2秒后刷新", "删除出错");
											setTimeout(function () {
												location.reload();
											}, 2000);
										}
									}
								});
							}
						} else {
							alert_error("你没选中任何内容。");
						}
						$("input[type=checkbox]").removeAttr("checked");
						$("input[type=checkbox]").prop("checked", false);
					});
				});
			</script>
		<?php endif; ?>
		<?php endif; ?>
		<?php //var_dump($__list) ?>
	</div>
</div>