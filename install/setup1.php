<?php
c_lib()->load('sql', 'char');
$sql = new \CLib\Sql(cfg()->get('sql', 'write'), cfg()->get('sql', 'read'));
$status = $sql->status();
$next = true;
if(include_once(__DIR__ . "/check_system.php")):?>
	<h2>第一步，配置数据库</h2>

	<?php
	if($status){
		echo "<p class='well well-sm text-success'>" . (cfg()->get('sql', 'read') !== NULL ? "[Read]" : "") . "数据库已连接，请继续下一步。</p>";
	} else{
		$next = false;
		$err = $sql->ex_message();
		$err = \CLib\Char::Convert(\CLib\Char::GetCoding($err), "UTF-8", $err);
		echo "<p class='well text-danger'>" . (cfg()->get('sql', 'read') !== NULL ? "[Read]" : "") . "数据库连接失败，请检查配置文件，手动修改对应属性,请优先保证写数据库正常！<br />错误信息：$err</p>";
	}
	if(cfg()->get('sql', 'read') !== NULL){
		if(!$sql->open_write()){
			$next = false;
			$err = $sql->ex_message();
			$err = \CLib\Char::Convert(\CLib\Char::GetCoding($err), "UTF-8", $err);
			echo "<p class='well text-danger'>[Write]写数据库异常，请保证写数据库能正常连接！<br>错误信息：$err</p>";
		} else{
			echo "<p class='well well-sm text-success'>[Write]数据库已连接，请继续下一步。</p>";
		}
	}
	if($next){
		$session->set('install', [
			'number' => '2',
			'list' => []
		]);
		echo "<p class='text-right'><a href='install.php?setup=2' class='btn btn-primary'>继续第二步</a></p>";
	} else{
		?>
		<p>配置错误，请详细查看规则，如下，当只读数据库不存在时将其注释！</p>
		<pre><code>'sql' => [
				'write' => [
				'database_type' => 'mysql',
				//服务器类型 支持 mysql sqlite pgsql mssql sybase
				'server' => 'localhost',
				//服务器地址
				'username' => 'root',
				//用户名
				'password' => '123456',
				//密码
				'database_file' => '',
				//数据库文件, SqLite 专有文件
				'charset' => 'utf8',
				//编码
				'database_name' => 'pitus',
				//数据库名
				'option' => [ //PDO选项
				PDO::ATTR_CASE => PDO::CASE_NATURAL,
				PDO::ATTR_TIMEOUT => 5
				],
				],
				'read' => [
				'database_type' => 'mysql',
				'server' => 'localhost',
				'username' => 'root',
				'password' => '123456',
				'database_file' => '',
				'charset' => 'utf8',
				'database_name' => 'pitus',
				'option' => [ //PDO选项
				PDO::ATTR_CASE => PDO::CASE_NATURAL,
				PDO::ATTR_TIMEOUT => 5
				],
				]
				]</code></pre>
		<?php
	}
endif;
?>