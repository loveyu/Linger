/**
 * Created by huzhiyu on 14-2-18.
 */

//@ sourceURL=Pagination

function Pagination(select) {
	this.html_select = select;
	this.html_select_ul = select + " ul.pagination";
}

Pagination.prototype = {
	get_page_param: function () {
		return "{page_number}";
	},
	set_page_nav_url: function (url) {
		this.html_nav_url = url;
	},
	load_param: function (begin, before, now, next, end) {
		this.p_begin = begin;
		this.p_before = before;
		this.p_next = next;
		this.p_end = end;
		this.p_now = now;
		if (now == 1 || now == begin) {
			this.p_begin = this.p_before = null;
		}
		if (now == end) {
			this.p_next = null;
		}
	},
	get_url: function (page) {
		page = +page;
		if (page < 1) {
			page = 1;
		}
		return this.html_nav_url.replace("{page_number}", page);
	},
	load: function () {
		$(this.html_select).html("");
		$(this.html_select).append("<ul class=\"pagination\"></ul>");

		if (this.p_begin !== null && this.html_select > 0) {
			$(this.html_select_ul).append("<li><a title='第 " + this.p_begin + " 页' href=\"" + this.get_url(this.p_begin) + "\">首页</a></li>");
		}
		if (this.p_before == null || this.p_now < 1) {
			$(this.html_select_ul).append("<li class='disabled'><a href=\"" + this.get_url("") + "\">&laquo;</a></li>");
		} else {
			$(this.html_select_ul).append("<li><a href=\"" + this.get_url(this.p_before) + "\">&laquo;</a></li>");
		}
		var bn = this.p_now - 4;
		var be = this.p_now + 4;
		if (bn < 1) {
			be -= bn - 1;
			bn = 1;
		}
		if (be > this.p_end) {
			bn -= be - this.p_end;
			if (bn < 1) {
				bn = 1;
			}
			be = this.p_end;
		}
		var i = 0;
		//之前页
		for (i = bn; i < this.p_now; i++) {
			$(this.html_select_ul).append("<li><a href=\"" + this.get_url(i) + "\">" + i + "</a></li>");
		}

		//当前页
		$(this.html_select_ul).append("<li class='active'><a href=\"" + this.get_url(this.p_now) + "\">" + this.p_now + "</a></li>");

		//之后页
		for (i = this.p_now + 1; i <= be; i++) {
			$(this.html_select_ul).append("<li><a href=\"" + this.get_url(i) + "\">" + i + "</a></li>");
		}
		if (this.p_next == null) {
			$(this.html_select_ul).append("<li class='disabled'><a href=\"" + this.get_url("") + "\">&raquo;</a></li>");
		} else {
			$(this.html_select_ul).append("<li><a href=\"" + this.get_url(this.p_next) + "\">&raquo;</a></li>");
		}
		if (this.p_end !== this.p_now) {
			$(this.html_select_ul).append("<li><a title='第 " + this.p_end + " 页' href=\"" + this.get_url(this.p_end) + "\">尾页</a></li>");
		}
	}
};

