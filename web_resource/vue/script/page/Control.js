V_PAGE.Control = function () {
	var $ = jQuery;
	var vue = new Vue({
		el: "#ControlMain",
		data: {
			menus: null,
			error: null,
			title: "Loading",
			showVersion: false,
			currentView: null,
			callback: null
		},
		components: {
			picture_list: {__require: 'control/picture_list.html'},
			dashboard: {__require: 'control/dashboard.html'}
		},
		methods: {
			rDashboard: function () {
				this.setMenu('dashboard');
				this.setTitle('控制面板', true);
				this.callback = function ($child) {

				};
				this.currentView = "dashboard";
			},
			rPictureList: function () {
				this.setMenu('picture', 'picture-list');
				this.setTitle('图片列表');
				this.callback = function ($child) {
					$child.load();
				};
				this.currentView = "picture_list";
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
				//return;
				if (typeof group === "string" && group != "") {
					$("#VME-" + group).addClass("active");
					if (typeof group_item === "string" && group_item != "") {
						$("#VME-" + group_item).addClass("active");
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