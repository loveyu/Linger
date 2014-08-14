<?php
$req = req()->_plain();
?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">发送信息</h2>
	</div>

	<div class="panel-body">
		<form method="post" id="MessageSendForm" class="form-horizontal" action="<?php echo get_url("UserApi", "message_send") ?>">
			<fieldset>
				<div class="form-group">
					<label class="col-sm-1 control-label" for="InputTitle">标题</label>

					<div class="col-sm-11">
						<input id="InputTitle" name="title" placeholder="输入标题，可省略" value="<?php echo $req->get('s_title') ?>" class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-1" for="InputUser">用户</label>

					<div class="col-sm-11">
						<input id="InputUser" name="users" class="form-control" value="<?php echo $req->get('s_to') ?>" placeholder="用户的名称，多个用户空白符分开">

						<p class="help-block">只允许发送给已关注的用户，或被关注的用户,一次最多5个，每次间隔30秒！</p>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-1" for="InputContent">内容</label>

					<div class="col-sm-11">
						<p class="help-block">Markdown语法支持</p>
						<textarea class="form-control" id="InputContent" name="content" rows="4"><?php echo $req->get('s_content') ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-1 col-sm-11">
						<button class="btn btn-primary" type="submit">发送</button>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>