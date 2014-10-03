/**
 * Created by huzhiyu on 14-2-15.
 */

var page_url_hash = '';

function alert_msg(msg) {
	alert(msg);
}

function alert_notice(msg) {
	$.pnotify({
		text: msg,
		type: 'info'
	});
}
function alert_error(msg, title) {
	if (typeof title != 'string') {
		title = "出错了";
	}
	if (msg == '') {
		msg = "未知错误";
	}
	$.pnotify({
		title: title,
		text: msg,
		type: 'error'
	});
}

if (typeof API_URL == 'undefined') {
	API_URL = '';
}

function time_string() {
	var date = new Date();
	return date.getMinutes() + "" + date.getSeconds() + "" + date.getMilliseconds();
}

function page_analysis(page) {
	if (location.hash != '') {
		page = location.hash.substr(1);
	}
	var i = page.indexOf("?");
	if (i !== -1) {
		page = page.substr(0, i);
	}
	$.pnotify_remove_all();
	if (page != page_url_hash) {
		page_load(page);
		page_url_hash = page;
	}
}

function page_load(page) {
	if (typeof page != 'string' || page == '') {
		alert_error("请求的页面不存在");
	} else {
		$("#page_content_load").load(location.pathname + "/" + page + "?refresh=" + time_string(), function (data, status, xhr) {
			if (status == "error") {
				alert_error("页面请求错误，请尝试其他页面");
			} else {
			}
		});
	}
}

function load_menu_add(data, ID) {
	$.each(data, function (index, elem) {
		$("#" + ID).append("<li id='" + ID + '_' + elem['id'] + "'><a  href='" + elem['url'] + "'>" + elem['name'] + "</a></li>");
		if (elem['sub'].length > 0) {
			$("#" + ID + '_' + elem['id']).append("<div class='control_sub nav nav-pills nav-stacked'id='" + ID + '_' + elem['id'] + "_sub'></div>");
			load_menu_add(elem['sub'], ID + '_' + elem['id'] + "_sub");
		}
	});
}

function load_menu(select, api_url) {
	$.get(api_url, {}, function (data) {
		if (!data['status']) {
			alert_error("菜单加载错误");
		} else {
			$(select).append("<div class='nav nav-pills nav-stacked'id='NAV_MENU_TOP'></div>");
			load_menu_add(data['content'], 'NAV_MENU_TOP');
			if (location.hash == '') {
				location.href = data['content'][0]['url'];
			} else {
				page_analysis('______');
			}
			$("#NAV_MENU_TOP a").click(function () {
				$("#NAV_MENU_TOP li").removeClass("active");
				$(this).parent().addClass("active");
			});
		}
	});
	$(window).bind('hashchange', function () {
		page_analysis('______');
	});
}

function update_check() {
	$.get(API_URL + "/checkUpdate", function (data) {
		if (data.status && data.content != "") {
			update_show(data.content);
		}
	});
}

function update_show(version) {
	var obj = $("#Update_check_well");
	if (obj.length > 0) {
		obj.remove();
	}
	$("#page_content_load").before("<div style='display: none' class='well well-sm' id='Update_check_well'><a href=\"#check_update\" class='text-danger'>发现新版本 : " + version + "</a></div>");
	$("#Update_check_well").slideDown("fast");
}