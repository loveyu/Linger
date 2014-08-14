/**
 * Created by loveyu on 14-4-7.
 */
function TimeLine(api_url, select) {
	this._API_URL = api_url;
	this._SELECT = select;
	this._USER_LIST = null;
	this._BIG = 0;
	this._SMALL = 0;
}
TimeLine.prototype = {
	event_set: function () {
		$(this._SELECT + " p.img img").click(function () {
			var img = this;
			var parent = $(this).parent()[0];
			var link = parent.href;
			if (link != img.src) {
				img.src = link;
				$(parent).addClass("show_img");
			} else {
				//TODO
				//幻灯片的实现
			}
			return false;
		});
	},
	add_header_notice: function (data) {
		$(this._SELECT + " .header_notice").remove();
		$(this._SELECT).prepend('<div class="header_notice alert alert-warning alert-dismissable">' +
			'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
			data +
			'</div>');
	},
	add_footer_notice: function (data) {
		$(this._SELECT + " .footer_notice").remove();
		$(this._SELECT).append('<div class="footer_notice alert alert-danger alert-dismissable">' +
			'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
			data +
			'</div>');
	},
	footer_notice_remove: function () {
		$(this._SELECT + " .footer_notice").hide('slow', function () {
			$(this).remove();
		});
	},
	header_notice_remove: function () {
		$(this._SELECT + " .header_notice").hide('slow', function () {
			$(this).remove();
		});
	},
	init_load: function () {
		var tl = this;
		this.add_header_notice("加载中.......");
		$.get(this._API_URL, function (data) {
			if (data['status']) {
				if (data['content']['count'] < 1) {
					tl.add_header_notice("没有可供显示的动态");
				} else {
					tl.append_data(data['content']['list'], data['content']['user']);
					tl.header_notice_remove();
				}
			} else {
				modal_show("<span class='text-danger'>加载数据失败</span>", "<p>" + data['msg'] + "</p>");
			}
		});
	},
	get_new: function () {
		this.header_notice_remove();
		var tl = this;
		$.get(this._API_URL, {id: 0 - this._BIG}, function (data) {
			if (data['status']) {
				if (data['content']['count'] < 1) {
					tl.add_header_notice("没有更多内容可以显示");
				} else {
					tl.append_data(data['content']['list'], data['content']['user'], true);
				}
			} else {
				modal_show("<span class='text-danger'>加载数据失败</span>", "<p>" + data['msg'] + "</p>");
			}
		});
	},
	get_more: function () {
		this.footer_notice_remove();
		var tl = this;
		$.get(this._API_URL, {id: this._SMALL}, function (data) {
			if (data['status']) {
				if (data['content']['count'] < 1) {
					tl.add_footer_notice("没有更多内容可以显示");
				} else {
					tl.append_data(data['content']['list'], data['content']['user']);
				}
			} else {
				modal_show("<span class='text-danger'>加载数据失败</span>", "<p>" + data['msg'] + "</p>");
			}
		});
	},
	set_number: function (num) {
		num = +num;
		if (this._BIG === 0) {
			this._BIG = num;
		}
		if (this._SMALL === 0) {
			this._SMALL = num;
		}
		if (num > this._BIG) {
			this._BIG = num;
		}
		if (num < this._SMALL) {
			this._SMALL = num;
		}
	},
	append_data: function (data_list, user_list, before) {
		this._USER_LIST = user_list;
		if (typeof before == 'undefined') {
			before = false;
		} else if (before === true) {
			before = true;
		} else {
			before = false;
		}
		var ids = [];
		for (id in data_list) {
			if (data_list.hasOwnProperty(id)) {
				ids.push(id);
			}
		}
		ids = ids.reverse();
		var data = null;
		for (var id = 0, l = ids.length; id < l; id++) {
			this.set_number(ids[id]);
			switch (data_list[ids[id]]['action'].toLowerCase()) {
				case 'gallery':
					data = this.parse_gallery(data_list[ids[id]]);
					break;
				case 'sharepicture':
					data = this.parse_sharePicture(data_list[ids[id]]);
					break;
				case 'sharegallery':
					data = this.parse_shareGallery(data_list[ids[id]]);
					break;
				case 'talk':
					data = this.parse_talk(data_list[ids[id]]);
					break;
			}
			if (data != null) {
				if (before) {
					$(this._SELECT).prepend(data);
				} else {
					$(this._SELECT).append(data);
				}
			}
		}
		this.event_set();
		link_out_link();
	},
	parse_talk: function (data) {
		var uid = data['user'];
		var user = null;
		if (this._USER_LIST.hasOwnProperty(uid)) {
			user = this._USER_LIST[uid];
		}
		if (user == null) {
			return '';
		}
		return '<div class="talk media">' +
			'<a class="pull-left" href="' + user['link'] + '" rel="external">' +
			'<img title="' + user['aliases'] + '" class="media-object img-rounded" src="' + user['avatar'] + '" alt="' + user['name'] + '">' +
			'</a>' +
			'<div class="media-body">' +
			'<h4 class="media-heading"><span>' + user['aliases'] + '</span>' + '写到：</h4>' +
			'<div class="talk-content">' + data['content'] + '</div>' +
			'<p class="time"><span class="glyphicon-time glyphicon"></span><span>' + data['time'] + '</span></p>' +
			'</div>' +
			'</div>';
	},
	parse_shareGallery: function (data) {
		var s_id = data['s_uid'];
		var o_id = data['o_uid'];
		var share_users = null;
		var object_users = null;
		if (this._USER_LIST.hasOwnProperty(s_id)) {
			share_users = this._USER_LIST[s_id];
		}
		if (this._USER_LIST.hasOwnProperty(o_id)) {
			object_users = this._USER_LIST[o_id];
		}
		if (share_users == null || object_users == null) {
			return null;
		}
		var info = data['front_cover'];
		return '<div class="share_gallery media">' +
			'<a class="pull-left" href="' + share_users['link'] + '" rel="external">' +
			'<img title="' + share_users['aliases'] + '" class="media-object img-rounded" src="' + share_users['avatar'] + '" alt="' + share_users['name'] + '">' +
			'</a>' +
			'<div class="media-body">' +
			'<h4 class="media-heading"><span>' + share_users['aliases'] + '</span>' + '分享了' + (s_id != o_id ? "" +
			"<a rel=\"external\" href='" + object_users['link'] + "'>" + object_users['aliases'] + "</a>的" : "一个") + '图集 <a  rel="external" href="' + data['link'] +
			'" class="glyphicon glyphicon-link">' + data['title'] + '</a></h4>' +
			'<p class="time"><span class="glyphicon-time glyphicon"></span><span>' + data['time'] + '</span></p>' +
			(data['desc'].length > 0 ? ('<p class="desc">' + data['desc'] + '</p>') : "") +
			(info != null ? ('<p class="img"><a href="' + info['pic_display_url'] + '"><img src="' + info['pic_thumbnails_url'] + '" alt="' + info['pic_name'] + '"></a></p>') : "") +
			'</div>' +
			'</div>';
	},
	parse_sharePicture: function (data) {
		var s_id = data['share_users_id'];
		var o_id = data['object_users_id'];
		var share_users = null;
		var object_users = null;
		if (this._USER_LIST.hasOwnProperty(s_id)) {
			share_users = this._USER_LIST[s_id];
		}
		if (this._USER_LIST.hasOwnProperty(o_id)) {
			object_users = this._USER_LIST[o_id];
		}
		if (share_users == null || object_users == null) {
			return null;
		}
		var info = data['info'][0];
		return '<div class="share_picture media">' +
			'<a class="pull-left" href="' + share_users['link'] + '" rel="external">' +
			'<img title="' + share_users['aliases'] + '" class="media-object img-rounded" src="' + share_users['avatar'] + '" alt="' + share_users['name'] + '">' +
			'</a>' +
			'<div class="media-body">' +
			'<h4 class="media-heading"><span>' + share_users['aliases'] + '</span>' + '分享了' + (s_id != o_id ? "" +
			"<a rel=\"external\" href='" + object_users['link'] + "'>" + object_users['aliases'] + "</a>的" : "一张") + '图片 <a  rel="external" href="' + info['link'] +
			'" class="glyphicon glyphicon-link">' + (info['name'].length < 2 ? "第" + data['id'] + "号图片" : info['name']) + '</a></h4>' +
			'<p class="time"><span class="glyphicon-time glyphicon"></span><span>' + data['time'] + '</span></p>' +
			(info['desc'].length > 0 ? ('<p class="desc">' + info['desc'] + '</p>') : "") +
			'<p class="img"><a href="' + info['display_url'] + '"><img src="' + info['thumbnail_url'] + '" alt="' + info['name'] + '"><a/></p>' +
			'</div>' +
			'</div>';
	},
	parse_gallery: function (data) {
		var uid = data['users_id'];
		var user = null;
		if (this._USER_LIST.hasOwnProperty(uid)) {
			user = this._USER_LIST[uid];
		}
		if (user == null) {
			return '';
		}
		return '<div class="gallery media">' +
			'<a class="pull-left" href="' + user['link'] + '" rel="external">' +
			'<img title="' + user['aliases'] + '" class="media-object img-rounded" src="' + user['avatar'] + '" alt="' + user['name'] + '">' +
			'</a>' +
			'<div class="media-body">' +
			'<h4 class="media-heading"><span>' + user['aliases'] + '</span> ' + (data['is_update'] ? "更新" : "创建") + '了图集 <a rel="external" href="' + data['link'] +
			'" class="glyphicon glyphicon-link">' + data['title'] + '</a></h4>' +
			'<p class="time"><span class="glyphicon-time glyphicon"></span><span>' + data['time'] + '</span></p>' +
			(data['desc'].length > 0 ? ('<p class="desc">' + data['desc'] + '</p>') : "") +
			'<p class="img"><a href="' + data['front_cover']['pic_display_url'] + '"><img src="' + data['front_cover']['pic_thumbnails_url'] + '" alt="' + data['front_cover']['pic_name'] + '"></a></p>' +
			'<p class="tag">标签：<span class="label label-primary">' + data['tags'].join('</span><span class="label label-primary">') + '</span></p>' +
			'</div>' +
			'</div>';
	}
}
;

