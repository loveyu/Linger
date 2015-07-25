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