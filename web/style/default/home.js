if (typeof SITE_URL == 'undefined') {
	var SITE_URL = '';
}
if (typeof IS_LOGIN == 'undefined') {
	var IS_LOGIN = false;
}

function supports_html5_storage() {
	try {
		return 'localStorage' in window && window['localStorage'] !== null;
	} catch (e) {
		return false;
	}
}
function html5_file_upload() {
	return typeof(window.FileReader) != 'undefined';
}

function html5_supports() {
	return supports_html5_storage() && html5_file_upload();
}

function Cookie() {
}
function Storage() {
}
Storage.get = function (name) {
	if (localStorage.hasOwnProperty(name)) {
		return localStorage[name];
	} else {
		return null;
	}
};
Storage.set = function (name, value) {
	localStorage[name] = value;
};
Storage.del = function (name) {
	if (localStorage.hasOwnProperty(name)) {
		localStorage.removeItem(name);
	}
};
Cookie.set = function (name, value) {
	var Days = 1000;
	var exp = new Date();
	exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
	document.cookie = name + "=" + encodeURI(value) + ";expires=" + exp.toUTCString();
};
Cookie.get = function (name) {
	var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
	if (arr != null) return decodeURI(arr[2]);
	return null;
};
Cookie.del = function (name) {
	var exp = new Date();
	exp.setTime(exp.getTime() - 1);
	var cval = getCookie(name);
	if (cval != null) document.cookie = name + "=" + cval + ";expires=" + exp.toUTCString();
};
function modal_show(title, content, callback) {
	$("#commonModal").remove();
	$("body").append("<div id='commonModal' class='modal fade' tabindex='-1' role='dialog' aria-labelledby='commonModalMyModalLabel' aria-hidden='true'>" +
		'<div class="modal-dialog">' +
		'<div class="modal-content">' +
		'<div class="modal-header">' +
		'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
		'<h4 class= "modal-title" id="commonModalMyModalLabel" >' + title + '</h4 >' +
		'</div>	' +
		"<div class='modal-body'>" + content + "</div>" +
		' <div class="modal-footer">' +
		'<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>' +
		"</div></div></div></div>"
	);
	if ($.isArray(callback)) {
		for (var i = 0, l = callback.length; i < l; i++) {
			if (callback[i].hasOwnProperty('type') && callback[i].hasOwnProperty('call')) {
				$("#commonModal").on(callback[i]['type'] + ".bs.modal", callback[i]['call']);
			}
		}
	} else {
		if (typeof callback != 'undefined' && callback.hasOwnProperty('type') && callback.hasOwnProperty('call')) {
			$("#commonModal").on(callback['type'] + ".bs.modal", callback['call']);
		}
	}
	$('#commonModal').modal('show');
}
function views_add(type, id, time, callback, error_callback) {
	$(function () {
		if (typeof time !== 'number') {
			time = 5000;
		}
		setTimeout(function () {
			$.post(SITE_URL + "UserApi/count_views_add", {type: type, id: id}, function (data) {
				if (data['status']) {
					if (typeof callback === 'function') {
						callback(data['content']);
					}
				} else {
					if (typeof error_callback === 'function') {
						error_callback(data['msg']);
					}
				}
			});
		}, time);
	});
}
function link_out_link() {
	$("a").each(function (id, elem) {
		var parse = elem.href.match(/^(([a-z]+):\/\/)?([^\/\?#]+)\/*([^\?#]*)\??([^#]*)#?(\w*)$/i);
		if ((parse != null && parse.length > 3
			&& parse[3] != location.hostname) || ($(elem).attr("rel") + "").toLowerCase() == "external") {
			$(elem).attr("target", "_blank");
		}
	});
}
function HomeAction() {
	this.time_sleep = 8000;
	this.last = 0;
	this.slider = $("#H-Title");
	this.URL = this.slider.attr('data');
	this.setBackground(this.randName());
	this.setLogin();
	this.setFriendLink();
	this.setShareTalk();
}
HomeAction.prototype = {
	setLogin: function () {
		$("a.home_modal_link").click(function () {
			$.ajaxSetup({cache: true});
			$.ajax({
				url: this.href,
				success: function (data) {
					modal_show("用户操作", data);
				},
				cache: true
			});
			return false;
		});
	},
	setShareTalk: function () {
		$("#TalkShare").keydown(function () {
			if (event.ctrlKey && event.keyCode == 13) {
				if ($("#ShareTalk_Help").display != "none") {
					$("#ShareTalk_Help").hide();
				}
				var url = $("#H-User form")[0].action;
				var content = $(this).val();
				if (content.length > 0) {
					$.post(url, {content: content}, function (data) {
						if (data['status']) {
							$("#TalkShare").val("");
							$("#ShareTalk_Help p").html("分享成功！");
							$("#ShareTalk_Help").show("slow", function () {
								setTimeout(function () {
									$("#ShareTalk_Help").fadeOut("slow");
								}, 3000);
							});
						} else {
							modal_show("<span class='text-danger'>分享失败</span>", "<h3 class='text-danger'>" + data['msg'] + "</h3>");
						}
					});
				}
			}
		});
	},
	setFriendLink: function () {
		$("#H-Link a").each(function () {
			this.href = SITE_URL + "Tool/redirect?ref=friend&go=" + encodeURI(this.href);
		});
	},
	setBackground: function (name) {
		this.slider.css("background-image", "url('" + this.URL + name + "')");
		this.setTimer();
	},
	setTimer: function () {
		setTimeout((function (obj) {
			return function () {
				obj.setBackground(obj.randName())
			};
		})(this), this.time_sleep);
	},
	randName: function () {
		++this.last;
		if (this.last > 4) {
			this.last = 1;
		}
		var v = this.last;
		if (this.last < 10) {
			v = "0" + v;
		}
		return "slider_" + v + ".jpg";
	}
};
$(link_out_link);