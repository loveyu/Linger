<div class="well" id="ImagePre" ondragenter="return false" ondragover="return false" ondrop="dropIt(event)">
	<blockquote class="text-info">你可以将你需要上传的图片拖到上面的图片面板。<span class="text-danger">图片最大5M，最小尺寸400*400。</span></blockquote>
	<div>
		<input id="SelectFileInput" value="" type="file" name="files[]" accept="image/gif,image/png,image/jpeg" multiple
			   onchange="imagesSelected(this.files)">
	</div>
	<div class="image_upload clearfix">
		<button type="button" onclick="all_add();" class="btn btn-default hidden-xs hidden-print">批量操作</button>
		<div class="form-inline hidden-xs hidden-print" style="display: inline;">
			<label class="sr-only" for="All_action">批量操作</label>
			<select id="All_action" class="form-control">
				<option value="1">添加标签</option>
				<option value="2">添加描述</option>
				<option value="3">清除标签</option>
				<option value="4">清除描述</option>
				<option value="5">清除名称</option>
			</select>
		</div>
		<button type="button" class="btn btn-danger col-sm-1 pull-right" onclick="upload_all()">上传<span
				class="glyphicon glyphicon-cloud-upload"></span></button>
		<button type="button" class="btn btn-warning col-sm-1 upload_clear pull-right" onclick="image_clear()">清除</button>
	</div>
	<div class="well well-sm">
		<p class="help-block">已上传的图片：<a href="#" onclick="return uploaded_img_edit();">编辑</a></p>

		<div id="Uploaded"></div>
	</div>
	<div id="ImageList">
	</div>
</div>
<script src="<?php echo get_file_url("js/md5_sha1.js"); ?>" type="text/javascript"></script>
<script type="text/javascript">
$(function () {
	if (!html5_file_upload()) {
		alert_error("当前浏览器不支持HTML5的图片上传！");
	}
});
var API_URL = '<?php echo get_url("UserApi", "picture_upload");?>';
var files_info = {files: {},//文件存储信息，是对象，非数组
	uid: {},//文件唯一ID
	count: 0, //当前队列计数
	index_list: [],//当前文件对象，对应的对象键值
	index_key: '0', //在循环时可以用到的键名
	upload_lock: false, //文件上传锁，防止多文件同时上传出异常
	all_upload: false,//全部上传标记
	index: 0//当前全部上传计数，可以用来对应index_list数组的值
};
function all_add() {
	var val = $("select#All_action").val();
	var str = '';
	switch (+val) {
		case 1:
			str = prompt("输入标签:");
			if (str.length > 0) {
				$("input[name='tag']").each(function (i, elem) {
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
				$("textarea[name='desc']").each(function (i, elem) {
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
			$("input[name='tag']").val("");
			break;
		case 4:
			$("textarea[name='desc']").val("");
			break;
		case 5:
			$("input[name='name']").val("");
			break;
	}
}
function upload_all() {
	create_index();
	if (files_info.index_list.length > 0) {
		files_info.all_upload = true;
		$("#ImageList button").enable(false);
		files_info.index = 0;//从第一张开始
		upload_img(files_info.index_list[files_info.index]);
	} else {
		alert_error("没有图片被添加");
	}

}
function image_clear() {
	files_info = {files: {}, uid: {}, count: 0, index_list: [], index_key: '0', upload_lock: false};
	$("#ImageList .image_preview").hide('slow', function () {
		$(this).html("");
	});
}
function upload_img(index) {
	if (files_info.upload_lock === true) {
		alert_error("当前有文件在上传，请等待上传结束。");
		return;
	}
	if (files_info.files.hasOwnProperty(index) && files_info.files[index] !== null) {
		files_info.upload_lock = true;
		files_info.index_key = index;
		var xhr = new XMLHttpRequest(); //创建请求对象
		xhr.upload.addEventListener("progress", e_process, false);
		xhr.addEventListener("load", e_complete, false);
		xhr.addEventListener("error", e_failed, false);
		xhr.addEventListener("abort", e_canceled, false);
		xhr.open("POST", API_URL, true);
		var fd = new FormData(); //创建表单
		fd.append("files[]", files_info.files[index]);
		fd.append("get_msg", "1");
		fd.append("tag[]", $("#ImagePreview" + index + " input[name='tag']").val());
		fd.append("desc[]", $("#ImagePreview" + index + " textarea[name='desc']").val());
		fd.append("name[]", $("#ImagePreview" + index + " input[name='name']").val());
		xhr.send(fd);
	}
}
function e_process(evt) {
	if (evt.lengthComputable) {
		var percentComplete = Math.round(evt.loaded * 100 / evt.total) + '%';
		$("#ImagePreview" + files_info.index_key + " .progress-bar").width(percentComplete);
		if (percentComplete == "100%") {
			percentComplete = "上传结束，处理中！";
		}
		$("#ImagePreview" + files_info.index_key + " .progress-bar span").html(percentComplete);
	}
}
function uploaded_img_edit() {
	var list = [];
	$("#Uploaded img.selected").each(function (i, e) {
		list.push(+this.alt);
	});
	if (list.length < 1) {
		alert_error("没有要编辑的图片");
	} else {
		location.href = "<?php echo get_url("Photo","edit_pic")?>?id=" + list.join(",");
	}
	return false;
}
function uploaded_img_click(s) {
	if ($(s).hasClass("selected")) {
		$(s).removeClass("selected");
	} else {
		$(s).addClass("selected");
	}
	return false;
}
function e_complete(evt) {
	var data = $.parseJSON(this.response);
	files_info.upload_lock = false;
	if (!data.status) {
		$("#ImagePreview" + files_info.index_key + " .progress-bar span").html("出错:" + data['msg']);
	} else {
		files_info.files[files_info.index_key] = null;
		$("#ImagePreview" + files_info.index_key).hide("slow", function () {
			$(this).remove();
		});
		$("#Uploaded").append("<img onclick='return uploaded_img_click(this);' class='img-thumbnail selected' src='" + data['content']['msg']['pic_thumbnails_url'] + "' alt='" + data['content']['msg']['pic_id'] + "'>");
	}
	if (!files_info.all_upload) {
		create_index();
	} else {
		if (files_info.index_list.length - 1 === files_info.index) {
			//最后一个新建索引
			files_info.all_upload = false;
		} else {
			++files_info.index;
			upload_img(files_info.index_list[files_info.index]);
		}
	}
}
function e_failed(evt) {
	$("#ImagePreview" + files_info.index_key + " .progress-bar span").html(
		"出错:" + this.status + " - " + this.statusText);
}
function e_canceled(evt) {
	$("#ImagePreview" + files_info.index_key + " .progress-bar span").html(
		"被取消:" + this.status + " - " + this.statusText);
}
function clear_img(index) {
	if (files_info.files.hasOwnProperty(index) && files_info.files[index] !== null) {
		var uid = get_file_uid(files_info.files[index]);
		files_info.uid[uid] = null;
		files_info.files[index] = null;
	}
	create_index();
	$("#ImagePreview" + index).hide('slow', function () {
		$(this).remove();
	});
}
function get_file_uid(file) {
	return md5(file.lastModifiedDate + file.size + file.name + file.type);
}
function add_queue(file, index, uid) {
	files_info.files[index] = file;
	files_info.uid[uid] = uid;
	$("#ImageList").append("<div class='image_preview row clearfix' id='ImagePreview" + index + "'>" +
		"<div class='col-sm-4 preview_img'><div class='preview_img_pic'>" +
		'<img class="img-thumbnail" id="image_id_' + index + '" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">' +
		'</div><div class="progress progress-striped active" style="margin-top: 10px;">' +
		'<div class="progress-bar" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">' +
		'<span class="text-danger">' + file_size(file.size) + '</span>' +
		'</div></div>' +
		"</div>" +
		"<div class='col-sm-8 form-horizontal'><div class='form-group'>" +
		'<label class="col-sm-2 control-label" for="InputName' + index + '">名称</label>' +
		'<div class="col-sm-10">' +
		'<input class="form-control" value="' + file.name.replace(/\.\w+$/, '') + '" id="InputName' + index + '" name="name" type="text" placeholder="给图片的一个名字">' +
		"</div></div><div class='form-group'>" +
		'<label class="col-sm-2 control-label" for="InputTag' + index + '">图片标签</label>' +
		'<div class="col-sm-10 preview_tag">' +
		'<input class="form-control" id="InputTag' + index + '" name="tag" type="text" placeholder="图片标签,多个使用逗号等分开">' +
		"</div></div><div class='form-group'>" +
		'<label class="col-sm-2 control-label" for="InputDesc' + index + '">描述信息</label>' +
		'<div class="col-sm-10 preview_desc">' +
		'<textarea name="desc" rows="3" id="InputDesc' + index + '" class="form-control" placeholder="一段简易的图片描述"></textarea>' +
		'</div></div><div class="form-group">' +
		'<div class="col-sm-offset-2 col-sm-2 preview_remove">' +
		'<button class="btn btn-warning" onclick="clear_img(' + index + ');">删除该图片</button>' +
		'</div>' +
		'<div class="col-sm-offset-6 col-sm-2 preview_upload">' +
		'<button class="btn btn-primary" onclick="upload_img(' + index + ');">上传</button>' +
		'</div></div></div></div>');
	var imageReader = new FileReader();
	imageReader.onload = (function (aFile, o_i) {
		return function (e) {
			$("#image_id_" + o_i)[0].src = e.target.result;
		};
	})(file, index);
	imageReader.readAsDataURL(file);
}
function imagesSelected(files) {
	for (var i = 0, l = files.length; i < l; i++) {
		var type = files[i].type.split('/');
		if (type[0] !== 'image') {
			alert_error(files[i].name + " 不是图片文件");
			continue;
		}
		if (type[1] !== "jpeg" && type[1] !== "png" && type[1] !== "gif") {
			alert_error(files[i].name + " 不是指定格式的图片文件。");
			continue;
		}
		if (files[i].size > 5 * 1024 * 1024) {
			alert_error("文件: " + files[i].name + " ，" + file_size(files[i].size) + " 超过5M");
			continue;
		}
		var uid = get_file_uid(files[i]);
		if (files_info.uid.hasOwnProperty(uid) && files_info.uid[uid] !== null) {
			alert_error("文件：" + files[i].name + " 已存在，无需重复添加。");
			continue;
		}
		add_queue(files[i], files_info.count, uid);
		++files_info.count;
	}
	create_index();
	$("#SelectFileInput").val("");
}
function create_index() {
	files_info.index_list = [];
	for (var index in files_info.files) {
		if (files_info.files.hasOwnProperty(index) && files_info.files[index] !== null) {
			files_info.index_list.push(index);
		}
	}
}
function dropIt(event) {
	imagesSelected(event.dataTransfer.files);
	event.stopPropagation();
	event.preventDefault();
}
</script>