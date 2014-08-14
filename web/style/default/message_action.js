/**
 * Created by huzhiyu on 14-3-28.
 */
var markdown_edit = null;
$(function () {
	$("#MessageSendForm").ajaxForm(function (data) {
		if (data['status']) {
			modal_show("<span class='text-warning'>发送状态！</span>",
				"<div><p class='text-success'>成功发送给：" + data['content']['ok'] + " 个用户</p>" +
					(data['content']['error'] > 0 ? "<p class='text-danger'>" + data['content']['error'] + " 个用户发送失败</p>" : "") +
					"<p>详情查看发信箱。</p></div>"
			);
			markdown_edit.setContent("");
		} else {
			alert_error(data['msg'], "发送错误！");
		}
	});
	if (typeof $.fn.markdown != 'undefined') {
		$("#InputContent").markdown({
			onShow: function (e) {
				markdown_edit = e;
			}, onPreview: function (e) {
				var previewContent;
				var originalContent = e.getContent();
				var status = $.ajaxSettings.async;
				$.ajaxSetup({
					async: false
				});
				$.post(SITE_URL + "UserApi/markdown", {content: originalContent}, function (data) {
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
			}
		});
	}
});
function message_read(id) {
	$.get(SITE_URL + "UserApi/message_read", {id: id}, function (data) {
		if (data['status']) {
			modal_show(data['content']['msg_title'] ? data['content']['msg_title'] : "未定义标题", data['content']['msg_content'], {
				type: "shown", call: function () {
					$("#Box_tr_" + id).removeClass("success");
					$("#Box_tr_" + id + " .set_read").remove();
				}
			});
		} else {
			alert_error(data['msg'], "获取内容失败！");
		}
	});
	return false;
}

function message_set_read_flag(id) {
	$.post(SITE_URL + "UserApi/message_read_flag", {id: id}, function (data) {
		if (data['status']) {
			alert_notice("标记为已读成功！");
			$("#Box_tr_" + id).removeClass("success");
			$("#Box_tr_" + id + " .set_read").remove();
		} else {
			alert_error(data['msg'], "标记“" + id + "”为已读失败！");
			setTimeout(function () {
				location.reload();
			}, 2000);
		}
	});
	return false;
}

function message_delete(id) {
	$.post(SITE_URL + "UserApi/message_delete", {id: id}, function (data) {
		if (data['status']) {
			$("#Box_tr_" + id).hide('slow', function () {
				$(this).remove();
			});
		} else {
			alert_error(data['msg'], "删除失败！");
		}
	});
	return false;
}
