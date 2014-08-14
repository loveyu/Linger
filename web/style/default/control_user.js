/**
 * Created by huzhiyu on 14-2-16.
 */
//@ sourceURL=Control_user
function Control_user(API_URL, SELECT, NUMBER) {
	this.API_URL = API_URL;
	this.SELECT = SELECT;
	this.NUMBER = NUMBER;
	this.NOW_PAGE = 1;
	this.NAV_CALLBACK = null;
	this.UserData = [];
	this.Search_type = '';
	this.Search_value = '';
	this.ORDER = 'id';
	var cu = this;
	$(window).bind('hashchange', function () {
		cu.hash_analysis();
	});
}
Control_user.prototype = {
	hash_analysis: function () {
		var hash = location.hash;
		var i = hash.indexOf("?");
		if (i >= 0) {
			hash = hash.substr(i + 1);
		} else {
			hash = "";
		}
		matches = hash.match(/order=([a-z]+)/);
		if (matches !== null) {
			this.ORDER = matches[1];
		} else {
			this.ORDER = 'id';
		}
		var matches = hash.match(/page=([\d]+)/);
		var page = 1;
		if (matches !== null) {
			page = +matches[1];
		}
		if (page < 1) {
			page = 1;
		}
		this.NOW_PAGE = page;
		matches = hash.match(/number=([\d]+)/);
		if (matches !== null) {
			page = +matches[1];
		} else {
			page = 20;
		}
		if (page < 1) {
			page = 20;
		}
		this.NUMBER = page;

		matches = hash.match(/type=([a-z]+)/);
		if (matches !== null) {
			this.Search_type = matches[1];
		} else {
			this.Search_type = '';
		}
		matches = hash.match(/value=([\s\S^&^=]+)/);
		if (matches !== null) {
			this.Search_value = matches[1];
		} else {
			this.Search_value = '';
		}

		var cu = this;
		$.get(this.API_URL, {order: this.ORDER, page: this.NOW_PAGE, number: this.NUMBER,
			search: this.Search_value, search_type: this.Search_type, refresh_string: time_string()}, function (data) {
			if (data['status']) {
				cu.pagination = cu.NAV_CALLBACK(data['content']['info']);
				cu.load_data(data['content']['data']);
			} else {
				alert_error(data['msg'], "用户加载出错");
			}
		});
	},
	load: function (page_callback) {
		this.NAV_CALLBACK = page_callback;
		this.hash_analysis();
	},
	load_data: function (data) {
		var obj = $(this.SELECT + " tbody");
		obj.html("");
		this.UserData = data;
		for (var i = 0, l = data.length; i < l; ++i) {
			obj.append("<tr id='user_table_list_id_" + i + "'>" +
				"<td class='avatar'><img src='" + data[i]['avatar'] + "'></td>" +
				"<td>" + data[i]['id'] + "</td><td>" + data[i]['name'] + "</td><td>" + data[i]['aliases'] + "</td>" +
				"<td>" + data[i]['email'] + "</td><td>" + (data[i]['url'] !== null ? data[i]['url'] : '') + "</td>" +
				"<td class='user_status'><button class='btn btn-warning'>" + this.get_status_info(data[i]['status']) + "</button></td>" +
				"<td>" + data[i]['last_login_time'] + "<br />IP:" + data[i]['last_login_ip'] + "</td>" +
				"<td><button class='btn btn-primary user_more_info_class'>更多</button>" +
				"<a  class='btn btn-danger' href='#user_edit/" + data[i]['id'] + "'>编辑</a>" +
				"<a  class='btn btn-danger' href='#user_permission/" + data[i]['id'] + "'>权限</a>" +
				"<button class='btn-default btn user_meta_info_class'>标签</button></td></tr>");
		}
		var cu = this;
		$("td button.user_more_info_class").click(function () {
			var id = $($(this).parent()).parent()[0].id.substr(19);
			var data = cu.UserData[id];
			var html = "<table class='table table-striped' style='table-layout:inherit;'>";
			for (var param in data) {
				if (data.hasOwnProperty(param)) {
					html += "<tr><td style='width: 30%'>" + param + "</td><td style='width: 70%;word-wrap: break-word; word-break: break-all;'>" + cu.encodeHtml(data[param]) + "</td></tr>";
				}
			}
			html += "</table>";
			cu.show_user_modal("更多用户信息", html);
		});
		$("td button.user_meta_info_class").click(function () {
			var id = $($(this).parent()).parent()[0].id.substr(19);
			$.get(API_URL + "/get_user_meta", {id: cu.UserData[id]['id']}, function (data) {
				if (data['status']) {
					var html = "<table class='table table-striped' style='table-layout: fixed;'>";
					for (var param in data['content']) {
						if (data['content'].hasOwnProperty(param)) {
							html += "<tr><td style='width: 20%;'>" + param + "</td><td>";
							for (var i = 0, l = data['content'][param].length; i < l; i++) {
								html += "<pre style='width: 100%;overflow-y: scroll;'>" + data["content"][param][i] + "</pre>"
							}
							html + "</td></tr>";
						}
					}
					html += "</table>";
					cu.show_user_modal("用户标签信息", html);
				} else {
					alert_error("用户标签列表获取失败", data['msg']);
				}
			});
		});
		$("td.user_status button").click(function () {
			var id = $($(this).parent()).parent()[0].id.substr(19);
			var user_id = +cu.UserData[id]['id'];
			var user_status = +cu.UserData[id]['status'];
			var html = '<div class="btn-group" id="user_info_status_change" data-toggle="buttons">' +
				'<label class="btn btn-primary' + (user_status === 0 ? " active" : "") + '">' +
				'<input type="radio" id="option1" value="0">未验证' +
				'</label>' +
				'<label class="btn btn-primary' + (user_status === 1 ? " active" : "") + '">' +
				'<input type="radio" id="option2" value="1">正常用户' +
				'</label>' +
				'<label class="btn btn-primary' + (user_status === 2 ? " active" : "") + '">' +
				'<input type="radio" id="option3" value="2">限制用户' +
				'</label>' +
				'<label class="btn btn-primary' + (user_status === 3 ? " active" : "") + '">' +
				'<input type="radio" id="option3" value="3">锁定用户' +
				'</label>' +
				'</div><div style="margin-top: 10px;"><p>' +
				'<button id="Control_user_send_activation_mail" class="btn btn-danger' + (user_status === 0 ? "" : " disabled") + '">发送激活邮件</button>' +
				'</p></div>';
			cu.show_user_modal("切换用户状态", html, [
				{type: 'shown', call: function () {
					$("#Control_user_send_activation_mail").click(function () {
						$.post(API_URL + "/send_activation_mail", {id: user_id}, function (data) {
							if (data['status']) {
								alert_notice("成功发送激活邮件");
							} else {
								alert_error(data['msg'], "邮件发送失败");
							}
						});
					});
				}},
				{type: 'hide', call: function () {
					var s = $("#user_info_status_change .active input:radio");
					var new_status = user_status;
					if (s.length === 1) {
						new_status = +s[0].value;
					}
					if (new_status != user_status && (new_status >= 0 && new_status <= 3)) {
						$.post(API_URL + "/user_change_status", {id: user_id, status: new_status}, function (data) {
							if (data['status']) {
								alert_notice("状态修改成功");
								cu.hash_analysis();
							} else {
								alert_error(data['msg'], "更新出错");
							}
						});
					}
				}}
			]);
		});
		this.pagination.load();
	},
	pagination: null,
	get_status_info: function (code) {
		code = +code;
		switch (code) {
			case 0:
				return "未验证";
			case 1:
				return "正常";
			case 2:
				return "被限制";
			case 3:
				return "被锁定";
			default:
				return "未知";
		}
	},
	get_nav_url: function (page_str) {
		return "#user?order=" + this.ORDER + "&page=" + page_str + "&number=" + this.NUMBER + "&type=" + this.Search_type + "&value=" + this.Search_value;
	},
	show_user_modal: function (title, content, callback) {
		modal_show(title, content, callback);
	},
	encodeHtml: function (s) {
		return (typeof s != "string") ? s :
			s.replace(this.REGX_HTML_ENCODE,
				function ($0) {
					var c = $0.charCodeAt(0), r = ["&#"];
					c = (c == 0x20) ? 0xA0 : c;
					r.push(c);
					r.push(";");
					return r.join("");
				});
	},
	REGX_HTML_ENCODE: /"|&|'|<|>|[\x00-\x20]|[\x7F-\xFF]|[\u0100-\u2700]/g
}
;