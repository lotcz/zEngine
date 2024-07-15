DROP TABLE IF EXISTS `trainslator_cache`;

CREATE TABLE `trainslator_cache` (
  `trainslator_cache_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `trainslator_cache_language_id` TINYINT UNSIGNED NOT NULL,
  `trainslator_cache_key_hash` CHAR(32) NOT NULL,
  `trainslator_cache_key` TEXT,
  `trainslator_cache_value` TEXT,
  `trainslator_cache_create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`trainslator_cache_id`),
  INDEX `trainslator_cache_ready_index` (`trainslator_cache_ready` desc, `trainslator_cache_id` asc),
  UNIQUE INDEX `trainslator_cache_key_hash_index` (`trainslator_cache_language_id`, `trainslator_cache_key_hash`),
  CONSTRAINT `trainslator_cache_language_id_fk`
	  FOREIGN KEY (`trainslator_cache_language_id`)
		  REFERENCES `language` (`language_id`)
		  ON DELETE cascade
) ENGINE = InnoDB;


DROP VIEW IF EXISTS `view_trainslator_cache`;

CREATE VIEW view_trainslator_cache AS
	SELECT *
	FROM trainslator_cache c
	JOIN `language` l ON (l.language_id = c.trainslator_cache_language_id);
