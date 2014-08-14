<script src="<?php echo get_style("pagination.js") ?>"></script>
<div id="Select_user_pic">
	<div class="well well-sm form-inline clearfix">
		<button class="btn btn-primary pic_add" type="button">添加选中</button>
		<button class="btn btn-warning pic_edit" type="button">编辑</button>
		<button class="btn btn-default pull-right s_x" type="button">反选</button>
		<button class="btn btn-default pull-right s_n" type="button">全不选</button>
		<button class="btn btn-default pull-right s_a" type="button">全选</button>
		<label class="sr-only control-label" for="Page_number_select">选择每页显示数量</label>
		<select name="one_page" id="Page_number_select" class="pull-right form-control">
			<option value="100">每页100</option>
			<option value="50" selected>每页50</option>
			<option value="30">每页30</option>
			<option value="20">每页20</option>
			<option value="10">每页10</option>
			<option value="5">每页5</option>
		</select>
	</div>
	<div class="well well-sm clearfix nav_list">
	</div>
	<form class="well well-sm form-inline clearfix" onsubmit="return false;">
		<span class="text-primary glyphicon glyphicon-search">搜索：</span>
		<input type="text" placeholder="标签搜索" name="tag" class="form-control">
		<label class="sr-only control-label" for="Tag_search_select">标签搜索类型</label>
		<select id="Tag_search_select" name="tag_like" class="form-control">
			<option value="all">完整匹配</option>
			<option value="like">模糊查询</option>
		</select>
		<input class="form-control" type="datetime" name="begin_time" placeholder="开始时间，如<?php echo date("Y-m-d") ?>">
		<input class="form-control" type="datetime" name="end_time" placeholder="结束时间,可精确">
		<label class="sr-only control-label" for="Order_select">排序类型</label>
		<select name="order" id="Order_select" class="form-control">
			<option value="ASC">正排序</option>
			<option value="DESC" selected>倒排序</option>
		</select>
		<button class="btn btn-primary pull-right" type="submit">搜索</button>
	</form>
	<div class="Image_list" style="overflow: auto;"></div>
</div>
<script>
	$(function () {
		//该文档完成Select_Pic.add的jQuery事件
		var get_nav_url = function (str) {
			return "#page_" + str;
		};
		$("#Select_user_pic .pic_edit").click(function () {
			var list = [];
			$("#Select_user_pic .Image_list img.select").each(function (i, e) {
				list.push(+this.alt);
			});
			if (list.length < 1) {
				alert_error("未找到要编辑的图片");
			} else {
				window.open('<?php echo get_url("Photo",'edit_pic')?>?id=' + list.join(','));
			}
		});
		$("#Select_user_pic .pic_add").click(function () {
			var list = [];
			$("#Select_user_pic .Image_list img.select").each(function (i, e) {
				list.push(+this.alt);
			});
			if (list.length < 1) {
				alert_error("未找要添加的图片");
			} else {
				$(this).trigger("Select_Pic.add", [list]);
			}
		});

		$("#Select_user_pic .well button.s_x").click(function () {
			$("#Select_user_pic .Image_list img").each(function (i, e) {
				if ($(e).hasClass("select")) {
					$(e).removeClass("select");
				} else {
					$(e).addClass("select");
				}
			});
		});
		$("#Select_user_pic .well button.s_n").click(function () {
			$("#Select_user_pic .Image_list img").removeClass("select");
		});
		$("#Select_user_pic .well button.s_a").click(function () {
			$("#Select_user_pic .Image_list img").addClass("select");
		});
		$("select#Page_number_select").change(function () {
			get_data();
		});
		$("select#Tag_search_select").change(function () {
			get_data();
		});
		$("select#Order_select").change(function () {
			get_data();
		});
		$(".well button[type=submit]").click(function () {
			get_data();
		});
		var nav_callback = function () {
			var page = +this.href.substr(this.href.indexOf('#') + 6);
			if (page !== page_now) {
				get_data({now_page: page});
			}
			return false;
		};
		var count = 0;
		var page_now = 1;
		var pg = new Pagination("#Select_user_pic .nav_list");
		pg.set_page_nav_url(get_nav_url(pg.get_page_param()));
		var get_data = function (param) {
			var request_param = {};
			request_param.one_page = $("select#Page_number_select").val();
			request_param.page = page_now;
			if (typeof param != 'undefined' && typeof param.now_page != 'undefined') {
				request_param.page = param.now_page;
			}
			request_param.order = $("select#Order_select").val();
			request_param.tag = $(".well input[name='tag']").val();
			request_param.tag_like = $(".well select[name='tag_like']").val();
			request_param.time_begin = $(".well input[name='begin_time']").val();
			request_param.time_end = $(".well input[name='end_time']").val();
			$.get("<?php echo get_url('UserApi','select_user_pic')?>", request_param, function (data) {
				if (data['status']) {
					$("#Select_user_pic .Image_list").html("");
					if (typeof data['content']['content'] !== 'undefined') {
						for (var i in data['content']['content']) {
							var list = data['content']['content'][i];
							$("#Select_user_pic .Image_list").append("<img alt='" + list['pic_id'] + "' class='img-thumbnail' src='" + list.url + "'>");
						}
						$("#Select_user_pic .Image_list img").click(function () {
							if ($(this).hasClass("select")) {
								$(this).removeClass("select");
							} else {
								$(this).addClass("select");
							}
						});
						count = data['content']['count'];
						page_now = data['content']['now'];
						pg.load_param(1, data['content']['now'] - 1, data['content']['now'], 1 + data['content']['now'], data['content']['max']);
						pg.load();
						$("#Select_user_pic .nav_list a").click(nav_callback);
						if (+count === 0) {
							$("#Select_user_pic .Image_list").append("<h2 class='text-danger'>未找到搜索的内容</h2>");
						}
					}
				} else {
					alert_error(data['msg'], "加载错误");
				}
			});
		};
		get_data();
	});
</script>