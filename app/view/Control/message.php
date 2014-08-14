<div id="message_page_nav"></div>
<table id="message_system" class="table table-hover">
	<thead>
	<tr>
		<th>ID</th>
		<th>标题</th>
		<th>用户</th>
		<th>已读</th>
		<th>状态</th>
		<th>时间</th>
		<th>阅读，<a href="#" onclick="return set_one_page_number();">设置数量</a></th>
	</tr>
	</thead>
	<tbody>

	</tbody>
</table>
<script>
	var PAGE_PAGE = 1;
	var PAGE_NUMBER = 50;
	function read_msg(id) {
		$.get(API_URL + "/message_read", {id: id}, function (data) {
			if (data['status']) {
				modal_show(data['content']['msg_title'], data['content']['msg_content']);
			} else {
				alert_error(data['msg'], "读取消息内容错误");
			}
		});
		return false;
	}
	function set_one_page_number() {
		var sStr = prompt("输入每页显示的数量，最多100", "" + PAGE_NUMBER);
		if (sStr != null && sStr != "") {
			sStr = +sStr;
			if (sStr > 100) {
				sStr = 100;
			} else if (sStr < 10) {
				sStr = 10;
			}
			PAGE_NUMBER = sStr;
			alert_notice("当前设置每页显示：" + PAGE_NUMBER + " 条");
			get_data();
		} else {
			alert_error("设置无效");
		}
		return false;
	}
	function remove_msg(id) {
		if (confirm("确定移除？该操作会从数据库中删除该信息,未读的信息也会删除！")) {
			$.post(API_URL + "/message_del", {id: id}, function (data) {
				if (data['status']) {
					$("tr#message_id_" + id).hide("slow", function () {
						$(this).remove();
					});
				} else {
					alert_error(data['msg'], "移除消息失败");
				}
			});
		}
		return false;
	}
	function get_data() {
		$.get(API_URL + "/message_system", {page: PAGE_PAGE, number: PAGE_NUMBER}, function (data) {
			if (data['status']) {
				$("#message_system tbody").html("");
				for (var i = 0, l = data['content']['data'].length; i < l; ++i) {
					$("#message_system tbody").append("<tr id='message_id_" +
						data['content']['data'][i]['msg_id'] + "'><td>" + data['content']['data'][i]['msg_id'] + "</td><td>" +
						data['content']['data'][i]['msg_title'] + "</td><td>" +
						"<a href='<?php echo get_url('Tool','go_user')?>?name=" + encodeURIComponent(data['content']['data'][i]['user_name']) +
						"'>" + data['content']['data'][i]['user_aliases'] + "</a></td><td>" +
						(data['content']['data'][i]['msg_is_read'] == 1 ? "已读" : "<span class='text-danger'>未读</span>") + "</td><td>" +
						(data['content']['data'][i]['msg_to_del'] == 1 ? "<span class='text-danger'>已删</span>" : "未删") + "</td><td>" +
						data['content']['data'][i]['msg_datetime'] + "</td><td>" +
						"<a href='#' onclick='return read_msg(" + data['content']['data'][i]['msg_id'] + ")'>阅读</a>,<a class='text-danger' href='#' onclick='return remove_msg(" +
						data['content']['data'][i]['msg_id'] + ")'>移除</a> </td></tr>");
				}
				pg.load_param(1, data['content']['page']['page'] - 1, data['content']['page']['page'],
					1 + data['content']['page']['page'], data['content']['page']['max']);
				pg.load();
				$("#message_page_nav a").click(page_click);
			} else {
				alert_error(data['msg'], "加载列表出错");
			}
		});
	}
	var pg = new Pagination("#message_page_nav");
	function page_click(){
		var i = this.href.indexOf('#');
		PAGE_PAGE = +this.href.substr(i + 1);
		get_data();
		return false;
	}
	$(function () {
		pg.set_page_nav_url("#" + pg.get_page_param());
		get_data();
	});
</script>