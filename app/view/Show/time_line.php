<?php
$user = login_user();
?>
<div class="row" id="Time-Line">
	<div class="col-sm-4">
		<div class="user_info text-center">
			<img class="img-circle" src="<?php echo $user->getAvatar(80) ?>" width="80" height="80" alt="avatar"/>

			<p class="name"><strong><?php echo $user->getAliases() ?></strong></p>

			<p><a href="<?php echo user_link($user->getName()) ?>">@<?php echo $user->getName() ?></a></p>
		</div>

		<div class="send_talk well well-sm">
			<form id="TalkShareForm" action="<?php echo get_url("UserApi", "share_talk") ?>" method="post">
				<div class="form-group">
					<label class="control-label sr-only" for="TalkShare">分享：</label>
					<textarea onkeydown="keyTrySubmit();" class="form-control" name="content" rows="4" id="TalkShare" placeholder="分享点什么"></textarea>
				</div>

				<div class="form-group text-right">
					<button class="btn btn-primary btn-sm" id="TalkShareFormSubmit" type="submit">分享</button>
				</div>
			</form>
		</div>
	</div>
	<div class="col-sm-8 display_content">
		<div class="get_new"><a href="#"><span>获取新动态</span></a></div>
		<div class="content"></div>
		<div class="get_more"><a href="#"><span>更多...</span></a></div>
	</div>
</div>
<script>
	function keyTrySubmit() {
		if (event.ctrlKey && event.keyCode == 13) {
			document.getElementById('TalkShareFormSubmit').click();
		}
	}
	$(function () {
		var time_line = new TimeLine('<?php echo get_url("UserApi",'time_line')?>', "#Time-Line .content");
		time_line.init_load();
		$("#TalkShareForm").ajaxForm(function (data) {
			if (data['status']) {
				$("#TalkShare").val("");
				time_line.get_new();
			} else {
				modal_show("<span class='text-danger'>分享失败</span>", data['msg']);
			}
		});
		$("#Time-Line .get_new a").click(function () {
			time_line.get_new();
			return false;
		});
		$("#Time-Line .get_more a").click(function () {
			time_line.get_more();
			return false;
		});
	});
</script>