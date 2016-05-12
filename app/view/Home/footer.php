<!--底部开始-->
</div>
<footer class="site_footer hidden-print" role="contentinfo">
	<div class="container">
		<p>&copy;&nbsp;Copyright <a href="<?php echo get_url() ?>"><?php echo site_title(); ?></a> 2013-<?php echo date("Y") ?>. Power by <a href="https://www.loveyu.net/Linger">linger</a>.</p>
		<?php if(_Debug_ && is_login() && login_user()->Permission('Control')): ?>
			<p>
				<span class="text-info">页面加载 <?php echo c()->getTimer()->get_second() ?> 秒， 数据库查询 <?php
					echo db()->get_query_count(); ?>次。</span>
			</p>
		<?php endif; ?>
	</div>
	<?php footer_hook(); ?>
</footer>
</body>
</html>