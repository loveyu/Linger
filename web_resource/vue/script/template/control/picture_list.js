_methods_ = {
	load: function () {
		this.list = this.$data;
	}
};//_methods_

_props_ = {callback: Function};//_props_

_data_ = function () {
	return {
		list: null
	};
};

_created_ = function () {
	if (typeof this.callback === "function") {
		this.callback(this);
	}
};//_created_