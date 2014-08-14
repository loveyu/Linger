<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-12
 * Time: 下午12:32
 * Filename: display.php
 * 用于显示评论列表
 */
?>
<div id="Comment">
	<?php
	/**
	 * @var ULib\CommentData $this
	 * @var bool             $__hidden_form 是否影藏表单
	 * @var array            $__count_info  统计信息
	 * @var string           $__action      提交地址
	 * @var string           $__id          页面ID
	 * @var string           $__error       错误信息
	 * @var string           $__type        评论类型
	 * @var bool             $__is_closed   评论是否被关闭
	 */
	$this->show_comment();
	if(!$__is_closed && !empty($__error)):?>
		<div class="comment-error text-center text-danger hidden-print"><?php echo $__error ?></div>
	<?php endif; ?>
	<ul class="pager left">
		<?php if($__count_info['now'] > 1): ?>
			<li class="previous"><a href="<?php echo $this->get_comment_pager($__count_info['now'] - 1) ?>">&larr;上一页</a></li>
		<?php endif;
		if($__count_info['max'] > $__count_info['now']): ?>
			<li class="next"><a href="<?php echo $this->get_comment_pager($__count_info['now'] + 1) ?>">下一页&rarr;</a></li>
		<?php endif; ?>
	</ul>
	<?php if(!$__hidden_form): ?>
		<?php if($__is_closed): ?>
			<p class="text-danger">评论被关闭！</p>
		<?php else: ?>
			<form id="CommentForm" action="<?php echo $__action ?>" method="post" class="clearfix hidden-print">
				<textarea onkeydown="keyTrySubmit();" rows="3" name="comment" class="form-control" placeholder="输入评论内容"></textarea>
				<input type="hidden" name="id" value="<?php echo $__id ?>">
				<input type="hidden" name="reply" value="0">
				<input type="hidden" name="type" value="<?php echo $__type ?>">
				<button id="CommentReplyCancel" style="display: none;" type="button" class="btn btn-warning pull-left">取消回复</button>
				<button id="CommentSubmit" type="submit" class="btn btn-success pull-right">评论/Ctrl+Enter</button>
			</form>
			<script>
				function keyTrySubmit() {
					if (event.ctrlKey && event.keyCode == 13) {
						document.getElementById('CommentSubmit').click();
					}
				}
				$(function () {
					$("#CommentForm").submit(function () {
						if (!IS_LOGIN) {
							location.href = '<?php echo redirect_to_login(true);?>';
						}
						var data = $("form#CommentForm").serialize();
						$.post(this.action, data, function (data) {
							if (data['status']) {
								location.reload();
							} else {
								alert(data['msg']);
							}
						});
						return false;
					});
					$(".comment-reply").click(function () {
						var i = this.href.indexOf('#');
						var id = +this.href.substr(12 + i);
						$("form#CommentForm input[name='reply']").val(id);
						$("form#CommentForm").appendTo("#comment-reply-" + id);
						$("form#CommentForm textarea").focus();
						$("#CommentReplyCancel").show();
						return false;
					});
					$(".comment-action-del").click(function () {
						if (!confirm("你确定删除该评论?")) {
							return false;
						}
						var i = this.href.indexOf('#');
						var id = +this.href.substr(12 + i);
						$.post('<?php echo get_url("CommentApi","delete")?>', {id: id, type: '<?php echo $__type?>'}, function (data) {
							if (data['status']) {
								$("#Comment-id-" + id + " .comment-msg:first").hide('slow', function () {
									this.remove();
//								setTimeout(function () {
//									location.reload();
//								}, 1000);
								})
							} else {
								alert("删除出错:" + data['msg']);
							}
						});
						return false;
					});

					$(".comment-action-top").click(function () {
						var i = this.href.indexOf('#');
						var id = +this.href.substr(12 + i);
						var s_o = this;
						var list = Storage.get("Comment_top_list");
						if (list === null) {
							list = [];
						} else {
							list = list.split(',');
							if ($.inArray(id + '', list) > -1) {
								alert('已经顶过了');
								return;
							}
						}
						$.post('<?php echo get_url("CommentApi","top")?>', {id: id, type: '<?php echo $__type?>'}, function (data) {
							if (data['status']) {
								$(s_o).find("span span").html("[" + data['content'] + "]");
								list.push(id);
								Storage.set("Comment_top_list", list.join(','));
							} else {
								alert("出错了:" + data['msg']);
							}
						});
						return false;
					});
					$(".comment-action-like").click(function () {
						var i = this.href.indexOf('#');
						var id = +this.href.substr(12 + i);
						var s_o = this;

						$.post('<?php echo get_url("CommentApi","like")?>', {id: id, type: '<?php echo $__type?>'}, function (data) {
							if (data['status']) {
								var now_number = $(s_o).find("span span").html().substr(1);
								now_number = +now_number.substr(0, now_number.length - 1);
								if ($(s_o).find("span:first").hasClass('glyphicon-heart-empty')) {
									//改为喜欢
									$(s_o).find("span:first").removeClass('glyphicon-heart-empty').addClass('glyphicon-heart').html("喜欢<span>[" + (now_number - 1) + "]</span>");
								} else {
									//改为不喜欢
									$(s_o).find("span:first").removeClass('glyphicon-heart').addClass('glyphicon-heart-empty').html("取消喜欢<span>[" + (now_number + 1) + "]</span>");
								}
							} else {
								alert("出错了:" + data['msg']);
							}
						});
						return false;
					});

					$("#CommentReplyCancel").click(function () {
						$("#CommentReplyCancel").hide();
						$("form#CommentForm input[name='reply']").val(0);
						$("form#CommentForm").appendTo("#Comment");
					});
				});
			</script>
		<?php endif; ?>
	<?php endif; ?>
</div>