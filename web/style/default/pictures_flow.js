/**
 * Created by loveyu on 2016/5/3.
 */
function PicturesFlow(api_url, select) {
	this.api_url = api_url;
	this.select = jQuery(select);
	this.current_page = 0;
	this.is_end = false;
}
PicturesFlow.prototype = {
	append_data: function (data) {
		if (this.current_page % 8 == 0) {
			//clean
			this.select.html("");
			var that = this;
			setTimeout(function () {
				that.run_next();
			}, 500);
		}
		for (var i in data) {
			if (!data.hasOwnProperty(i)) {
				continue;
			}
			var item = data[i];
			this.select.append("<a href=\"" + item.pic_link + "\" title=\"" + item.pic_description +
				"\"><img src=\"" + item.pic_thumbnails_url +
				"\" alt=\"" + item.pic_name + "\"></a>");
		}
	},
	load: function (page, end_call) {
		this.current_page = page;
		var obj = this;
		jQuery.get(this.api_url + "/" + 48 + "/" + page, function (result) {
			if (result.status) {
				if (result.data.length == 0) {
					obj.is_end = true;
				} else {
					//设置数据
					obj.append_data(result.data);
					if (typeof end_call == "function") {
						end_call();
					}
				}
			} else {
				obj.is_end = true;
			}
		});
	},
	run_next: function () {
		if (this.is_end) {
			//已加载全部
			return;
		}
		this.load(this.current_page + 1);
	},
	run: function () {
		var that = this;
		this.load(1, function () {
			var timer = false;
			jQuery(window).scroll(function () {
				if (jQuery(document).scrollTop() + jQuery(window).height() >= jQuery(document).height()) {
					if (timer) {
						return;
					}
					timer = true;
					that.run_next();
					setTimeout(function () {
						timer = false;
					}, 500)
				}
			});
		});
	}
};