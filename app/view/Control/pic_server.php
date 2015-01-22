<h2>图片服务器列表</h2>
<table class="pic_server table-striped table">
	<thead>
	<tr>
		<th>名称</th>
		<th>地址</th>
		<th>操作</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($__list as $v): ?>
		<tr>
			<td><?php echo $v['name'] ?></td>
			<td><?php echo $v['url'] ?></td>
			<td>
				<a href="#pic_server_edit/<?php echo $v['name'] ?>" class="btn btn-warning">编辑</a>
				<button id="Server_delete_<?php echo $v['name'] ?>" class="server_class_delete btn btn-danger">删除</button>
				<?php if(picture_server() !== $v['name']): ?>
					<button id="Server_set_<?php echo $v['name'] ?>" class="server_class_set btn btn-danger">设置为主</button>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<form id="Pic_server_add" class="form-inline" method="post" action="<?php echo get_url("UserControlApi", "pic_server_add") ?>">
	<fieldset>
		<legend>创建一个新的服务器</legend>
		<div class="form-group">
			<label class="sr-only" for="InputName">名称:</label>
			<input class="form-control" id="InputName" name="name" placeholder="唯一名称标记">
		</div>
		<div class="form-group">
			<label class="sr-only" for="InputUrl">名称:</label>
			<input class="form-control" id="InputUrl" name="text" placeholder="根网址">
		</div>
		<button type="submit" class="btn btn-default">创建</button>
	</fieldset>
</form>
<script>
	$(function () {
		$("#Pic_server_add").ajaxForm(function (data) {
			if (data['status']) {
				alert_notice("创建成功");
				setTimeout(function () {
					location.reload();
				}, 3000);
			} else {
				alert_error(data['msg']);
			}
		});
		$(".server_class_set").click(function(){
			var name = this.id.substr(11);
			$.post(API_URL + "/pic_server_set", {name: name}, function (data) {
				if (data['status']) {
					alert_notice("设置成功");
					setTimeout(function () {
						location.reload();
					}, 2000);
				} else {
					alert_error(data['msg']);
				}
			});
		});
		$(".server_class_delete").click(function () {
			var name = this.id.substr(14);
			$.post(API_URL + "/pic_server_delete", {name: name}, function (data) {
				if (data['status']) {
					alert_notice("删除成功");
					setTimeout(function () {
						location.reload();
					}, 2000);
				} else {
					alert_error(data['msg']);
				}
			});
		});
	});
</script>