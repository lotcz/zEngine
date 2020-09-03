DROP TABLE IF EXISTS `ip_failed_attempt`;

CREATE TABLE `ip_failed_attempt` (
  `ip_failed_attempt_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_failed_attempt_ip` VARCHAR(15),
  `ip_failed_attempt_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `ip_failed_attempt_first` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_failed_attempt_last` TIMESTAMP  NULL ,
  PRIMARY KEY (`ip_failed_attempt_id`),
   UNIQUE INDEX `ip_failed_attempt_ip_unique` (`ip_failed_attempt_ip` ASC)
) ENGINE = InnoDB;

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

DROP TABLE IF EXISTS `session`;

CREATE TABLE `user_session` (
  `user_session_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_session_token_hash` VARCHAR(255) NOT NULL,
  `user_session_user_id` INT UNSIGNED NOT NULL,
  `user_session_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_session_expires` DATETIME NULL,
  `user_session_ip` VARCHAR(15),
  PRIMARY KEY (`user_session_id`),
  CONSTRAINT `user_session_user_fk`
    FOREIGN KEY (`user_session_user_id`)
    REFERENCES `user` (`user_id`)
    ON DELETE CASCADE
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewSessionsStats` ;

CREATE VIEW viewSessionsStats AS
	SELECT 'Anonymous' as n, COUNT(*) as c
    FROM user_session us
    JOIN user u ON (u.user_id = us.user_session_user_id)
    WHERE u.user_state = 0

    UNION

    SELECT 'Logged in' as n, COUNT(*) as c
    FROM user_session us
    JOIN user u ON (u.user_id = us.user_session_user_id)
    WHERE u.user_state > 0;
