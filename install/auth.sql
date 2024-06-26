DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_state` TINYINT UNSIGNED NOT NULL default 0,
  `user_email` VARCHAR(50),
  `user_login` VARCHAR(50),
  `user_name` VARCHAR(100),
  `user_password_hash` VARCHAR(255) NULL,
  `user_failed_attempts` INT NOT NULL DEFAULT 0,
  `user_last_access` TIMESTAMP,
  `user_reset_password_hash` VARCHAR(255) NULL,
  `user_reset_password_expires` DATETIME NULL,
  `user_language_id` TINYINT UNSIGNED NOT NULL,

  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `users_email_unique` (`user_email`),
  UNIQUE INDEX `users_login_unique` (`user_login`),
  CONSTRAINT `user_language_fk`
    FOREIGN KEY (`user_language_id`)
    REFERENCES `language` (`language_id`)
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewUsers`;

CREATE VIEW viewUsers AS
	SELECT *, u.user_id as admin_id
	FROM user u;

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
