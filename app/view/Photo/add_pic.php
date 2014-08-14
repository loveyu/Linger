<div class="well" id="ImagePre">
	<blockquote class="text-info">你可以将你需要上传的图片拖到上面的图片面板。<span class="text-danger">图片最大5M，最小尺寸400*400。</span></blockquote>
	<div class="progress progress-striped active">
		<div class="progress-bar" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
			<span class=""></span>
		</div>
	</div>
	<form class="image_upload_form_0" ondragenter="return false" ondragover="return false" ondrop="dropIt(event)" action="<?php echo get_url("UserApi", "picture_upload") ?>" method="post" enctype="multipart/form-data">
		<div class="image_upload row clearfix">
			<button class="image_upload btn btn-primary col-sm-2">
				<span>选择图片</span>
				<input type="file" name="files[]" accept="image/gif,image/png,image/jpeg" multiple onchange="imagesSelected(this.files,0)">
			</button>
			<button type="button" onclick="all_add();" class="btn btn-default">批量操作</button>
			<div class="form-inline" style="display: inline;">
				<label class="sr-only" for="All_action">批量操作</label>
				<select id="All_action" class="form-control">
					<option value="1">添加标签</option>
					<option value="2">添加描述</option>
					<option value="3">清除标签</option>
					<option value="4">清除描述</option>
				</select>
			</div>
			<button type="submit" class="btn btn-danger col-sm-1 pull-right">上传<span class="glyphicon glyphicon-cloud-upload"></span></button>
			<button type="button" class="btn btn-warning col-sm-1 upload_clear pull-right" id="Upload_clear_0">清除</button>
		</div>
	</form>
</div>

<script>
	var count = 0;
	function setProgress(width) {
		var pr = $(".progress");
		if (width <= 0) {
			pr.hide();
		} else {
			pr.show();
			pr.find(".progress-bar").width("" + width + "%");
			pr.find("span").html("" + width + "% 完成");
		}
	}

	function all_add() {
		var val = $("select#All_action").val();
		var str = '';
		switch (+val) {
			case 1:
				str = prompt("输入标签:");
				if (str.length > 0) {
					$("input[name='tag[]']").each(function (i, elem) {
						var s = $(this).val();
						if (s.length === 0) {
							s = str;
						} else {
							s = s + "," + str;
						}
						$(this).val(s);
					});
				}
				break;
			case 2:
				str = prompt("输入描述:");
				if (str.length > 0) {
					$("textarea[name='desc[]']").each(function (i, elem) {
						var s = $(this).val();
						if (s.length === 0) {
							s = str;
						} else {
							s = s + "\n" + str;
						}
						$(this).val(s);
					});
				}
				break;
			case 3:
				$("input[name='tag[]']").val("");
				break;
			case 4:
				$("textarea[name='desc[]']").val("");
				break;
		}
	}
	/**
	 * 加载等待图片
	 */
	function loading(remove) {
		if (typeof remove != 'undefined' && remove == true) {
			$("#Pic_Upload_Loading").remove();
			$("#Pic_Upload_Loading_Content").remove();
		} else {
			var s = $("body");
			s.append("<div id='Pic_Upload_Loading'></div>" +
				"<div id='Pic_Upload_Loading_Content'>" +
				"<p class='text-center'>图片上传中，上传结束后会需要几秒到十多秒的时间来处理图片，请耐心等待。<p>" +
				"</div>");
		}
	}
	$(function () {
		setProgress(0);
		$("form").submit(function () {
			if (count < 1) {
				alert_error("没有一张图片被选中。");
				return false;
			}
			setProgress(0);
			$("form").ajaxSubmit({
				uploadProgress: function (event, position, total, percent) {
					setProgress(percent);
				},
				error: function () {
					alert_error("上传出现错误，非常抱歉");
					loading(true);
					setProgress(0);
				},
				success: function (data) {
					loading(true);
					setProgress(0);
					if (data['status']) {
						setTimeout(function () {
							location.href = "<?php echo get_url("Photo","edit_pic")?>?id=" + data['content']['list'].join(",");
						}, 1000);
						image_clear(0);
						$("form").append("<p class='well text-success'>上传成功，即将跳转到编辑页面</p>");
					} else {
						if (data['msg'] == '' && typeof data['content']['error']) {
							alert_error(data['content']['error'].join("\n"));
						} else {
							alert_error(data['msg']);
						}
					}
					return false;
				}});
			loading();
			return false;
		});
	});
	function bind_dom() {
		$(".upload_clear").click(function () {
			image_clear(this.id.substr(13));
		});
	}
	function image_clear(id) {
		$(".image_upload_form_" + id + " .image_preview").remove();
		$(".image_upload_form_" + id + " input[type=file]").val("");
		count = 0;
	}
	function imagesSelected(files, form_list_id) {
		$(".image_upload_form_" + form_list_id + " .image_preview").remove();
		if (files.length > 20) {
			alert_error("一次最多运行上传20张图片");
			return image_clear();
		}
		var all_size = 0;

		for (var i = 0, l = files.length; i < l; i++) {
			all_size += files[i].size;
		}
		if (all_size > 64 * 1024 * 1024) {
			alert_error("一次上传文件不能超过64M");
			return image_clear();
		}
		for (var i = 0, f; f = files[i]; i++) {
			var type = f.type.split('/');
			if (type[0] !== 'image') {
				alert_error(f.name + " 不是图片文件");
				return image_clear();
			}
			if (type[1] !== "jpeg" && type[1] !== "png" && type[1] !== "gif") {
				alert_error(f.name + " 不是指定格式的图片文件。");
				return image_clear();
			}
			if (f.size > 5 * 1024 * 1024) {
				alert_error("文件: " + f.name + " ，" + file_size(f.size) + " 超过5M");
				return image_clear();
			}
//			alert_notice("FILE:" + f.name + ", size:" + file_size(f.size) + ", type:" + f.type);
			$(".image_upload_form_" + form_list_id).append("<div class='image_preview row'>" +
				"<div class='col-md-3 preview_img'>" +
				'<img class="img-thumbnail" id="image_id_' + form_list_id + '_' + i + '" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">' +
				"</div>" +
				"<div class='col-md-9 form-group'>" +
				'<label class="col-sm-2 control-label" for="InputTag' + form_list_id + '_' + i + '">图片标签</label>' +
				'<div class="col-sm-10">' +
				'<input class="form-control" id="InputTag' + form_list_id + '_' + i + '" name="tag[]" type="text" placeholder="图片标签,多个使用逗号等分开">' +
				"</div>" +
				'<label class="col-sm-2 control-label" for="InputDesc' + form_list_id + '_' + i + '">描述信息</label>' +
				'<div class="col-sm-10 preview_desc">' +
				'<textarea name="desc[]" rows="3" id="InputDesc' + form_list_id + '_' + i + '" class="form-control" placeholder="一段简易的图片描述"></textarea>' +
				'</div>' +
//				'<div class="col-sm-offset-2 col-sm-2 preview_remove">' +
//				'<button class="btn btn-warning">删除该图片</button>' +
//				'</div>' +
//				'<div class="col-sm-offset-6 col-sm-2 preview_upload">' +
//				'<button class="btn btn-primary">上传</button>' +
//				'</div>' +
				'</div>' +
				'</div>');
			var imageReader = new FileReader();
			imageReader.onload = (function (aFile, o_i, i) {
				return function (e) {
					$("#image_id_" + o_i + "_" + i)[0].src = e.target.result;
				};
			})(f, form_list_id, i);
			imageReader.readAsDataURL(f);
		}
		++count;
		bind_dom();
		return true;
	}
	function dropIt(event) {
		imagesSelected(event.dataTransfer.files);
		event.stopPropagation();
		event.preventDefault();
	}
</script>