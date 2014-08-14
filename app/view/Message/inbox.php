<?php
/**
 * @var array $__list
 * @var int[] $__count
 */
?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">收到的信息</h2>
	</div>

	<div class="panel-body">
		<?php if($__count['max'] > 1 || ($__count['page'] === -1 && $__count['count'] !== 0)): ?>
			<div class="well well-sm clearfix">
				<ul class="pagination" style="display: inline;">
					<?php echo theme()->createNav($__count['page'], $__count['max'], $__count['count'], get_url("Message", 'inbox') . "?page={number}"); ?></ul>
			</div>
		<?php endif;
		if($__count['count'] === 0): ?>
			<h3 class="text-danger">你没有可以显示的信息!</h3>
		<?php
		elseif($__count['page'] === -1):?>
			<h3 class="text-danger">当前页面不存在！请返回上一页！</h3>
		<?php
		else: ?>
			<table class="table table-striped table-hover">
				<?php foreach($__list as $v): ?>
					<tr<?php echo $v['msg_is_read'] ? "" : " class='success'" ?> id="Box_tr_<?php echo $v['msg_id'] ?>">
						<td>
							<p>标题：<span class="text-warning">[<?php echo $v['msg_id'] ?>]</span> <a href="#" title="简要查看" onclick="return message_read(<?php echo $v['msg_id'] ?>)"><?php echo $v['msg_title'] ? $v['msg_title'] : "无标题信息" ?></a></p>

							<p>
								<?php if($v['user_id'] > 0){ ?>
									<span class="glyphicon glyphicon-user"></span>From:<a title="查看主页" rel="external" href="<?php echo user_link($v['user_name']) ?>"><?php echo $v['user_aliases'] ?></a>
									<a class="glyphicon glyphicon-share-alt" href="<?php echo get_url("Message", "view") ?>?id=<?php echo $v['msg_id'] ?>#reply" title="回复信息！"></a> ,
								<?php } else{ ?>
									<span class="glyphicon glyphicon-user"></span>From:<span class="text-warning">系统信息</span>
								<?php } ?>
								<span class="glyphicon glyphicon-time"></span>Time：<span class="text-info"><?php echo convert_time($v['msg_datetime']) ?></span>,
								<a class="text-danger" href="#" onclick="return message_delete(<?php echo $v['msg_id'] ?>)"><span class="glyphicon glyphicon-remove-sign"></span>删除</a></p>
						</td>
						<td class="text-right">
							<a class="label label-primary" href="<?php echo get_url("Message", "view") ?>?id=<?php echo $v['msg_id'] ?>">阅读</a><br/>
							<?php if(!$v['msg_is_read']): ?>
								<a href="#" class="set_read" onclick="return message_set_read_flag(<?php echo $v['msg_id'] ?>)">标记为已读</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>
	</div>
</div>