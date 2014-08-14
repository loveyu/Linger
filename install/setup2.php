<h2>第二步，数据库导入</h2>
<p class="well-sm well text-danger">下列操作会对数据库进行更改，将导致原有数据库完全被删除且无法恢复，做好心理准备！</p>

<p class="well-sm well"><a href="#" class="import_sql btn btn-primary">开始导入系统表结构</a><pre><code class="text-danger" style="margin-left: 15px"></code></pre></p>

<script>
	$(".import_sql").click(function () {
		$(this).addClass('disabled');
		var span = $("code");
		span.html("数据导入中.....");
		$.get('install.php', {setup: 'import_sql'}, function (data) {
			if (data == 'true') {
				location.href = "install.php?setup=3";
			} else {
				$(".import_sql").removeClass('disabled');
				span.html(data);
			}
		});
		return false;
	});
</script>