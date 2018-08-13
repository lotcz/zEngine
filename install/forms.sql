DROP TABLE IF EXISTS `form_xsrf_token`;

CREATE TABLE `form_xsrf_token` (
  `form_xsrf_token_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `form_xsrf_token_user_session_id` INT UNSIGNED NULL,
  `form_xsrf_token_hash` VARCHAR(255) NOT NULL,
  `form_xsrf_token_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `form_xsrf_token_expires` DATETIME NOT NULL,
  `form_xsrf_token_ip` VARCHAR(15) NOT NULL,
  `form_xsrf_token_form_name` VARCHAR(50),

  PRIMARY KEY (`form_xsrf_token_id`),

  CONSTRAINT `form_xsrf_token_user_session_user_fk`
    FOREIGN KEY (`form_xsrf_token_user_session_id`)
    REFERENCES `user_session` (`user_session_id`)
    ON DELETE CASCADE
) ENGINE = InnoDB;
