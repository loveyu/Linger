# 简易图片分享系统
这是一个图片存储的网站系统，具有简单的时间线功能，同时有完善的通知中心。

## 系统需求
PHP版本必须大于等与`5.4`
Mysql必须支持`INNODB`引擎，数据库默认编码请指定为`utf8mb4`，集合为`utf8mb4_unicode_ci`，创建语句如下
```sql
CREATE DATABASE `database_name` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */
```

## 安装步骤

```
cd config
mv all-simple.php all.php
vim all.php //配置正确的数据库连接信息
访问 http://xxx.xxx/install.php 进行安装
```
安装后会创建一个管理员账户，用该账户登录可访问后台，然后进行详细的配置。

## 网站配置
由于完全依赖于伪静态，所以必须对文件进行重定向。

### nginx配置
```
location / {
	if (!-f $request_filename){
		rewrite (.*) /index.php;
	}
}
# 重定向404页面，防止静态资源404无法获取
error_page 404 /index.php;
```

### Apache 配置
```
RewriteEngine On
RewriteBase /

#不存在的文件直接重定向
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ /index.php [L]
```

同时，对于Apache也可以使用PATH_INFO的形式，如 `index.php/Home` .

系统默认将Web配置目录放到web文件夹下，其他对应的文件sys,app,install等文件均在web目录的上级目录，
这是为了安全性的考虑，如果有需要将文件调整到一个目录，可具体参考`sys/config.php`文件调整目录结构，并调整index.php文件的具体参数
同时如果未安装系统，同时可能需要修改install.php中的文件参数。

## 问题反馈
### 详情页面
http://www.loveyu.net/Linger
### 反馈
http://www.loveyu.org/3273.html
