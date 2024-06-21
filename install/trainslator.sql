DROP TABLE IF EXISTS `trainslator_cache`;

CREATE TABLE `trainslator_cache` (
  `trainslator_cache_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `trainslator_cache_language_id` TINYINT UNSIGNED NOT NULL,
  `trainslator_cache_key_hash` CHAR(32) NOT NULL,
  `trainslator_cache_key` TEXT,
  `trainslator_cache_value` TEXT,
  `trainslator_cache_create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`trainslator_cache_id`),
  UNIQUE INDEX `trainslator_cache_key_hash_index` (`trainslator_cache_language_id`, `trainslator_cache_key_hash`),
  CONSTRAINT `trainslator_cache_language_id_fk`
	  FOREIGN KEY (`trainslator_cache_language_id`)
		  REFERENCES `language` (`language_id`)
		  ON DELETE cascade
) ENGINE = InnoDB;
