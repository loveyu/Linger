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