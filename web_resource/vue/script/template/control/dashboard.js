_methods_ = {};//_methods_

_props_ = {callback: Function};//_props_

_created_ = function () {
	if (typeof this.callback === "function") {
		this.callback(this);
	}
};//_created_