<h1>用户管理</h1>
<div id="Control_user_nav" class="row">
	<div class="col-md-6" id="user_list_page_nav">

	</div>
	<div class="col-md-6">
		<form id="Control_user_search" class="form-inline">
			<label for="Search_InputNumber">每页显示：</label>
			<input type="number" class="form-control" id="Search_InputNumber" placeholder="number" value="20">
			<label for="Search_InputSearchType">搜索类型:</label>
			<select class="form-control" id="Search_InputSearchType" name="search_type">
				<option value="id">ID序号</option>
				<option value="name">用户名</option>
				<option value="aliases">别名</option>
				<option value="email">邮箱</option>
				<option value="url">网址</option>
				<option value="status">状态[0,1,2,3]</option>
			</select>
			<label for="Search_InputSearchValue">搜索内容</label>
			<input id="Search_InputSearchValue" name="search_value" placeholder="关键字" value="" class="form-control">
			<button class="btn-default btn" type="submit">设置</button>
		</form>
	</div>
</div>
<table id="Control_user" class="table table-striped table-hover">
	<thead>
	<tr>
		<th class="avatar">头像</th>
		<th><a href="#id">ID</a></th>
		<th><a href="#name">用户名</a></th>
		<th><a href="#aliases">别名</a></th>
		<th><a href="#email">邮箱</a></th>
		<th>主页</th>
		<th>状态</th>
		<th>上次登录</th>
		<th>操作</th>
	</tr>
	</thead>
	<tbody>

	</tbody>
</table>
<script src="<?php echo get_style('control_user.js'); ?>"></script>
<script>
	//@ sourceURL=Control_user_html_page
	$(function () {
		var cu = new Control_user(API_URL + '/user_list', '#Control_user', 20);
		cu.load(function (data) {
			var pg = new Pagination("#user_list_page_nav");
			pg.set_page_nav_url(cu.get_nav_url(pg.get_page_param()));
			pg.load_param(1, data['page'] - 1, data['page'], 1 + data['page'], data['page_count']);
			return pg;
		});
		$("#Control_user_search").submit(function () {
			var n = +$("#Search_InputNumber").val();
			if (n < 1) {
				n = 1;
				$("#Search_InputNumber").value = n;
			}
			var st = (($("#Search_InputSearchType").val()));
			var sv = (($("#Search_InputSearchValue").val()));
			location.href = "#user?order=" + cu.ORDER + "&page=" + cu.NOW_PAGE + "&number=" + n + "&type=" + st + "&value=" + sv;
			return false;
		});
		$("#Control_user thead tr th a").click(function () {
			var name = this.href.substr(this.href.indexOf("#")+1);
			location.href = "#user?order=" + (name) + "&page=" + cu.NOW_PAGE + "&number=" + cu.NUMBER + "&type=" + cu.Search_type + "&value=" + cu.Search_value;
			return false;
		});
	});
</script>