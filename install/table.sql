
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for comments
-- ----------------------------
DROP TABLE
IF EXISTS `comments`;

CREATE TABLE `comments` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	`comment_content` LONGTEXT NOT NULL,
	`comment_time` datetime NOT NULL,
	`comment_parent` BIGINT (20) UNSIGNED DEFAULT '0',
	`comment_status` INT (11) NOT NULL DEFAULT '0',
	`comment_ip` VARBINARY (128) NOT NULL,
	`comment_agent` VARCHAR (200) NOT NULL,
	`comment_like_count` INT (10) UNSIGNED NOT NULL DEFAULT '0',
	`comment_top` INT (10) UNSIGNED NOT NULL DEFAULT '0',
	`comment_parent_top` BIGINT (11) UNSIGNED DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `fk_comments_users1_idx` (`users_id`),
	KEY `fk_comments_comments1_idx` (`comment_parent`),
	KEY `fk_comments_comments2_idx` (`comment_parent_top`),
	CONSTRAINT `fk_comments_comments1` FOREIGN KEY (`comment_parent`) REFERENCES `comments` (`id`) ON DELETE
SET NULL ON UPDATE CASCADE,
 CONSTRAINT `fk_comments_comments2` FOREIGN KEY (`comment_parent_top`) REFERENCES `comments` (`id`) ON DELETE
SET NULL ON UPDATE CASCADE,
 CONSTRAINT `fk_comments_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for comment_meta
-- ----------------------------
DROP TABLE
IF EXISTS `comment_meta`;

CREATE TABLE `comment_meta` (
	`meta_id` BIGINT (20) UNSIGNED NOT NULL,
	`comments_id` BIGINT (20) UNSIGNED NOT NULL,
	`meta_key` VARCHAR (128) NOT NULL,
	`meta_value` LONGTEXT NOT NULL,
	PRIMARY KEY (`meta_id`),
	KEY `fk_comment_meta_comments1_idx` (`comments_id`),
	CONSTRAINT `fk_comment_meta_comments1` FOREIGN KEY (`comments_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for feed
-- ----------------------------
DROP TABLE
IF EXISTS `feed`;

CREATE TABLE `feed` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`action` VARCHAR (200) NOT NULL,
	`content` LONGTEXT NOT NULL,
	`sid` VARCHAR (256) DEFAULT NULL,
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	`time` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `fk_feed_users1_idx` (`users_id`),
	CONSTRAINT `fk_feed_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for gallery
-- ----------------------------
DROP TABLE
IF EXISTS `gallery`;

CREATE TABLE `gallery` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	`gallery_title` VARCHAR (100) NOT NULL,
	`gallery_description` VARCHAR (1000) NOT NULL,
	`gallery_create_time` datetime NOT NULL,
	`gallery_like_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	`gallery_follow_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	`gallery_comment_count` INT (11) UNSIGNED NOT NULL DEFAULT '0',
	`gallery_comment_status` INT (11) NOT NULL DEFAULT '1',
	`gallery_update_time` datetime NOT NULL,
	`gallery_front_cover` BIGINT (20) UNSIGNED DEFAULT NULL,
	`gallery_status` INT (11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `fk_gallery_users1_idx` (`users_id`),
	KEY `fk_gallery_pictures1_idx` (`gallery_front_cover`),
	CONSTRAINT `fk_gallery_pictures1` FOREIGN KEY (`gallery_front_cover`) REFERENCES `pictures` (`id`) ON DELETE
SET NULL ON UPDATE CASCADE,
 CONSTRAINT `fk_gallery_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for gallery_has_comments
-- ----------------------------
DROP TABLE
IF EXISTS `gallery_has_comments`;

CREATE TABLE `gallery_has_comments` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`gallery_id` BIGINT (20) UNSIGNED NOT NULL,
	`comments_id` BIGINT (20) UNSIGNED NOT NULL,
	`object_users_id` BIGINT (20) UNSIGNED NOT NULL,
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	KEY `fk_gallery_has_comments_comments1_idx` (`comments_id`),
	KEY `fk_gallery_has_comments_gallery1_idx` (`gallery_id`),
	KEY `fk_gallery_has_comments_users1_idx` (`users_id`),
	KEY `fk_gallery_has_comments_users2_idx` (`object_users_id`),
	CONSTRAINT `fk_gallery_has_comments_comments1` FOREIGN KEY (`comments_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_gallery_has_comments_gallery1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_gallery_has_comments_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	CONSTRAINT `fk_gallery_has_comments_users2` FOREIGN KEY (`object_users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for gallery_has_pictures
-- ----------------------------
DROP TABLE
IF EXISTS `gallery_has_pictures`;

CREATE TABLE `gallery_has_pictures` (
	`gallery_id` BIGINT (20) UNSIGNED NOT NULL,
	`pictures_id` BIGINT (20) UNSIGNED NOT NULL,
	PRIMARY KEY (`gallery_id`, `pictures_id`),
	KEY `fk_gallery_has_pictures_pictures1_idx` (`pictures_id`),
	KEY `fk_gallery_has_pictures_gallery1_idx` (`gallery_id`),
	CONSTRAINT `fk_gallery_has_pictures_gallery1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_gallery_has_pictures_pictures1` FOREIGN KEY (`pictures_id`) REFERENCES `pictures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for gallery_has_tags
-- ----------------------------
DROP TABLE
IF EXISTS `gallery_has_tags`;

CREATE TABLE `gallery_has_tags` (
	`gallery_id` BIGINT (20) UNSIGNED NOT NULL,
	`tags_name` VARCHAR (100) NOT NULL,
	PRIMARY KEY (`gallery_id`, `tags_name`),
	KEY `fk_gallery_has_tags_tags1_idx` (`tags_name`),
	KEY `fk_gallery_has_tags_gallery1_idx` (`gallery_id`),
	CONSTRAINT `fk_gallery_has_tags_gallery1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_gallery_has_tags_tags1` FOREIGN KEY (`tags_name`) REFERENCES `tags` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for gallery_meta
-- ----------------------------
DROP TABLE
IF EXISTS `gallery_meta`;

CREATE TABLE `gallery_meta` (
	`meta_id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`gallery_id` BIGINT (20) UNSIGNED NOT NULL,
	`meta_key` VARCHAR (128) NOT NULL,
	`meta_value` LONGTEXT NOT NULL,
	PRIMARY KEY (`meta_id`),
	KEY `fk_gallery_meta_gallery1_idx` (`gallery_id`),
	CONSTRAINT `fk_gallery_meta_gallery1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for gallery_outside_pictures
-- ----------------------------
DROP TABLE
IF EXISTS `gallery_outside_pictures`;

CREATE TABLE `gallery_outside_pictures` (
	`gallery_id` BIGINT (20) UNSIGNED NOT NULL,
	`pictures_id` BIGINT (20) UNSIGNED NOT NULL,
	`outside_add_time` datetime NOT NULL,
	`outside_status` INT (11) NOT NULL DEFAULT '0',
	`outside_description` VARCHAR (512) DEFAULT NULL,
	PRIMARY KEY (`gallery_id`, `pictures_id`),
	KEY `fk_gallery_has_pictures1_pictures1_idx` (`pictures_id`),
	KEY `fk_gallery_has_pictures1_gallery1_idx` (`gallery_id`),
	CONSTRAINT `fk_gallery_has_pictures1_gallery1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_gallery_has_pictures1_pictures1` FOREIGN KEY (`pictures_id`) REFERENCES `pictures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for gallery_views
-- ----------------------------
DROP TABLE
IF EXISTS `gallery_views`;

CREATE TABLE `gallery_views` (
	`gallery_id` BIGINT (20) UNSIGNED NOT NULL,
	`views_date` date NOT NULL,
	`views_count` INT (10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`gallery_id`, `views_date`),
	CONSTRAINT `fk_views_gallery1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for message
-- ----------------------------
DROP TABLE
IF EXISTS `message`;

CREATE TABLE `message` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`msg_title` VARCHAR (200) DEFAULT NULL,
	`msg_datetime` datetime NOT NULL,
	`msg_content` LONGTEXT NOT NULL,
	`from_users_id` BIGINT (20) UNSIGNED DEFAULT NULL,
	`to_users_id` BIGINT (20) UNSIGNED NOT NULL,
	`is_read` TINYINT (1) NOT NULL DEFAULT '0',
	`from_del` TINYINT (1) NOT NULL DEFAULT '0',
	`to_del` TINYINT (1) NOT NULL DEFAULT '0',
	`read_time` datetime DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `fk_message_users1_idx` (`from_users_id`),
	KEY `fk_message_users2_idx` (`to_users_id`),
	CONSTRAINT `fk_message_users1` FOREIGN KEY (`from_users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_message_users2` FOREIGN KEY (`to_users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for options
-- ----------------------------
DROP TABLE
IF EXISTS `options`;

CREATE TABLE `options` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`option_name` VARCHAR (64) NOT NULL,
	`option_value` LONGTEXT NOT NULL,
	`option_autoload` INT (11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE KEY `option_name_UNIQUE` (`option_name`)
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for pictures
-- ----------------------------
DROP TABLE
IF EXISTS `pictures`;

CREATE TABLE `pictures` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '单张图片唯一ID',
	`users_id` BIGINT (20) UNSIGNED NOT NULL COMMENT '图片对应用户ID',
	`server_name` VARCHAR (45) NOT NULL,
	`pic_name` VARCHAR (512) NOT NULL,
	`pic_path` VARCHAR (100) NOT NULL COMMENT '原图路径',
	`pic_create_time` datetime NOT NULL,
	`pic_width` INT (10) UNSIGNED NOT NULL,
	`pic_height` INT (10) UNSIGNED NOT NULL COMMENT '原图宽度',
	`pic_description` VARCHAR (1000) NOT NULL COMMENT '图片的单张描述',
	`pic_thumbnails_path` VARCHAR (500) NOT NULL,
	`pic_thumbnails_width` INT (10) UNSIGNED NOT NULL,
	`pic_thumbnails_height` INT (10) UNSIGNED NOT NULL,
	`pic_hd_path` VARCHAR (500) NOT NULL,
	`pic_hd_width` INT (10) UNSIGNED NOT NULL,
	`pic_hd_height` INT (10) UNSIGNED NOT NULL,
	`pic_status` INT (11) NOT NULL DEFAULT '0',
	`pic_comment_count` INT (10) UNSIGNED NOT NULL DEFAULT '0',
	`pic_display_path` VARCHAR (500) NOT NULL,
	`pic_display_width` INT (10) UNSIGNED NOT NULL,
	`pic_display_height` INT (10) UNSIGNED NOT NULL,
	`pic_like_count` INT (10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `fk_pictures_users_idx` (`users_id`),
	KEY `fk_pictures_server1_idx` (`server_name`),
	CONSTRAINT `fk_pictures_server1` FOREIGN KEY (`server_name`) REFERENCES `server` (`name`) ON DELETE NO ACTION ON UPDATE CASCADE,
	CONSTRAINT `fk_pictures_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for pictures_has_comments
-- ----------------------------
DROP TABLE
IF EXISTS `pictures_has_comments`;

CREATE TABLE `pictures_has_comments` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`pictures_id` BIGINT (20) UNSIGNED NOT NULL,
	`comments_id` BIGINT (20) UNSIGNED NOT NULL,
	`object_users_id` BIGINT (20) UNSIGNED NOT NULL,
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	KEY `fk_pictures_has_comments_comments1_idx` (`comments_id`),
	KEY `fk_pictures_has_comments_pictures1_idx` (`pictures_id`),
	KEY `fk_pictures_has_comments_users1_idx` (`users_id`),
	KEY `fk_pictures_has_comments_users2_idx` (`object_users_id`),
	CONSTRAINT `fk_pictures_has_comments_comments1` FOREIGN KEY (`comments_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_pictures_has_comments_pictures1` FOREIGN KEY (`pictures_id`) REFERENCES `pictures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_pictures_has_comments_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	CONSTRAINT `fk_pictures_has_comments_users2` FOREIGN KEY (`object_users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for pictures_has_tags
-- ----------------------------
DROP TABLE
IF EXISTS `pictures_has_tags`;

CREATE TABLE `pictures_has_tags` (
	`pictures_id` BIGINT (20) UNSIGNED NOT NULL,
	`tags_name` VARCHAR (100) NOT NULL,
	PRIMARY KEY (`pictures_id`, `tags_name`),
	KEY `fk_pictures_has_tags_tags1_idx` (`tags_name`),
	KEY `fk_pictures_has_tags_pictures1_idx` (`pictures_id`),
	CONSTRAINT `fk_pictures_has_tags_pictures` FOREIGN KEY (`pictures_id`) REFERENCES `pictures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_pictures_has_tags_tags` FOREIGN KEY (`tags_name`) REFERENCES `tags` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for pictures_views
-- ----------------------------
DROP TABLE
IF EXISTS `pictures_views`;

CREATE TABLE `pictures_views` (
	`pictures_id` BIGINT (20) UNSIGNED NOT NULL,
	`views_date` date NOT NULL,
	`views_count` INT (10) UNSIGNED DEFAULT NULL,
	PRIMARY KEY (`views_date`, `pictures_id`),
	KEY `fk_picture_views_pictures1_idx` (`pictures_id`),
	CONSTRAINT `fk_picture_views_pictures1` FOREIGN KEY (`pictures_id`) REFERENCES `pictures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for posts
-- ----------------------------
DROP TABLE
IF EXISTS `posts`;

CREATE TABLE `posts` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	`post_title` VARCHAR (150) NOT NULL,
	`post_name` VARCHAR (200) NOT NULL,
	`post_content` LONGTEXT NOT NULL,
	`post_time` datetime NOT NULL,
	`post_update_time` datetime NOT NULL,
	`post_description` VARCHAR (1024) DEFAULT NULL,
	`post_keyword` VARCHAR (512) DEFAULT NULL,
	`post_category` VARCHAR (128) NOT NULL,
	`post_status` INT (11) NOT NULL DEFAULT '0',
	`post_comment_count` INT (10) UNSIGNED NOT NULL DEFAULT '0',
	`post_allow_comment` INT (11) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`),
	UNIQUE KEY `post_name_UNIQUE` (`post_name`),
	KEY `fk_posts_users1_idx` (`users_id`),
	CONSTRAINT `fk_posts_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for posts_has_comments
-- ----------------------------
DROP TABLE
IF EXISTS `posts_has_comments`;

CREATE TABLE `posts_has_comments` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`posts_id` BIGINT (20) UNSIGNED NOT NULL,
	`comments_id` BIGINT (20) UNSIGNED NOT NULL,
	`object_users_id` BIGINT (20) UNSIGNED NOT NULL,
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	KEY `fk_posts_has_comments_comments1_idx` (`comments_id`),
	KEY `fk_posts_has_comments_posts1_idx` (`posts_id`),
	KEY `fk_posts_has_comments_users1_idx` (`users_id`),
	KEY `fk_posts_has_comments_users2_idx` (`object_users_id`),
	CONSTRAINT `fk_posts_has_comments_comments1` FOREIGN KEY (`comments_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_posts_has_comments_posts1` FOREIGN KEY (`posts_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_posts_has_comments_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	CONSTRAINT `fk_posts_has_comments_users2` FOREIGN KEY (`object_users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for posts_views
-- ----------------------------
DROP TABLE
IF EXISTS `posts_views`;

CREATE TABLE `posts_views` (
	`posts_id` BIGINT (20) UNSIGNED NOT NULL,
	`views_date` date NOT NULL,
	`views_count` INT (10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`posts_id`, `views_date`),
	KEY `fk_posts_views_posts1_idx` (`posts_id`),
	CONSTRAINT `fk_posts_views_posts1` FOREIGN KEY (`posts_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for queue
-- ----------------------------
DROP TABLE
IF EXISTS `queue`;

CREATE TABLE `queue` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '队列唯一ID',
	`time` datetime NOT NULL COMMENT '队列创建时间',
	`up_time` datetime NOT NULL COMMENT '队列更新时间',
	`status` INT (11) NOT NULL DEFAULT '0' COMMENT '该队列的状态',
	`callback` LONGTEXT NOT NULL COMMENT '队列的回调函数',
	`param` LONGTEXT NOT NULL COMMENT '回调函数接受的参数',
	`library` LONGTEXT NOT NULL COMMENT '回调函数需要的类库',
	`message` VARCHAR (1024) DEFAULT NULL COMMENT '队列执行信息',
	`type` VARCHAR (45) DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for server
-- ----------------------------
DROP TABLE
IF EXISTS `server`;

CREATE TABLE `server` (
	`name` VARCHAR (45) NOT NULL,
	`url` VARCHAR (100) NOT NULL,
	`meta` LONGTEXT,
	PRIMARY KEY (`name`),
	UNIQUE KEY `server_url_UNIQUE` (`url`)
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for tags
-- ----------------------------
DROP TABLE
IF EXISTS `tags`;

CREATE TABLE `tags` (
	`name` VARCHAR (100) NOT NULL,
	`count` INT (10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`name`)
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE
IF EXISTS `users`;

CREATE TABLE `users` (
	`id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '唯一用户ID',
	`user_name` VARCHAR (64) NOT NULL COMMENT '用户唯一名称',
	`user_aliases` VARCHAR (64) DEFAULT NULL COMMENT '用户别名，用作显示',
	`user_email` VARCHAR (100) NOT NULL COMMENT '用户唯一邮箱地址',
	`user_url` VARCHAR (100) DEFAULT NULL COMMENT '用户个人主页',
	`user_password` VARCHAR (128) NOT NULL COMMENT '用户加密密码',
	`user_salt` VARCHAR (64) NOT NULL COMMENT '用户加密字符',
	`user_status` INT (11) NOT NULL DEFAULT '0' COMMENT '用户状态，0表示默认状态，1表示激活',
	`user_registered_time` datetime NOT NULL COMMENT '用户注册时间',
	`user_registered_ip` VARBINARY (128) NOT NULL COMMENT '注册IP',
	`user_last_login_time` datetime DEFAULT NULL COMMENT '上次登录时间',
	`user_last_login_ip` VARBINARY (128) DEFAULT NULL COMMENT '上次登录IP',
	`user_error_login_count` INT (11) DEFAULT NULL COMMENT '错误登录次数',
	`user_error_login_ip` VARBINARY (128) DEFAULT NULL COMMENT '错误登录IP',
	`user_error_login_time` datetime DEFAULT NULL COMMENT '错误登录时间',
	`user_cookie_salt` VARCHAR (64) NOT NULL COMMENT '用户COOKIE加密字符',
	`user_cookie_login` VARCHAR (128) DEFAULT NULL,
	`user_avatar` VARCHAR (100) NOT NULL DEFAULT '{default}' COMMENT '用户头像地址',
	PRIMARY KEY (`id`),
	UNIQUE KEY `name_UNIQUE` (`user_name`),
	UNIQUE KEY `email_UNIQUE` (`user_email`)
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for users_follow_gallery
-- ----------------------------
DROP TABLE
IF EXISTS `users_follow_gallery`;

CREATE TABLE `users_follow_gallery` (
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	`gallery_id` BIGINT (20) UNSIGNED NOT NULL,
	`follow_time` datetime NOT NULL,
	`follow_update` INT (11) NOT NULL DEFAULT '0',
	`follow_update_time` datetime DEFAULT NULL,
	PRIMARY KEY (`users_id`, `gallery_id`),
	KEY `fk_users_has_gallery_gallery2_idx` (`gallery_id`),
	KEY `fk_users_has_gallery_users2_idx` (`users_id`),
	CONSTRAINT `fk_users_has_gallery_gallery2` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_users_has_gallery_users2` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for users_follow_users
-- ----------------------------
DROP TABLE
IF EXISTS `users_follow_users`;

CREATE TABLE `users_follow_users` (
	`users_id` BIGINT (20) UNSIGNED NOT NULL COMMENT '用户的ID',
	`follow_users_id` BIGINT (20) UNSIGNED NOT NULL COMMENT '被关注用户ID',
	`follow_time` datetime NOT NULL COMMENT '关注时间',
	`follow_update` INT (11) NOT NULL DEFAULT '0' COMMENT '用户更新次数',
	`follow_update_time` datetime DEFAULT NULL COMMENT '更新时间',
	PRIMARY KEY (
		`users_id`,
		`follow_users_id`
	),
	KEY `fk_users_has_users_users2_idx` (`follow_users_id`),
	KEY `fk_users_has_users_users1_idx` (`users_id`),
	CONSTRAINT `fk_users_has_users_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_users_has_users_users2` FOREIGN KEY (`follow_users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for users_like_comments
-- ----------------------------
DROP TABLE
IF EXISTS `users_like_comments`;

CREATE TABLE `users_like_comments` (
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	`comments_id` BIGINT (20) UNSIGNED NOT NULL,
	`like_time` datetime DEFAULT NULL,
	PRIMARY KEY (`users_id`, `comments_id`),
	KEY `fk_users_has_comments_comments1_idx` (`comments_id`),
	KEY `fk_users_has_comments_users1_idx` (`users_id`),
	CONSTRAINT `fk_users_has_comments_comments1` FOREIGN KEY (`comments_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_users_has_comments_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for users_like_gallery
-- ----------------------------
DROP TABLE
IF EXISTS `users_like_gallery`;

CREATE TABLE `users_like_gallery` (
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	`gallery_id` BIGINT (20) UNSIGNED NOT NULL,
	`like_time` datetime NOT NULL,
	PRIMARY KEY (`users_id`, `gallery_id`),
	KEY `fk_users_has_gallery_gallery1_idx` (`gallery_id`),
	KEY `fk_users_has_gallery_users1_idx` (`users_id`),
	CONSTRAINT `fk_users_has_gallery_gallery1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_users_has_gallery_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for users_like_pictures
-- ----------------------------
DROP TABLE
IF EXISTS `users_like_pictures`;

CREATE TABLE `users_like_pictures` (
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	`pictures_id` BIGINT (20) UNSIGNED NOT NULL,
	`like_time` datetime NOT NULL,
	PRIMARY KEY (`users_id`, `pictures_id`),
	KEY `fk_users_has_pictures_pictures1_idx` (`pictures_id`),
	KEY `fk_users_has_pictures_users1_idx` (`users_id`),
	CONSTRAINT `fk_users_has_pictures_pictures1` FOREIGN KEY (`pictures_id`) REFERENCES `pictures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_users_has_pictures_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for user_count
-- ----------------------------
DROP TABLE
IF EXISTS `user_count`;

CREATE TABLE `user_count` (
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	`picture_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	`gallery_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	`comment_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	`user_follow_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	`user_fans_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	`like_gallery_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	`like_picture_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	`like_comment_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	`follow_gallery_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	`unread_message_count` BIGINT (20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`users_id`),
	KEY `fk_user_count_users1_idx` (`users_id`),
	CONSTRAINT `fk_user_count_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for user_meta
-- ----------------------------
DROP TABLE
IF EXISTS `user_meta`;

CREATE TABLE `user_meta` (
	`meta_id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`users_id` BIGINT (20) UNSIGNED NOT NULL,
	`meta_key` VARCHAR (128) NOT NULL,
	`meta_value` LONGTEXT NOT NULL,
	PRIMARY KEY (`meta_id`),
	KEY `fk_user_meta_users1_idx` (`users_id`),
	CONSTRAINT `fk_user_meta_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

