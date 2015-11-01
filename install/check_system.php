<?php
$check_system = true;
$check_result = check_status();
foreach($check_result as $name => $value){
	if($value !== true){
		$check_system = false;
		break;
	}
}
if($check_system === false):?>
	<h1>安装环境检查结果</h1>
	<table class="table table-condensed">
		<thead>
		<tr>
			<th>组件</th>
			<th>状态</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach($check_result as $name => $value): ?>
			<tr class="<?php echo $value ? "success" : "danger" ?>">
				<td><?php echo $name ?></td>
				<td class="text-<?php echo $value ? "success" : "danger" ?>"><?php echo $value ? "成功" : "失败" ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<p class="alert alert-warning"><strong>组件缺失提示：</strong>程序运行的基本要求，如果无法达到，可能会导致运行异常</p>
<?php endif;
return $check_system;

function check_status(){
	return array(
		'php_5.4' => version_compare(PHP_VERSION, '5.4.0', '>'),
		'pdo_mysql' => extension_loaded('pdo_mysql'),
		'gd' => extension_loaded('gd'),
		'mbstring' => extension_loaded('mbstring'),
		'gettext' => extension_loaded('gettext') && function_exists('_'),
		'curl' => extension_loaded('curl'),
	);
}