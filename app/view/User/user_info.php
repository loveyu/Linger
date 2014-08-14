<?php
$user = login_user(); ?>
<div class="panel panel-info">
	<div class="panel-heading">
		<h2 class="panel-title">用户信息概要</h2>
	</div>
	<div class="panel-body">
		<table class="user_info_table table">
			<tr>
				<th>头像</th>
				<td><a href="<?php echo get_url("User", "edit_avatar") ?>"><img class="img-rounded avatar"
																				src="<?php echo $user->getAvatar(); ?>"></a></td>
			</tr>
			<tr>
				<th>用户ID</th>
				<td><?php echo $user->getId(); ?> <span class="text-info">(<?php echo $user->getStatusInfo($user->getStatus()) ?>)</span>
					<a href="<?php echo user_link($user->getName()) ?>">信息主页</a>
				</td>
			</tr>
			<tr>
				<th>用户名</th>
				<td><?php echo $user->getName(); ?></td>
			</tr>
			<tr>
				<th>别名</th>
				<td><?php echo $user->getAliases(); ?></td>
			</tr>
			<tr>
				<th>邮箱</th>
				<td><?php echo $user->getEmail(); ?></td>
			</tr>
			<tr>
				<th>个人主页</th>
				<td><?php echo $user->getUrl(); ?></td>
			</tr>
			<tr>
				<th>上次登录时间</th>
				<td><?php echo $user->getLastLoginTime(); ?></td>
			</tr>
			<tr>
				<th>上次登录IP</th>
				<td><?php echo $user->getLastLoginIp(); ?></td>
			</tr>

		</table>
	</div>
</div>