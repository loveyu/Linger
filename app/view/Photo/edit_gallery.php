<?php
/**
 * @var array         $__info    图集信息
 * @var \ULib\Gallery $__gallery 图集对象
 */
if(empty($__info)): ?>
	<h2 class="text-danger">内容未找到....</h2>
	<script>
		$(function () {
			setTimeout(function () {
				location.href = "<?php echo get_url("Photo","list_gallery")?>";
			}, 1000);
		});
	</script>
<?php else: ?>
	<form method="post" action="<?php echo get_url("UserApi", "gallery_edit_info") ?>">
		<div class="row">
			<div class="col-md-8">
				<div class="form-group">
					<label class="control-label sr-only" for="InputTitle">标题</label>
					<input type="text" name="gallery_title" class="form-control input-lg" placeholder="一个个性的标题" id="InputTitle"
						   value="<?php echo $__info['gallery_title']; ?>">

					<p class="help-block">固定连接:<?php $link = gallery_link($__info['gallery_id']) ?><a
							href="<?php echo $link ?>" rel="external"><?php echo $link ?></a></p>
				</div>
				<div class="form-group">
					<label for="InputDescription" class="control-label">简易的描述：
						<small class="text-warning">仅支持纯文本</small>
					</label>
					<textarea class="form-control" placeholder="简要的描述" rows="3" id="InputDescription"
							  name="gallery_description"><?php echo $__info['gallery_description'] ?></textarea>
				</div>
				<div class='form-group'>
					<label class='control-label' for="Gallery_more_info">详细介绍</label>
					<textarea style="height: 250px;max-height: 600px" class="form-control" id="Gallery_more_info"
							  name="meta[more_info]"><?php echo $__gallery->more_info() ?></textarea>
				</div>
				<div class="well form-horizontal gallery_meta well-sm">
					<p class="help-block">静态标签资源，对图集的详细描述。</p>
					<?php hook()->apply("Photo_edit_gallery_meta", ''); ?>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label" for="InputTime">创建时间</label>
					<input id="InputTime" class="form-control" disabled value=" <?php echo $__info['gallery_create_time'] ?>">
				</div>
				<div class="form-group">
					<label class="control-label" for="InputCommentStatus">是否允许评论</label>
					<select class="form-control" id="InputCommentStatus" name="gallery_comment_status">
						<?php echo html_option([
							'0' => '关闭评论',
							'1' => '允许评论'
						], $__info['gallery_comment_status']) ?>
					</select>
				</div>
				<div class="form-group">
					<p class="help-block">
						评论数量:<span class="label label-info"><?php echo $__info['gallery_comment_count'] ?></span>
						喜欢人数:<span class="label label-info"><?php echo $__info['gallery_like_count']; ?></span>
					</p>
				</div>
				<div class="form-group">
					<label class="control-label" for="Gallery_add_tag_<?php echo $__info['gallery_id'] ?>">标签:</label>

					<div class="row">
						<div class="col-sm-9">
							<input class="form-control"
								   onkeypress="if(event.keyCode==13) {gallery_add_tag(<?php echo $__info['gallery_id'] ?>);return false;}"
								   type="text" id="Gallery_add_tag_<?php echo $__info['gallery_id'] ?>" placeholder="添加标签">
						</div>
						<div class="col-sm-3">
							<button class="btn btn-info" onclick="gallery_add_tag(<?php echo $__info['gallery_id'] ?>);" type="button">添加</button>
						</div>
					</div>
					<p class="help-block">当前标签:<?php foreach($__info['gallery_tags'] as $tag){ ?>
							<span class="label label-info"><?php echo $tag ?>
								<span onclick="gallery_remove_tag(<?php echo $__info['gallery_id'], ",'", $tag, "'"; ?>,this)"
									  style="cursor: pointer;"
									  class="glyphicon glyphicon-remove"></span></span>
						<?php } ?></p>
				</div>
				<div class="form-group">
					<label class="control-label">封面图片：</label>

					<div id="GalleryFrontCover">
						<?php if(intval($__info['gallery_front_cover']) > 0): ?>
							<img class='img-rounded' style='max-width: 70%;' src='<?php echo $__info['front_cover']['pic_thumbnails_url'] ?>'
								 alt="front_cover">
						<?php else: ?>
							<p class="help-block text-warning">还没有封面图片</p>
						<?php endif; ?>
					</div>
				</div>
				<p class="clearfix">
					<?php if($__info['gallery_status'] == 1): ?>
						<button class="btn btn-warning pull-right s_draft" type="button">改变为草稿</button>
					<?php else: ?>
						<button class="btn btn-primary s_public" type="button">发布该图集</button>
					<?php endif; ?>
				</p>
				<p class="clearfix">
					<button class="btn btn-danger pull-right s_d" type="button" onclick="alert_notice('请在管理页面中删除');">删除</button>
					<button class="btn btn-primary pull-left" type="submit">更新修改的信息</button>
				</p>
			</div>
		</div>
		<input type="hidden" name="gallery_id" value="<?php echo $__info['gallery_id'] ?>">
	</form>
	<div class="Gallery_pic_list well well-sm">
		<p class="clearfix">
			<button class="btn btn-primary add_pic" type="button">添加图片</button>
			<button class="btn btn-danger del_pic" type="button">移除图片</button>
			<button class="btn btn-info set_front_cover" type="button">设置封面</button>
			<button class="btn btn-info edit_pic" type="button">编辑图片</button>
			<button class="btn btn-default pull-right s_x" type="button">反选</button>
			<button class="btn btn-default pull-right s_n" type="button">全不选</button>
			<button class="btn btn-default pull-right s_a" type="button">全选</button>
		</p>
		<div class="img_list">
			<?php foreach($__info['gallery_pictures'] as $v): ?>
				<img src="<?php echo $v['pic_thumbnails_url'] ?>" class="img-thumbnail" alt="<?php echo $v['pic_id'] ?>"/>
			<?php endforeach; ?>
		</div>
	</div>
	<script>
		$(function () {
			$("#Gallery_more_info").markdown({onPreview: function (e) {
				var previewContent;
				var originalContent = e.getContent();
				var status = $.ajaxSettings.async;
				$.ajaxSetup({
					async: false
				});
				$.post("<?php echo get_url("UserApi","markdown")?>", {content: originalContent}, function (data) {
					if (data['status']) {
						previewContent = data['content'];
					} else {
						previewContent = "异常信息：" + data['msg'];
					}
					$.ajaxSetup({
						async: status
					});
				});
				return previewContent;
			}});
			var add_img_click_event = function () {
				$(".Gallery_pic_list .img_list img").click(function () {
					if ($(this).hasClass("select")) {
						$(this).removeClass("select");
					} else {
						$(this).addClass("select");
					}
				});
			};
			var add_event = function (list) {
				$.post("<?php echo get_url("UserApi","gallery_add_pic");?>", {gallery_id:<?php echo $__info['gallery_id']?>, list: list.join(',')}, function (data) {
					if (data['status']) {
						$(".Gallery_pic_list .img_list").html("");
						$.each(data['content'], function (i, e) {
							$(".Gallery_pic_list .img_list").append('<img src="' + e['pic_thumbnails_url'] + '" class="img-thumbnail" alt="' + e['pic_id'] + '"/>');
						});
						add_img_click_event();
					} else {
						alert_error(data['msg'], "数据加载错误");
					}
				});
			};
			add_img_click_event();
			$("p button.s_public").click(function(){
				//设置为发布状态
				$.post('<?php echo get_url('UserApi','gallery_set_public')?>',{id:<?php echo $__info['gallery_id']?>},function(data){
					if(data['status']){
						location.reload();
					}else{
						alert_error(data['msg']);
					}
				});
			});
			$("p button.s_draft").click(function(){
				//设置为草稿状态
				$.post('<?php echo get_url('UserApi','gallery_set_draft')?>',{id:<?php echo $__info['gallery_id']?>},function(data){
					if(data['status']){
						location.reload();
					}else{
						alert_error(data['msg']);
					}
				});
			});
			$(".Gallery_pic_list p button.del_pic").click(function () {
				var list = [];
				$(".Gallery_pic_list .img_list img.select").each(function (i, e) {
					list.push(+this.alt);
				});
				if (list.length < 1) {
					alert_error("没选中任何图片");
				} else {
					$.post("<?php echo get_url("UserApi","gallery_remove_pic")?>", {gallery_id:<?php echo $__info['gallery_id']?>, list: list.join(',')}, function (data) {
						if (data['status']) {
							alert_notice("删除成功");
							$.each(list, function (i, e) {
								$(".Gallery_pic_list .img_list img[alt=" + e + "]").hide('slow', function () {
									$(this).remove();
								});
							});
						} else {
							alert_error(data['msg'], "删除失败");
						}
					});
				}
			});
			$(".Gallery_pic_list p button.s_x").click(function () {
				$(".Gallery_pic_list .img_list img").each(function (i, e) {
					if ($(e).hasClass("select")) {
						$(e).removeClass("select");
					} else {
						$(e).addClass("select");
					}
				});
			});
			$(".Gallery_pic_list p button.s_n").click(function () {
				$(".Gallery_pic_list .img_list img").removeClass("select");
			});
			$(".Gallery_pic_list p button.s_a").click(function () {
				$(".Gallery_pic_list .img_list img").addClass("select");
			});
			$(".Gallery_pic_list p button.edit_pic").click(function () {
				var list = [];
				$(".Gallery_pic_list .img_list img.select").each(function (i, e) {
					list.push(+this.alt);
				});
				if (list.length < 1) {
					alert_error("必须先选择一张图片");
				} else {
					window.open("<?php echo get_url("Photo","edit_pic")?>?id=" + list.join(","), "_blank");
				}
			});
			$(".Gallery_pic_list p button.set_front_cover").click(function () {
				var list = [];
				$(".Gallery_pic_list .img_list img.select").each(function (i, e) {
					list.push(+this.alt);
				});
				if (list.length != 1) {
					alert_error("你只能选择一张图片作为封面,当前选择 " + list.length + " 张");
				} else {
					$.post("<?php echo get_url("UserApi","gallery_set_front_cover")?>", {gallery_id:<?php echo $__info['gallery_id']?>, pic_id: list.join(',')}, function (data) {
						if (data['status']) {
							if (data['content'] === null) {
								alert_notice("封面被清空");
							} else {
								$("#GalleryFrontCover").html("<img class='img-rounded' style='max-width: 70%;' src='" + data['content']['pic_thumbnails_url'] + "'>");
								$(".Gallery_pic_list .img_list img").removeClass("select");
								alert_notice("设置成功");
							}
						} else {
							alert_error(data['msg'], "设置封面失败");
						}
					});
				}
			});
			$(".Gallery_pic_list p button.add_pic").click(function () {
				var s = $("body");
				var sf = $("#Edit_gallery_add_pic_frame");
				if (sf.length > 0) {
					sf.show("fast");
				} else {
					s.append("<div id='Edit_gallery_add_pic_frame'><p class='text-center'><button class='btn btn-danger select_close'>关闭</button></p><div class='content'></div></div>");
					s.find("#Edit_gallery_add_pic_frame .content").load("<?php echo get_url("Photo","select_user_pic")?>");
					s.unbind("Select_Pic.add");
					s.bind("Select_Pic.add", function (e, list) {
						$("#Edit_gallery_add_pic_frame").hide("fast");
						add_event(list);
					});
					$("button.select_close").click(function () {
						$("#Edit_gallery_add_pic_frame").hide("fast");
					});
				}
			});
			$("form").ajaxForm(function (data) {
				if (data['status']) {
					alert_notice("更新信息成功");
				} else {
					alert_error(data['msg'], "更新信息出错了");
				}
			});
			$(".gallery_meta span.glyphicon").click(function () {
					var str = prompt("输入标签名称，只允许字母和下划线", "");
					if (str.length < 2) {
						alert_error("名称长度不小于2个字符");
					} else {
						str = str.toLowerCase();
						if (!/^[a-z_]+$/.test(str)) {
							alert_error("不符合规范");
						} else if ($("*[name='meta\[" + str + "\]'").length !== 0) {
							alert_error("该标签已存在");
						} else {
							$(".gallery_meta").append("<div class='form-group'><label class='control-label col-sm-2'>" + str + "</label>" +
								"<div class='col-sm-10'><input class='form-control' name='meta[" + str + "]' type='text'></div></div>");
						}
					}
				}
			)
			;
		});
		function gallery_add_tag(id) {
			var select = $("#Gallery_add_tag_" + id);
			var tag = select.val();
			if (tag.length > 0) {
				$.post('<?php echo get_url("UserApi","gallery_add_tag")?>', {id: id, tag: tag}, function (data) {
					if (data['status']) {
						alert_notice("标签添加成功");
						select.val("");
						select = select.parent().parent().parent().find(".help-block");
						for (var i in data['content']) {
							var tag_name = data['content'][i];
							select.append('<span class="label label-info">' + tag_name +
								'<span onclick="gallery_remove_tag(' + id + ',\'' + tag_name + '\',this)" style="cursor: pointer;" class="glyphicon glyphicon-remove"></span></span>');
						}
					} else {
						alert_error(data['msg'], '添加标签失败');
					}
				});
			}
		}

		function gallery_remove_tag(id, tag, elem) {
			$.post('<?php echo get_url("UserApi","gallery_remove_tag")?>', {id: id, tag: tag}, function (data) {
				if (data['status']) {
					alert_notice("删除[" + tag + "]标签成功");
					$(elem).parent().remove();
				} else {
					alert_error(data['msg']);
				}
			});
		}
	</script>
<?php endif;
