DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_state` TINYINT UNSIGNED NOT NULL default 0,
  `user_email` VARCHAR(50),
  `user_login` VARCHAR(50),
  `user_name` VARCHAR(100),
  `user_phone` VARCHAR(100),
  `user_password_hash` VARCHAR(255) NULL,
  `user_failed_attempts` INT NOT NULL DEFAULT 0,
  `user_last_access` TIMESTAMP,
  `user_reset_password_hash` VARCHAR(255) NULL,
  `user_reset_password_expires` DATETIME NULL,
  `user_language_id` TINYINT UNSIGNED NOT NULL,
  `user_user_role_id` INT UNSIGNED NOT NULL,

  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `users_email_unique` (`user_email`),
  UNIQUE INDEX `users_login_unique` (`user_login`),
  CONSTRAINT `user_language_fk`
    FOREIGN KEY (`user_language_id`)
    REFERENCES `language` (`language_id`),
  CONSTRAINT `user_user_role_fk`
    FOREIGN KEY (`user_user_role_id`)
    REFERENCES `user_role` (`user_role_id`)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `session`;

CREATE TABLE `user_session` (
  `user_session_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_session_token_hash` VARCHAR(255) NOT NULL,
  `user_session_user_id` INT UNSIGNED NOT NULL,
  `user_session_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_session_expires` DATETIME NULL,
  `user_session_ip` VARCHAR(46),
  PRIMARY KEY (`user_session_id`),
  CONSTRAINT `user_session_user_fk`
    FOREIGN KEY (`user_session_user_id`)
    REFERENCES `user` (`user_id`)
    ON DELETE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `user_role`;

CREATE TABLE `user_role` (
 `user_role_id` INT UNSIGNED NOT NULL,
 `user_role_name` NVARCHAR(100) NOT NULL,
 `user_role_is_external` BOOL NOT NULL DEFAULT false,

 PRIMARY KEY (`user_role_id`),
 UNIQUE INDEX `user_role_name_unique_index` (`user_role_name`)
) ENGINE = InnoDB;

insert into user_role values (1, 'Superuser', false);
insert into user_role values (2, 'Admin', false);
insert into user_role values (3, 'External', true);

DROP VIEW IF EXISTS `view_users`;

CREATE VIEW view_users AS
	SELECT *
	FROM user u
	JOIN user_role r ON (u.user_user_role_id = r.user_role_id)
	WHERE r.user_role_is_external = false;

DROP VIEW IF EXISTS `view_external_users`;

CREATE VIEW view_external_users AS
	SELECT *
	FROM user u
	JOIN user_role r ON (u.user_user_role_id = r.user_role_id)
	WHERE r.user_role_is_external = true;

DROP VIEW IF EXISTS `view_session_stats`;

CREATE VIEW view_session_stats AS
	SELECT count(*) as c, r.user_role_name as n
	FROM user_session us
	JOIN `user` u ON (u.user_id = us.user_session_user_id)
	JOIN user_role r ON (u.user_user_role_id = r.user_role_id)
	GROUP BY r.user_role_id;
