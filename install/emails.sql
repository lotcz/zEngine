DROP TABLE IF EXISTS `email`;

CREATE TABLE IF NOT EXISTS `email` (
  `email_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_to` VARCHAR(255) NOT NULL,
  `email_from` VARCHAR(255) NOT NULL,
  `email_subject` VARCHAR(255) NOT NULL,
  `email_content_type` VARCHAR(255) not NULL,
  `email_body` TEXT,
  `email_send_date` TIMESTAMP not null DEFAULT CURRENT_TIMESTAMP,
  `email_sent` tinyint null default 0,
  PRIMARY KEY (`email_id`)
) ENGINE = InnoDB;
