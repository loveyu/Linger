<?php
/**
 * @var array $__list
 */
?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">图集管理</h2>
	</div>
	<div class="panel-body">
		<div class="well well-sm clearfix">
			<a href="<?php echo get_url("Photo", "add_gallery") ?>" style="margin-right: 10px;" class="btn btn-primary pull-left">添加新图集</a>
			<?php if($__list['max'] > 1): ?>
				<ul class="pagination"
					style="display: inline;"><?php echo theme()->createNav($__list['page'], $__list['max'], $__list['count'], get_url("Photo", 'list_gallery') . "?page={number}&number=" . $__list['number']); ?></ul>
			<?php endif; ?>
			<button id="Gallery_delete" class="btn btn-danger btn-sm pull-right" type="button">删除选中</button>
		</div>
		<?php if($__list['found']): ?>
			<table id="Gallery_list" class="table table-striped table-hover">
				<thead>
				<tr>
					<th>标题</th>
					<th><span class="glyphicon glyphicon-time"></span>创建时间</th>
					<th><span class="glyphicon glyphicon-tags"></span>标签</th>
					<th><span class="glyphicon glyphicon-comment"></span>评论数</th>
					<th class="text-danger"><span class="glyphicon glyphicon-heart"></span>喜欢</th>
					<th>状态</th>
					<th>查看</th>
					<th class="text-right" style="width: 2em"><input name="check_all" type="checkbox"></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach($__list['gallery'] as $v): ?>
					<tr>
						<td>[<?php echo $v['gallery_id'] ?>]<a href="<?php echo get_url("Photo", "edit_gallery"), "?id=", $v['gallery_id']; ?>">
								<span class="glyphicon glyphicon-edit"></span><?php echo $v['gallery_title'] ?></a></td>
						<td><?php echo $v['gallery_create_time'] ?></td>
						<td><?php if(count($v['tags'])){
								echo "<span>", implode("</span>, <span>", $v['tags']), "</span>";
							} ?></td>
						<td><?php echo $v['gallery_comment_count'] ?></td>
						<td><?php echo $v['gallery_like_count'] ?></td>
						<td><?php echo $v['gallery_status'] ? "已发布" : "草稿"; ?></td>
						<td><a href="<?php echo gallery_link($v['gallery_id']) ?>" class="glyphicon glyphicon-eye-open"></a></td>
						<td class="text-right"><input type="checkbox" name="gallery_id_checked[]" value="<?php echo $v['gallery_id']; ?>"></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<script>
				$(function () {
					$("input[name='check_all']").click(function () {
						if (this.checked) {
							$("input[name='gallery_id_checked[]']").attr("checked", "true");
							$("input[name='gallery_id_checked[]']").prop("checked", true);
						} else {
							$("input[name='gallery_id_checked[]']").removeAttr("checked");
							$("input[name='gallery_id_checked[]']").prop("checked", false);
						}
					});
					$("#Gallery_delete").click(function () {
						var ids = [];
						$("input[name='gallery_id_checked[]']:checked").each(function () {
							ids.push(this.value);
						})
						if (ids.length > 0) {
							if (confirm("你确定删除ID为 " + ids + " 的图集？")) {
								$.post('<?php echo get_url("UserApi","gallery_delete")?>', {id: ids.join(",")}, function (data) {
									if (data['status']) {
										alert_notice("删除成功");
										setTimeout(function () {
											location.reload();
										}, 1000);
									} else {
										alert_error(data['msg'], "删除失败");
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
		<?php else: ?>
			<h3 class="text-danger">当前页内容未找到,或者没有内容</h3>
		<?php endif; ?>
	</div>
</div>