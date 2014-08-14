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

function file_size(size, save) {
	var a = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
	var pos = 0;
	if (size < 0)size = 0;
	while (size > 1024) {
		size /= 1024;
		pos++;
	}
	return decimal(size, typeof save === 'undefined' ? 2 : save) + a[pos];
}

function decimal(num, v) {
	var vv = Math.pow(10, v);
	return Math.round(num * vv) / vv;
}

$(function () {
	$(".user_menu .title").click(function () {
		$(".user_menu .title span").removeClass("glyphicon-chevron-up").addClass("glyphicon-chevron-down");
		if ($(this).parent().find(".sub").is(":hidden")) {
			$(".user_menu .sub").hide();
			$(this).parent().find(".sub").show(300,function(){
				$(this).parent().find("span").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-up");
			});
		} else {
			$(this).parent().find(".sub").hide(300);
		}
	});
	$(".user_menu .active .title span").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-up");
});