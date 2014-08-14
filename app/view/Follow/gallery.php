<?php
/**
 * @var array $__list
 * @var int[] $__count
 */
?>
<div class="panel panel-info">
	<div class="panel-heading">
		<h2 class="panel-title">我关注的图集</h2>
	</div>
	<div class="panel-body">
		<?php if($__count['max'] > 1 || ($__count['page'] === -1 && $__count['count'] !== 0)): ?>
			<div class="well well-sm clearfix">
				<ul class="pagination" style="display: inline;"><?php echo theme()->createNav($__count['page'], $__count['max'], $__count['count'], get_url("Follow", 'gallery') . "?page={number}"); ?></ul>
			</div>
		<?php endif;
		if($__count['count'] === 0): ?>
			<h3 class="text-danger">你当前没有关注任何图集！</h3>
		<?php
		elseif ($__count['page'] === -1):?>
			<h3 class="text-danger">当前页面不存在！请返回上一页！</h3>
		<?php
		else: ?>
			<table class="table table-striped table-hover">
				<tbody>
				<?php foreach($__list as &$v):
					/**
					 * @var \ULib\User $user
					 */
					$user = $v['user'];
					$follow = $v['follow']; ?>
					<tr id="Follow_tr_<?php echo $follow['gallery_id'] ?>">
						<td style="width: 40px">
							<img src="<?php echo $user->getAvatar(40) ?>" width="40" height="40">
						</td>
						<td>
							<div>
								<p>图集标题：<a rel="external" href="<?php echo gallery_link($follow['gallery_id']) ?>"><?php echo $follow['gallery_title'] ?></a>
									<?php if($follow['gallery_front_cover'] > 0): ?>
										【<a title="查看封面" href="#" onclick="return view_front_cover(<?php echo $follow['gallery_front_cover'] ?>)" class="glyphicon glyphicon-eye-open"></a>】
									<?php endif; ?>
								</p>

								<p>图集描述：<code><?php echo $follow['gallery_description'] ?></code></p>

								<p>所属用户：<a rel="external" href="<?php echo user_link($user->getName()) ?>"><?php echo $user->getAliases() ?></a> (<?php echo $user->getName() ?>)</p>

								<p>关注时间：<span><?php echo $follow['follow_time'] ?></span></p>
							</div>
						</td>
						<td class="text-right">
							<button class="btn btn-danger" onclick="follow_cancel(<?php echo $follow['gallery_id'] ?>,'<?php echo $follow['gallery_title'] ?>')">取消关注</button>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<script>
				function follow_cancel(id, title) {
					if (confirm("你确实取消关注该图集 {" + title + "} 么?")) {
						$.post("<?php echo get_url("UserApi","follow_gallery_cancel")?>", {id: id}, function (data) {
							if (data['status']) {
								alert_notice("你已取消对其关注！");
								$("#Follow_tr_" + id).hide("slow", function () {
									$(this).remove();
								});
							} else {
								alert_error(data['msg'], "取消关注失败！");
							}
						});
					}
				}
				function view_front_cover(id) {
					$.get("<?php echo get_url("UserApi","picture_url")?>", {id: id}, function (data) {
						if (data['status']) {
							modal_show("封面图片", '<img class="img-thumbnail" src="' + data['content']['pic_display_url'] + '" alt="" style="display:block;width:100%">');
						} else {
							alert_error(data['msg'], "加载图片封面出错");
						}
					});
					return false;
				}
			</script>
		<?php endif; ?>
	</div>
</div>