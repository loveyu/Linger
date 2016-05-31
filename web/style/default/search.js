/**
 * Created by loveyu on 2016/5/15.
 */

function search_init(keyword) {
	var count_api = "/DataApi/search_init";
	var search_api = "/DataApi/search";
	var $ = jQuery;
	if (typeof SITE_URL !== "undefined") {
		count_api = SITE_URL + "DataApi/search_init";
		search_api = SITE_URL + "DataApi/search";
	}
	var search_count = 0;
	var empty_search_html = "<div class='search-empty'><p><span class='glyphicon glyphicon-info-sign'></span>当前未搜索到任何内容</p></div>";
	var load_init_data = function (api, keyword) {
		//查询数据总数
		var is_trigger = false;
		var add_number_to_tabs = function (id, num, type) {
			var elem = $("#SearchPage li a[aria-controls=" + id + "]");
			elem.append("<span class=\"badge\">" + num + "</span>");
			if (num == 0) {
				$("#" + id).html(empty_search_html);
			} else if(is_trigger==false){
				//触发分类搜索
				is_trigger = true;
				elem.trigger("click");
			}
		};
		$.getJSON(api + "?keyword=" + keyword, function (result) {
			if (result == null || !result.hasOwnProperty('data') || result.data == null) {
				return;
			}
			result = result.data;
			for (var index = 0; index < result.length; index++) {
				var item = result[index];
				search_count += item['num'];
				switch (item['name']) {
					case "pic":
						add_number_to_tabs("PictureTab", item['num'], item['name']);
						break;
					case "gallery":
						add_number_to_tabs("GalleryTab", item['num'], item['name']);
						break;
					case "post":
						add_number_to_tabs("PostTab", item['num'], item['name']);
						break;
				}
			}
			if (search_count == 0) {
				//搜索总记录为空，设置为未找到数据
				load_search_empty();
			}
		});
	};

	var load_search_empty = function () {
		$("#SearchPage .search-tabs-content .tab-pane").html(empty_search_html);
	};

	var page_map = {};
	var load_result_by_type = function (type) {
		if(!page_map.hasOwnProperty(type)){
			page_map[type] = 0;
		}
		
	};

	jQuery(function ($) {
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var obj = $(e.target);
			var type = obj.attr("aria-controls");
			var map = {PictureTab: "pic", GalleryTab: "gallery", PostTab: "post"};
			if (map.hasOwnProperty(type)) {
				load_result_by_type(map[type]);
			}
		});
		load_init_data(count_api, keyword);
	});
}