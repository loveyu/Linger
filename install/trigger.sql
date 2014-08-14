-- --------------------------
-- 删除原有触发器
-- --------------------------

DROP TRIGGER IF EXISTS `comments_AINS`;
DROP TRIGGER IF EXISTS `comments_ADEL`;
DROP TRIGGER IF EXISTS `gallery_AINS`;
DROP TRIGGER IF EXISTS `gallery_ADEL`;
DROP TRIGGER IF EXISTS `gallery_has_comments_AINS`;
DROP TRIGGER IF EXISTS `gallery_has_comments_ADEL`;
DROP TRIGGER IF EXISTS `gallery_has_tags_BINS`;
DROP TRIGGER IF EXISTS `gallery_has_tags_AINS`;
DROP TRIGGER IF EXISTS `gallery_has_tags_ADEL`;
DROP TRIGGER IF EXISTS `message_AINS`;
DROP TRIGGER IF EXISTS `message_AUPD`;
DROP TRIGGER IF EXISTS `message_ADEL`;
DROP TRIGGER IF EXISTS `pictures_AINS`;
DROP TRIGGER IF EXISTS `pictures_ADEL`;
DROP TRIGGER IF EXISTS `pictures_has_comments_AINS`;

-- ----------------------------------
-- 设置分割符
-- ----------------------------------

-- DELIMITER ;;

-- ----------------------------
-- TRIGGER comments_AINS
-- ----------------------------

CREATE TRIGGER `comments_AINS` AFTER INSERT ON `comments` FOR EACH ROW
BEGIN
	UPDATE `user_count`
SET `comment_count` = `comment_count` + 1
WHERE
	`users_id` = new.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER comments_ADEL
-- ----------------------------

CREATE TRIGGER `comments_ADEL` AFTER DELETE ON `comments` FOR EACH ROW
BEGIN
	UPDATE `user_count`
SET `comment_count` = CASE
WHEN `comment_count` > 0 THEN
	`comment_count`
ELSE
	1
END - 1
WHERE
	`users_id` = old.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER gallery_AINS
-- ----------------------------

CREATE TRIGGER `gallery_AINS` AFTER INSERT ON `gallery` FOR EACH ROW
BEGIN
	UPDATE `user_count`
SET `gallery_count` = `gallery_count` + 1
WHERE
	`users_id` = new.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER gallery_ADEL
-- ----------------------------

CREATE TRIGGER `gallery_ADEL` AFTER DELETE ON `gallery` FOR EACH ROW
BEGIN
	UPDATE `user_count`
SET `gallery_count` = CASE
WHEN `gallery_count` > 0 THEN
	`gallery_count`
ELSE
	1
END - 1
WHERE
	`users_id` = old.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER gallery_has_comments_AINS
-- ----------------------------

CREATE TRIGGER `gallery_has_comments_AINS` AFTER INSERT ON `gallery_has_comments` FOR EACH ROW
BEGIN
	UPDATE `gallery`
SET `gallery_comment_count` = `gallery_comment_count` + 1
WHERE
	`id` = new.`gallery_id` ;
END;;

-- ----------------------------
-- TRIGGER gallery_has_comments_ADEL
-- ----------------------------

CREATE TRIGGER `gallery_has_comments_ADEL` AFTER DELETE ON `gallery_has_comments` FOR EACH ROW
BEGIN
	UPDATE `gallery`
SET `gallery_comment_count` = CASE
WHEN `gallery_comment_count` > 0 THEN
	`gallery_comment_count`
ELSE
	1
END - 1
WHERE
	`id` = old.`gallery_id` ;
END;;

-- ----------------------------
-- TRIGGER gallery_has_tags_BINS
-- ----------------------------

CREATE TRIGGER `gallery_has_tags_BINS` BEFORE INSERT ON `gallery_has_tags` FOR EACH ROW
BEGIN
	DECLARE
		t_count INT DEFAULT 0 ; SELECT
			count(*) INTO t_count
		FROM
			DUAL
		WHERE
			EXISTS (
				SELECT
					1
				FROM
					`tags`
				WHERE
					`name` = new.`tags_name`
			) ;
		IF t_count = 0 THEN
			-- 不存在标签插入
			INSERT INTO `tags` (`name`, `count`)
		VALUE
			(new.`tags_name`, 0) ;
		END
		IF ;
		END;;

-- ----------------------------
-- TRIGGER gallery_has_tags_AINS
-- ----------------------------

CREATE TRIGGER `gallery_has_tags_AINS` AFTER INSERT ON `gallery_has_tags` FOR EACH ROW
BEGIN
	UPDATE `tags`
SET `count` = `count` + 1
WHERE
	`name` = new.`tags_name` ;
END;;

-- ----------------------------
-- TRIGGER gallery_has_tags_ADEL
-- ----------------------------

CREATE TRIGGER `gallery_has_tags_ADEL` AFTER DELETE ON `gallery_has_tags` FOR EACH ROW
BEGIN
	DECLARE
		t_count INT DEFAULT 0 ; SELECT
			sum(`count`) INTO t_count
		FROM
			`tags`
		WHERE
			`name` = old.`tags_name`
		LIMIT 0,
		1 ;
	IF t_count = 1 THEN
		DELETE
	FROM
		`tags`
	WHERE
		`name` = old.`tags_name` ;
	ELSE
		UPDATE `tags`
	SET `count` = `count` - 1
	WHERE
		`name` = old.`tags_name` ;
	END
	IF ;
	END;;

-- ----------------------------
-- TRIGGER message_AINS
-- ----------------------------

CREATE TRIGGER `message_AINS` AFTER INSERT ON `message` FOR EACH ROW
BEGIN

IF new.`is_read` = 0 THEN
	UPDATE `user_count`
SET `unread_message_count` = `unread_message_count` + 1
WHERE
	`users_id` = new.`to_users_id` ;
END
IF ;
END;;

-- ----------------------------
-- TRIGGER message_AUPD
-- ----------------------------

CREATE TRIGGER `message_AUPD` AFTER UPDATE ON `message` FOR EACH ROW
BEGIN

IF old.`is_read` = 0
AND new.`is_read` = 1 THEN
	UPDATE `user_count`
SET `unread_message_count` = `unread_message_count` - 1
WHERE
	`users_id` = new.`to_users_id` ;
END
IF ;
IF old.`is_read` = 1
AND new.`is_read` = 0 THEN
	UPDATE `user_count`
SET `unread_message_count` = `unread_message_count` + 1
WHERE
	`users_id` = new.`to_users_id` ;
END
IF ;
END;;

-- ----------------------------
-- TRIGGER message_ADEL
-- ----------------------------

CREATE TRIGGER `message_ADEL` AFTER DELETE ON `message` FOR EACH ROW
BEGIN

IF old.`is_read` = 0 THEN
	UPDATE `user_count`
SET `unread_message_count` = `unread_message_count` - 1
WHERE
	`users_id` = old.`to_users_id` ;
END
IF ;
END;;

-- ----------------------------
-- TRIGGER pictures_AINS
-- ----------------------------

CREATE TRIGGER `pictures_AINS` AFTER INSERT ON `pictures` FOR EACH ROW
BEGIN
	UPDATE `user_count`
SET `picture_count` = `picture_count` + 1
WHERE
	`users_id` = new.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER pictures_ADEL
-- ----------------------------

CREATE TRIGGER `pictures_ADEL` AFTER DELETE ON `pictures` FOR EACH ROW
BEGIN
	UPDATE `user_count`
SET `picture_count` = CASE
WHEN `picture_count` > 0 THEN
	`picture_count`
ELSE
	1
END - 1
WHERE
	`users_id` = old.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER pictures_has_comments_AINS
-- ----------------------------

CREATE TRIGGER `pictures_has_comments_AINS` AFTER INSERT ON `pictures_has_comments` FOR EACH ROW
BEGIN
	UPDATE `pictures`
SET `pic_comment_count` = `pic_comment_count` + 1
WHERE
	`id` = new.`pictures_id` ;
END;;

-- ----------------------------
-- TRIGGER pictures_has_comments_BDEL
-- ----------------------------

CREATE TRIGGER `pictures_has_comments_BDEL` AFTER DELETE ON `pictures_has_comments` FOR EACH ROW
BEGIN
	UPDATE `pictures`
SET `pic_comment_count` = CASE
WHEN `pic_comment_count` > 0 THEN
	`pic_comment_count`
ELSE
	1
END - 1
WHERE
	`id` = old.`pictures_id` ;
END;;

-- ----------------------------
-- TRIGGER pictures_has_tags_BINS
-- ----------------------------

CREATE TRIGGER `pictures_has_tags_BINS` BEFORE INSERT ON `pictures_has_tags` FOR EACH ROW
BEGIN
	DECLARE
		t_count INT DEFAULT 0 ; SELECT
			count(*) INTO t_count
		FROM
			DUAL
		WHERE
			EXISTS (
				SELECT
					1
				FROM
					`tags`
				WHERE
					`name` = new.`tags_name`
			) ;
		IF t_count = 0 THEN
			-- 不存在标签插入
			INSERT INTO `tags` (`name`, `count`)
		VALUE
			(new.`tags_name`, 0) ;
		END
		IF ;
		END;;

-- ----------------------------
-- TRIGGER pictures_has_tags_AINS
-- ----------------------------

CREATE TRIGGER `pictures_has_tags_AINS` AFTER INSERT ON `pictures_has_tags` FOR EACH ROW
BEGIN
	UPDATE `tags`
SET `count` = `count` + 1
WHERE
	`name` = new.`tags_name` ;
END;;

-- ----------------------------
-- TRIGGER pictures_has_tags_ADEL
-- ----------------------------

CREATE TRIGGER `pictures_has_tags_ADEL` AFTER DELETE ON `pictures_has_tags` FOR EACH ROW
BEGIN
	DECLARE
		t_count INT DEFAULT 0 ; SELECT
			sum(`count`) INTO t_count
		FROM
			`tags`
		WHERE
			`name` = old.`tags_name`
		LIMIT 0,
		1 ;
	IF t_count = 1 THEN
		DELETE
	FROM
		`tags`
	WHERE
		`name` = old.`tags_name` ;
	ELSE
		UPDATE `tags`
	SET `count` = `count` - 1
	WHERE
		`name` = old.`tags_name` ;
	END
	IF ;
	END;;

-- ----------------------------
-- TRIGGER posts_has_comments_AINS
-- ----------------------------

CREATE TRIGGER `posts_has_comments_AINS` AFTER INSERT ON `posts_has_comments` FOR EACH ROW
BEGIN
	UPDATE `posts`
SET `post_comment_count` = `post_comment_count` + 1
WHERE
	`id` = new.`posts_id` ;
END;;

-- ----------------------------
-- TRIGGER posts_has_comments_ADEL
-- ----------------------------

CREATE TRIGGER `posts_has_comments_ADEL` AFTER DELETE ON `posts_has_comments` FOR EACH ROW
BEGIN
	UPDATE `posts`
SET `post_comment_count` = CASE
WHEN `post_comment_count` > 0 THEN
	`posts_comment_count`
ELSE
	1
END - 1
WHERE
	`id` = old.`posts_id` ;
END;;

-- ----------------------------
-- TRIGGER users_AINS
-- ----------------------------

CREATE TRIGGER `users_AINS` AFTER INSERT ON `users` FOR EACH ROW
BEGIN
	INSERT INTO user_count (`users_id`)
VALUES
	(new.`id`) ;
END;;

-- ----------------------------
-- TRIGGER users_follow_gallery_AINS
-- ----------------------------

CREATE TRIGGER `users_follow_gallery_AINS` AFTER INSERT ON `users_follow_gallery` FOR EACH ROW
BEGIN
	UPDATE `gallery`
SET `gallery_follow_count` = `gallery_follow_count` + 1
WHERE
	`id` = new.`gallery_id` ; UPDATE `user_count`
SET `follow_gallery_count` = `follow_gallery_count` + 1
WHERE
	`users_id` = new.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER users_follow_gallery_ADEL
-- ----------------------------

CREATE TRIGGER `users_follow_gallery_ADEL` AFTER DELETE ON `users_follow_gallery` FOR EACH ROW
BEGIN
	UPDATE `gallery`
SET `gallery_follow_count` = CASE
WHEN `gallery_follow_count` > 0 THEN
	`gallery_follow_count`
ELSE
	1
END - 1
WHERE
	`id` = old.`gallery_id` ; UPDATE `user_count`
SET `follow_gallery_count` = CASE
WHEN `follow_gallery_count` > 0 THEN
	`follow_gallery_count`
ELSE
	1
END - 1
WHERE
	`users_id` = old.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER users_follow_users_AINS
-- ----------------------------

CREATE TRIGGER `users_follow_users_AINS` AFTER INSERT ON `users_follow_users` FOR EACH ROW
BEGIN
	UPDATE `user_count`
SET `user_follow_count` = `user_follow_count` + 1
WHERE
	`users_id` = new.`users_id` ; UPDATE `user_count`
SET `user_fans_count` = `user_fans_count` + 1
WHERE
	`users_id` = new.`follow_users_id` ;
END;;

-- ----------------------------
-- TRIGGER users_follow_users_ADEL
-- ----------------------------

CREATE TRIGGER `users_follow_users_ADEL` AFTER DELETE ON `users_follow_users` FOR EACH ROW
BEGIN
	UPDATE `user_count`
SET `user_follow_count` = CASE
WHEN `user_follow_count` > 0 THEN
	`user_follow_count`
ELSE
	1
END - 1
WHERE
	`users_id` = old.`users_id` ; UPDATE `user_count`
SET `user_fans_count` = CASE
WHEN `user_fans_count` > 0 THEN
	`user_fans_count`
ELSE
	1
END - 1
WHERE
	`users_id` = old.`follow_users_id` ;
END;;

-- ----------------------------
-- TRIGGER users_like_comments_AINS
-- ----------------------------

CREATE TRIGGER `users_like_comments_AINS` AFTER INSERT ON `users_like_comments` FOR EACH ROW
BEGIN
	UPDATE `comments`
SET `comment_like_count` = `comment_like_count` + 1
WHERE
	`id` = new.`comments_id` ; UPDATE `user_count`
SET `like_comment_count` = `like_comment_count` + 1
WHERE
	`users_id` = new.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER users_like_comments_ADEL
-- ----------------------------

CREATE TRIGGER `users_like_comments_ADEL` AFTER DELETE ON `users_like_comments` FOR EACH ROW
BEGIN
	UPDATE `comments`
SET `comment_like_count` = CASE
WHEN `comment_like_count` > 0 THEN
	`comment_like_count`
ELSE
	1
END - 1
WHERE
	`id` = old.`comments_id` ; UPDATE `user_count`
SET `like_comment_count` = CASE
WHEN `like_comment_count` > 0 THEN
	`like_picture_count`
ELSE
	1
END - 1
WHERE
	`users_id` = old.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER users_like_gallery_AINS
-- ----------------------------

CREATE TRIGGER `users_like_gallery_AINS` AFTER INSERT ON `users_like_gallery` FOR EACH ROW
BEGIN
	UPDATE `gallery`
SET `gallery_like_count` = `gallery_like_count` + 1
WHERE
	`id` = new.`gallery_id` ; UPDATE `user_count`
SET `like_gallery_count` = `like_gallery_count` + 1
WHERE
	`users_id` = new.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER users_like_gallery_ADEL
-- ----------------------------

CREATE TRIGGER `users_like_gallery_ADEL` AFTER DELETE ON `users_like_gallery` FOR EACH ROW
BEGIN
	UPDATE `gallery`
SET `gallery_like_count` = CASE
WHEN `gallery_like_count` > 0 THEN
	`gallery_like_count`
ELSE
	1
END - 1
WHERE
	`id` = old.`gallery_id` ; UPDATE `user_count`
SET `like_gallery_count` = CASE
WHEN `like_gallery_count` > 0 THEN
	`like_gallery_count`
ELSE
	1
END - 1
WHERE
	`users_id` = old.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER users_like_pictures_AINS
-- ----------------------------

CREATE TRIGGER `users_like_pictures_AINS` AFTER INSERT ON `users_like_pictures` FOR EACH ROW
BEGIN
	UPDATE `pictures`
SET `pic_like_count` = `pic_like_count` + 1
WHERE
	`id` = new.`pictures_id` ; UPDATE `user_count`
SET `like_picture_count` = `like_picture_count` + 1
WHERE
	`users_id` = new.`users_id` ;
END;;

-- ----------------------------
-- TRIGGER users_like_pictures_AUPD
-- ----------------------------

CREATE TRIGGER `users_like_pictures_AUPD` AFTER DELETE ON `users_like_pictures` FOR EACH ROW
BEGIN
	UPDATE `pictures`
SET `pic_like_count` = CASE
WHEN `pic_like_count` > 0 THEN
	`pic_like_count`
ELSE
	1
END - 1
WHERE
	`id` = old.`pictures_id` ; UPDATE `user_count`
SET `like_picture_count` = CASE
WHEN `like_picture_count` > 0 THEN
	`like_picture_count`
ELSE
	1
END - 1
WHERE
	`users_id` = old.`users_id` ;
END;;

-- --------------------------
-- 分割结束
-- --------------------------

-- DELIMITER ;