DROP TABLE IF EXISTS `ip_failed_attempt`;

CREATE TABLE `ip_failed_attempt` (
  `ip_failed_attempt_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_failed_attempt_ip` VARCHAR(15),
  `ip_failed_attempt_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `ip_failed_attempt_first` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_failed_attempt_last` TIMESTAMP  NULL ,
  PRIMARY KEY (`ip_failed_attempt_id`),
   UNIQUE INDEX `uc_ip_failed_attempt_ip` (`ip_failed_attempt_ip` ASC)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `banned_ip`;

CREATE TABLE `banned_ip` (
  `banned_ip_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `banned_ip_ip` VARCHAR(15),
  `banned_ip_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`banned_ip_id`),
   UNIQUE INDEX `uc_banned_ip_ip` (`banned_ip_ip` ASC)
) ENGINE = InnoDB;