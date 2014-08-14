<?php
/**
 * @var \ULib\FeedInterface[] $__list
 * @var int[]                 $__count
 */
?>
<div class="panel panel-primary" id="MyFeed">
	<div class="panel-heading">
		<h2 class="panel-title">我的分享</h2>
	</div>

	<div class="panel-body">
		<?php if($__count['max'] > 1 || ($__count['page'] === -1 && $__count['count'] !== 0)): ?>
			<div class="well well-sm clearfix">
				<ul class="pagination" style="display: inline;">
					<?php echo theme()->createNav($__count['page'], $__count['max'], $__count['count'], get_url("Follow", 'feed') . "?page={number}"); ?></ul>
			</div>
		<?php endif;
		if($__count['count'] === 0): ?>
			<h3 class="text-danger">你没有可以显示的评论!</h3>
		<?php elseif($__count['page'] === -1): ?>
			<h3 class="text-danger">当前页面不存在！请返回上一页！</h3>
		<?php
		else: ?>
			<?php foreach($__list as $id => &$v):
				$other = "";
				$info = $v->getInfo();
				?>
				<?php switch(strtolower($v->getAction())):
				case 'talk':
					?>
					<div id="Share_<?php echo $id ?>" class="talk share_box">
						<button class="close" type="button">&times;</button>
						<h4><span>[<?php echo $id ?>]</span>随手写到</h4>

						<div class="content">
							<?php echo $info['content'] ?>
						</div>
						<div class="time"><span class="glyphicon-time glyphicon"></span><?php echo $info['time'] ?></div>
					</div>
					<?php break;
				case 'sharegallery':
					if($info['s_uid'] != $info['o_uid']){
						$ou = \ULib\User::getUser($info['o_uid']);
						$other = "<a rel='external' href='" . user_link($ou->getName()) . "'>" . $ou->getAliases() . "</a> ";
					}
					?>
					<div id="Share_<?php echo $id ?>" class="s_gallery share_box">
						<button class="close" type="button">&times;</button>
						<h4><span>[<?php echo $id ?>]</span>分享了<?php echo $other; ?>图集
							<a rel="external" href="<?php echo $info['link'] ?>" class="glyphicon glyphicon-link"><?php echo $info['title'] ?></a>
						</h4>
						<?php if(isset($info['front_cover']['pic_thumbnails_url'])): ?>
							<div class="img"><img class="img-thumbnail" src="<?php echo $info['front_cover']['pic_thumbnails_url'] ?>" alt=""/></div>
						<?php endif; ?>
						<div class="time"><span class="glyphicon-time glyphicon"></span><?php echo $info['time'] ?></div>
					</div>
					<?php
					break;
				case 'sharepicture':
					if($info['share_users_id'] != $info['object_users_id']){
						$ou = \ULib\User::getUser($info['object_users_id']);
						$other = "<a  rel='external' href='" . user_link($ou->getName()) . "'>" . $ou->getAliases() . "</a> ";
					}
					?>
					<div id="Share_<?php echo $id ?>" class="s_picture share_box">
						<button class="close" type="button">&times;</button>
						<h4><span>[<?php echo $id ?>]</span>分享了<?php echo $other; ?>图片
							<a rel="external" href="<?php echo $info['info'][0]['link'] ?>" class="glyphicon glyphicon-link"><?php
								echo $info['info'][0]['name'] ? : ("第" . $info['info'][0]['id'] . "号图片") ?></a>
						</h4>
						<?php if(isset($info['info'][0]['thumbnail_url'])): ?>
							<div class="img"><img class="img-thumbnail" src="<?php echo $info['info'][0]['thumbnail_url'] ?>" alt=""/></div>
						<?php endif; ?>
						<div class="time"><span class="glyphicon-time glyphicon"></span><?php echo $info['time'] ?></div>
					</div>
					<?php
					break;
				case 'gallery':
					?>
					<div id="Share_<?php echo $id ?>" class="gallery share_box">
						<button class="close" type="button">&times;</button>
						<h4><span>[<?php echo $id ?>]</span><?php echo $info['is_update'] ? "更新" : "创建" ?>了图集
							<a rel="external" href="<?php echo $info['link'] ?>" class="glyphicon glyphicon-link"><?php
								echo $info['title'] ?></a>
						</h4>

						<div class="img"><img class="img-thumbnail" src="<?php echo $info['front_cover']['pic_thumbnails_url'] ?>" alt=""/></div>
						<div class="time"><span class="glyphicon-time glyphicon"></span><?php echo $info['time'] ?></div>
					</div>
					<?php
					break;
				default:
			endswitch; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<script>
			$(function () {
				$(".share_box button.close").click(function () {
					var id = $(this).parent()[0].id.substr(6);
					if (confirm("确定删除 [" + id + "] 号分享?")) {
						$.post('<?php echo get_url('UserApi','share_delete')?>', {id: id}, function (data) {
							if (data['status']) {
								alert_notice("删除成功");
								$("#Share_" + id).hide('slow', function () {
									$(this).remove();
								});
							} else {
								alert_error(data['msg'], '删除失败');
							}
						});
					}
				});
			});
		</script>
	</div>
</div>