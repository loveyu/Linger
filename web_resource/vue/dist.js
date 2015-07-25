var V_CONFIG = {
	base_url: (typeof BASE_URL == "string") ? BASE_URL : (document.location.protocol + "//" + document.location.host + "/"),
	api: {
		control: {
			menu_list: "api2/ControlApi/menu_list"
		}
	}
};
(function setApiConfig(c) {
	for (var i in c) {
		if (c.hasOwnProperty(i)) {
			switch (typeof c[i]) {
				case "string":
					c[i] = V_CONFIG.base_url + c[i];
					break;
				case "object":
					setApiConfig(c[i]);
					break;
			}
		}
	}
})(V_CONFIG.api);


var V_PAGE = {};
var V_APP = {
	page: {
		//页面的PAGE实例
	},
	runPage: function (page) {
		if (V_PAGE.hasOwnProperty(page) && typeof V_PAGE[page] == "function") {
			if (!V_APP.page.hasOwnProperty(page)) {
				V_APP.page[page] = V_PAGE[page]();
			}
			return V_APP.page[page];
		} else {
			console.error("Page:" + page + ", not found.");
		}
		return null;
	}
};


V_PAGE.Control = function () {
	var $ = jQuery;
	var vue = new Vue({
		el: "#ControlMain",
		data: {
			menus: null,
			error: null,
			title: "Loading",
			showVersion: false
		},
		methods: {
			rDashboard: function () {
				this.setMenu('dashboard');
				this.setTitle('控制面板', true);
			},
			rPictureList: function () {
				this.setMenu('picture', 'picture-list');
				this.setTitle('图片列表');
			},
			init_menus: function (callback) {
				var obj = this;
				jQuery.getJSON(V_CONFIG.api.control.menu_list, {}, function (result) {
					if (result.status) {
						obj.menus = result.content;
						Vue.nextTick(function () {
							$.AdminLTE.tree('.sidebar');
							if (typeof callback === "function") {
								callback();
							}
						});
					} else {
						obj.error = result.msg;
					}
				});
			},
			setMenu: function (group, group_item) {
				if (typeof group === "string" && group != "") {
					$("#VME-" + group + " > a").trigger('click');
					if (typeof group_item === "string" && group_item != "") {
						setTimeout(function () {
							$("#VME-" + group_item + " > a").trigger('click');
						}, 1000);
					}
				}
			},
			setTitle: function (title, showVersion) {
				this.title = title;
				this.showVersion = showVersion === true;
			}
		}
	});
	vue.init_menus(function () {
		var routes = {
			'/': function () {
				vue.rDashboard();
			},
			'/picture_list': function () {
				vue.rPictureList();
			}
		};
		var router = Router(routes);//初始化一个路由器
		router.init();//加载路由配置
		if (document.location.hash == "") {
			//初始化空路由
			routes['/']();
		}
	});
	return vue;
};


