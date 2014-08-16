# 简易图片分享系统
这是一个图片存储的网站系统，具有简单的时间线功能，同时有完善的通知中心。

# 安装步骤
	cd config
	mv all-simple.php all.php
	vim all.php //配置正确的数据库连接信息
	访问 http://xxx.xxx/install.php 进行安装
安装后会创建一个管理员账户，用该账户登录可访问后台，然后进行详细的配置。

# 网站配置
由于完全依赖于伪静态，所以必须对文件进行重定向。
### nginx配置
	location / {
		if (!-f $request_filename){
			rewrite (.*) /index.php;
		}
	}
	# 重定向404页面，防止静态资源404无法获取
	error_page 404 /index.php;

### Apache 配置
	RewriteEngine On
	RewriteBase /

	#不存在的文件直接重定向
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule ^(.*)$ /index.php [L]
同时，对于Apache也可以使用PATH_INFO的形式，如 `index.php/Home` .

# 问题反馈
### 详情页面
http://www.loveyu.net/Linger
### 反馈
http://www.loveyu.org/3273.html
