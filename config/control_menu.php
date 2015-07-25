<?php
return [
	'control_menu' => [
		createMenu('控制面板', 'dashboard', 'dashboard', '/'),
		createMenu('图片管理', 'picture', 'image', '', false, [
			createMenu('图片列表', 'picture-list', 'picture-o', '/picture_list'),
			createMenu('统计', 'picture-count', 'picture-o', '/picture_count'),
		]),
		createMenu('图集管理', 'gallery', 'photo', '', false, [
			createMenu('图片列表', 'gallery-list', 'picture-o', '/gallery_list'),
			createMenu('统计', 'gallery-count', 'picture-o', '/gallery_count'),
		]),
		createMenu('用户管理', 'user', 'users', '', false, [
			createMenu('用户列表', 'user-list', 'user-secret', '/user_list'),
			createMenu('添加用户', 'user-add', 'plus-square-o', '/user_add'),
		]),
		createMenu('消息管理', 'message', 'envelope', '', false, [
			createMenu('消息列表', 'message-list', 'envelope-o', '/message_list'),
			createMenu('发送消息', 'message-send', 'plus-square-o', '/message_send'),
		])
	]
];