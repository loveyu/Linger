var V_PAGE = {};
var APP = {
	page: {
		//页面的PAGE实例
	},
	runPage: function (page) {
		if (V_PAGE.hasOwnProperty(page) && typeof V_PAGE[page] == "function") {
			if (!APP.page.hasOwnProperty(page)) {
				APP.page[page] = V_PAGE[page]();
			}
			return APP.page[page];
		} else {
			console.error("Page:" + page + ", not found.");
		}
		return null;
	}
};


V_PAGE.picture_manage = {

};


