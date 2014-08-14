<?php
/**
 * @var array  $__content
 * @var string $__error
 */
if(!empty($__error)):?>
	<div class="well text-danger"><h3><?php echo $__error ?></h3></div>
<?php
else:
	if($__content['from_users_id'] > 0){
		$from_user = \ULib\User::getUser($__content['from_users_id']);
	} else{
		$from_user = NULL;
	}
	$to_user = \ULib\User::getUser($__content['to_users_id']);
	?>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h2 class="panel-title">消息阅读</h2>
		</div>
		<div class="panel-body">
			<?php if(!empty($__content['msg_title'])): ?>
				<h3 class=""><?php echo $__content['msg_title'] ?></h3>
			<?php endif; ?>
			<div class="well well-sm">
				<?php echo $__content['msg_content'] ?>
			</div>
			<div class="well well-sm">
				<p>创建时间：<span class="text-info"><?php echo $__content['msg_datetime'] ?></span></p>

				<p><?php if($from_user !== NULL){ ?>
						发送人：<a title="查看主页" href="<?php echo user_link($from_user->getName()) ?>"><?php echo $from_user->getAliases() ?>(<?php echo $from_user->getName() ?>)</a>
					<?php } else{ ?>
						发送人：<span class="text-warning">系统信息</span>
					<?php } ?>
					&nbsp;&nbsp;收件人：<a title="查看主页" href="<?php echo user_link($to_user->getName()) ?>"><?php echo $to_user->getAliases() ?>(<?php echo $to_user->getName() ?>)</a></p>
			</div>
			<?php if($from_user !== NULL && $to_user->getId() === login_user()->getId()): ?>
				<div class="well well-sm">
					<form id="MessageSendForm" method="post" action="<?php echo get_url("UserApi", "message_send") ?>">
						<div class="form-group">
							<label class="sr-only" for="InputTitle">标题</label>
							<input type="text" class="form-control" name="title" id="InputTitle" value="回复：<?php echo $__content['msg_title'] ?>[<?php echo $__content['id'] ?>]">
						</div>
						<div class="form-group">
							<label for="InputContent" class="sr-only">回复该消息</label>
							<textarea class="form-control" id="InputContent" name="content" rows="4"></textarea>
						</div>
						<div class="form-group text-right">
							<button type="submit" class="btn-primary btn">回复</button>
						</div>
						<input type="hidden" name="users" value="<?php echo $from_user->getName() ?>">
					</form>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>