DROP TABLE IF EXISTS `alias`;

CREATE TABLE `alias` (
  `alias_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alias_url` VARCHAR(255) NOT NULL,
  `alias_path` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`alias_id`),
  UNIQUE INDEX `alias_url_unique` (`alias_url` ASC)
) ENGINE = InnoDB;
