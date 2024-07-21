DROP TABLE IF EXISTS `form_protection_token`;

CREATE TABLE `form_protection_token` (
  `form_protection_token_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `form_protection_token_user_session_id` INT UNSIGNED NULL,
  `form_protection_token_hash` VARCHAR(255) NOT NULL,
  `form_protection_token_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `form_protection_token_ip` VARCHAR(46) NOT NULL,
  `form_protection_token_form_name` VARCHAR(50),

  PRIMARY KEY (`form_protection_token_id`),

  CONSTRAINT `form_protection_token_user_session_user_fk`
    FOREIGN KEY (`form_protection_token_user_session_id`)
    REFERENCES `user_session` (`user_session_id`)
    ON DELETE CASCADE
) ENGINE = InnoDB;
