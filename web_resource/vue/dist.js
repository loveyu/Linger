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
	var vue = new Vue({
		el: "#ControlMain",
		data: {
			menus: null,
			error: null
		},
		methods: {
			init_menus: function () {
				var obj = this;
				jQuery.getJSON(V_CONFIG.api.control.menu_list, {}, function (result) {
					if (result.status) {
						obj.menus = result.content;
						Vue.nextTick(function () {
							$.AdminLTE.tree('.sidebar');
						});
					} else {
						obj.error = result.msg;
					}
				});
			},
			dashboard: function () {

			},
			menu_click: function (event) {
				console.log(event);
			}
		}
	});
	vue.init_menus();
	var routes = {
		'/': function () {
			vue.dashboard();
		}
	};
	var router = Router(routes);//初始化一个路由器
	router.init();//加载路由配置
	if (document.location.hash == "") {
		//初始化空路由
		routes['/']();
	}
	return vue;
};


