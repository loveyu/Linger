<?php
/**
 * @var array $__data
 * @var array $__count
 */
?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">文章管理</h2>
	</div>
	<div class="panel-body">
		<?php if($__count['max'] > 1 || ($__count['page'] === -1 && $__count['count'] !== 0)): ?>
			<div class="well well-sm clearfix">
				<ul class="pagination" style="display: inline;">
					<?php echo theme()->createNav($__count['page'], $__count['max'], $__count['count'], get_url("Posts", 'management') . "?page={number}"); ?>
				</ul>
			</div>
		<?php endif;
		if($__count['count'] === 0): ?>
			<h3 class="text-danger">没有任何文章信息!</h3>
		<?php
		elseif ($__count['page'] === -1):?>
			<h3 class="text-danger">当前页面不存在！请返回上一页！</h3>
		<?php
		else: ?>
			<table class="table table-striped table-hover">
				<thead>
				<tr>
					<th>标题</th>
					<th>时间</th>
					<th>分类</th>
					<th>评论</th>
					<th>状态</th>
					<th>操作</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach($__data as $v): ?>
					<tr id="post_list_<?php echo $v['id'] ?>">
						<td>【<?php echo $v['id'] ?>】<a class="glyphicon glyphicon-eye-open" rel="external" href="<?php echo post_link($v['post_name']) ?>?preview=true"><?php echo $v['post_title'] ?></a></td>
						<td><?php echo $v['post_time'] ?></td>
						<td><?php echo $v['post_category'] ?></td>
						<td><?php echo $v['post_status'] ? "已发布" : "草稿" ?></td>
						<td><?php echo $v['post_comment_count'] ?></td>
						<td><a class="glyphicon glyphicon-edit" href="<?php echo get_url("Posts", "edit") ?>?id=<?php echo $v['id'] ?>">编辑</a>,<a class="text-danger glyphicon glyphicon-remove" href="#" onclick="return remove_post(<?php echo $v['id'] ?>)">删除</a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<script>
				function remove_post(id) {
					if (confirm("你确定删除ID为 `" + id + "` 的这篇文章？")) {
						$.post("<?php echo get_url("UserApi","post_delete")?>", {id: id}, function (data) {
							if (data) {
								alert_notice("删除成功");
								$("#post_list_" + id).hide("slow", function () {
									$(this).remove();
								});
							} else {
								alert_error(data['msg'], "删除失败！");
							}
						});
					}
					return false;
				}
			</script>
		<?php endif; ?>
	</div>
</div>