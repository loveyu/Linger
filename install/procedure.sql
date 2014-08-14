-- --------------------------
-- 删除原有存储过程
-- --------------------------

DROP PROCEDURE IF EXISTS `comment_count_gallery`;
DROP PROCEDURE IF EXISTS `comment_count_pictures`;
DROP PROCEDURE IF EXISTS `comment_count_posts`;
DROP PROCEDURE IF EXISTS `count_comment`;
DROP PROCEDURE IF EXISTS `count_follow_gallery`;
DROP PROCEDURE IF EXISTS `count_gallery`;
DROP PROCEDURE IF EXISTS `count_like_comment`;
DROP PROCEDURE IF EXISTS `count_like_gallery`;
DROP PROCEDURE IF EXISTS `count_like_picture`;
DROP PROCEDURE IF EXISTS `count_picture`;
DROP PROCEDURE IF EXISTS `count_unread_message`;
DROP PROCEDURE IF EXISTS `count_user_fans`;
DROP PROCEDURE IF EXISTS `gallery_count_follow`;
DROP PROCEDURE IF EXISTS `getGalleryPreviousNextID`;

-- ----------------------------------
-- 设置分割符
-- ----------------------------------

-- DELIMITER ;;

-- ----------------------------
-- Procedure structure for comment_count_gallery
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`comment_count_gallery` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				table_id BIGINT UNSIGNED DEFAULT 0 ; DECLARE
					rs2 CURSOR FOR SELECT
						count(`gallery_id`),
						`gallery_id`
					FROM
						`gallery_has_comments`
					GROUP BY
						`gallery_id` ; DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; UPDATE `gallery`
						SET `gallery_comment_count` = 0
						WHERE
							`id` > 0 ; OPEN rs2 ; FETCH NEXT
						FROM
							rs2 INTO count,
							table_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `gallery`
						SET `gallery_comment_count` = count
						WHERE
							`id` = table_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs2 INTO count,
							table_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs2 ;
						END;;

-- ----------------------------
-- Procedure structure for comment_count_pictures
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`comment_count_pictures` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				table_id BIGINT UNSIGNED DEFAULT 0 ; DECLARE
					rs1 CURSOR FOR SELECT
						count(`pictures_id`),
						`pictures_id`
					FROM
						`pictures_has_comments`
					GROUP BY
						`pictures_id` ; DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; UPDATE `pictures`
						SET `pic_comment_count` = 0
						WHERE
							`id` > 0 ; OPEN rs1 ; FETCH NEXT
						FROM
							rs1 INTO count,
							table_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `pictures`
						SET `pic_comment_count` = count
						WHERE
							`id` = table_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs1 INTO count,
							table_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs1 ;
						END;;

-- ----------------------------
-- Procedure structure for comment_count_posts
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`comment_count_posts` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				table_id BIGINT UNSIGNED DEFAULT 0 ; DECLARE
					rs3 CURSOR FOR SELECT
						count(`posts_id`),
						`posts_id`
					FROM
						`posts_has_comments`
					GROUP BY
						`posts_id` ; DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; UPDATE `posts`
						SET `post_comment_count` = 0
						WHERE
							`id` > 0 ; OPEN rs3 ; FETCH NEXT
						FROM
							rs3 INTO count,
							table_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `posts`
						SET `post_comment_count` = count
						WHERE
							`id` = table_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs3 INTO count,
							table_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs3 ;
						END;;

-- ----------------------------
-- Procedure structure for count_comment
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`count_comment` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				user_id BIGINT UNSIGNED DEFAULT 0 ; -- 统计图集数量
				DECLARE
					rs CURSOR FOR SELECT
						count(`id`),
						`users_id`
					FROM
						`comments`
					GROUP BY
						`users_id` ; -- 异常处理
						DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; OPEN rs ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `user_count`
						SET `comment_count` = count
						WHERE
							`users_id` = user_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs ;
						END;;

-- ----------------------------
-- Procedure structure for count_follow_gallery
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`count_follow_gallery` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				user_id BIGINT UNSIGNED DEFAULT 0 ; -- 统计关注图集数量
				DECLARE
					rs CURSOR FOR SELECT
						count(`users_id`),
						`users_id`
					FROM
						`users_follow_gallery`
					GROUP BY
						`users_id` ; -- 异常处理
						DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; OPEN rs ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `user_count`
						SET `follow_gallery_count` = count
						WHERE
							`users_id` = user_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs ;
						END;;

-- ----------------------------
-- Procedure structure for count_gallery
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`count_gallery` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				user_id BIGINT UNSIGNED DEFAULT 0 ; -- 统计图集数量
				DECLARE
					rs CURSOR FOR SELECT
						count(`id`),
						`users_id`
					FROM
						`gallery`
					GROUP BY
						`users_id` ; -- 异常处理
						DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; OPEN rs ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `user_count`
						SET `gallery_count` = count
						WHERE
							`users_id` = user_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs ;
						END;;

-- ----------------------------
-- Procedure structure for count_like_comment
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`count_like_comment` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				user_id BIGINT UNSIGNED DEFAULT 0 ; -- 统计喜欢图片数量
				DECLARE
					rs CURSOR FOR SELECT
						count(`users_id`),
						`users_id`
					FROM
						`users_like_comments`
					GROUP BY
						`users_id` ; -- 异常处理
						DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; OPEN rs ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `user_count`
						SET `like_comment_count` = count
						WHERE
							`users_id` = user_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs ;
						END;;

-- ----------------------------
-- Procedure structure for count_like_gallery
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`count_like_gallery` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				user_id BIGINT UNSIGNED DEFAULT 0 ; -- 统计喜欢图集数量
				DECLARE
					rs CURSOR FOR SELECT
						count(`users_id`),
						`users_id`
					FROM
						`users_like_gallery`
					GROUP BY
						`users_id` ; -- 异常处理
						DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; OPEN rs ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `user_count`
						SET `like_gallery_count` = count
						WHERE
							`users_id` = user_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs ;
						END;;

-- ----------------------------
-- Procedure structure for count_like_picture
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`count_like_picture` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				user_id BIGINT UNSIGNED DEFAULT 0 ; -- 统计喜欢图片数量
				DECLARE
					rs CURSOR FOR SELECT
						count(`users_id`),
						`users_id`
					FROM
						`users_like_pictures`
					GROUP BY
						`users_id` ; -- 异常处理
						DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; OPEN rs ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `user_count`
						SET `like_picture_count` = count
						WHERE
							`users_id` = user_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs ;
						END;;

-- ----------------------------
-- Procedure structure for count_picture
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`count_picture` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				user_id BIGINT UNSIGNED DEFAULT 0 ; -- 统计关注数量
				DECLARE
					rs CURSOR FOR SELECT
						count(`id`),
						`users_id`
					FROM
						pictures
					GROUP BY
						`users_id` ; -- 异常处理
						DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; OPEN rs ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `user_count`
						SET `picture_count` = count
						WHERE
							`users_id` = user_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs ;
						END;;

-- ----------------------------
-- Procedure structure for count_unread_message
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`count_unread_message` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				user_id BIGINT UNSIGNED DEFAULT 0 ; DECLARE
					rs CURSOR FOR SELECT
						count(`to_users_id`),
						`to_users_id`
					FROM
						`message`
					WHERE
						`is_read` = 0
					AND `to_del` = 0
					GROUP BY
						`to_users_id` ; -- 异常处理
						DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; OPEN rs ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `user_count`
						SET `unread_message_count` = count
						WHERE
							`users_id` = user_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs ;
						END;;

-- ----------------------------
-- Procedure structure for count_user_fans
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`count_user_fans` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				user_id BIGINT UNSIGNED DEFAULT 0 ; -- 统计粉丝数量
				DECLARE
					rs CURSOR FOR SELECT
						count(`users_id`),
						`follow_users_id`
					FROM
						`users_follow_users`
					GROUP BY
						`follow_users_id` ; -- 异常处理
						DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; OPEN rs ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `user_count`
						SET `user_fans_count` = count
						WHERE
							`users_id` = user_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs ;
						END;;

-- ----------------------------
-- Procedure structure for count_user_follow
-- ----------------------------

USE `{database_name}`;;

CREATE PROCEDURE `{database_name}`.`count_user_follow` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				user_id BIGINT UNSIGNED DEFAULT 0 ; -- 统计粉丝数量
				DECLARE
					rs CURSOR FOR SELECT
						count(`follow_users_id`),
						`users_id`
					FROM
						`users_follow_users`
					GROUP BY
						`users_id` ; -- 异常处理
						DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; OPEN rs ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ;
						REPEAT

						IF NOT Done THEN
							UPDATE `user_count`
						SET `user_follow_count` = count
						WHERE
							`users_id` = user_id ;
						END
						IF ; FETCH NEXT
						FROM
							rs INTO count,
							user_id ; UNTIL Done
						END
						REPEAT
							; CLOSE rs ;
						END;;

-- ----------------------------
-- Procedure structure for gallery_count_follow
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`gallery_count_follow` ()
BEGIN
	DECLARE
		Done INT DEFAULT 0 ; DECLARE
			count BIGINT UNSIGNED DEFAULT 0 ; DECLARE
				gid BIGINT UNSIGNED DEFAULT 0 ; -- 统计关注图集数量
				DECLARE
					rs CURSOR FOR SELECT
						count(*),
						`gallery_id`
					FROM
						`users_follow_gallery`
					GROUP BY
						`gallery_id` ; -- 异常处理
						DECLARE
							CONTINUE HANDLER FOR SQLSTATE '02000'
						SET Done = 1 ; OPEN rs ; FETCH NEXT
						FROM
							rs INTO count,
							gid ;
						REPEAT

						IF NOT Done THEN
							UPDATE `gallery`
						SET `gallery_follow_count` = count
						WHERE
							`id` = gid ;
						END
						IF ; FETCH NEXT
						FROM
							rs INTO count,
							gid ; UNTIL Done
						END
						REPEAT
							; CLOSE rs ;
						END;;

-- ----------------------------
-- Procedure structure for getGalleryPreviousNextID
-- ----------------------------

CREATE PROCEDURE `{database_name}`.`getGalleryPreviousNextID` (IN gallery_id BIGINT)
BEGIN
	DECLARE
		previous_id BIGINT DEFAULT 0 ; DECLARE
			next_id BIGINT DEFAULT 0 ; SELECT
				max(id) INTO previous_id
			FROM
				`gallery`
			WHERE
				`id` < gallery_id ; SELECT
					min(id) INTO next_id
				FROM
					`gallery`
				WHERE
					`id` > gallery_id ; SELECT
						previous_id AS `previous`,
						next_id AS `next` ;
					END;;

-- --------------------------
-- 分割结束
-- --------------------------

-- DELIMITER ;