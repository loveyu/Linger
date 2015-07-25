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