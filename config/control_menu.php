<?php
return [
	'control_menu' => [
		createMenu('控制面板', 'dashboard', '/'),
		createMenu('图片管理', 'image', '', false, [
			createMenu('图片列表', 'picture-o', '/picture'),
			createMenu('统计', 'picture-o', '/picture/count'),
		]),
		createMenu('图集管理', 'photo', '', false, [
			createMenu('图片列表', 'picture-o', '/picture'),
			createMenu('统计', 'picture-o', '/picture/count'),
		]),
		createMenu('用户管理', 'users', '', false, [
			createMenu('用户列表', 'user-secret', '/picture'),
			createMenu('添加用户', 'plus-square-o', '/picture/count'),
		]),
		createMenu('消息管理', 'envelope', '', false, [
			createMenu('消息列表', 'envelope-o', '/picture'),
			createMenu('发送消息', 'plus-square-o', '/picture/count'),
		])
	]
];