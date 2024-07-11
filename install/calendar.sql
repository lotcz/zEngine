DROP TABLE IF EXISTS `calendar_reservation`;

CREATE TABLE `calendar_reservation` (
  `calendar_reservation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `calendar_reservation_start` DATETIME NOT NULL,
  `calendar_reservation_duration` INT not null default 15,
  `calendar_reservation_user_id` INT UNSIGNED NOT NULL,
  `calendar_reservation_cosmetic_service_id` INT UNSIGNED NOT NULL,
  `calendar_reservation_whole_day` BOOLEAN NOT NULL DEFAULT false,
 
  PRIMARY KEY (`calendar_reservation_id`),
  CONSTRAINT `calendar_reservation_user_fk`
	 FOREIGN KEY (`calendar_reservation_user_id`)
		REFERENCES `user` (`user_id`)
		ON DELETE cascade,
  	UNIQUE INDEX `calendar_reservation_start_unique` (`calendar_reservation_start` ASC),
  CONSTRAINT `calendar_reservation_cosmetic_service_fk`
	 FOREIGN KEY (`calendar_reservation_cosmetic_service_id`)
		REFERENCES `cosmetic_service` (`cosmetic_service_id`)
		ON DELETE cascade
) ENGINE=InnoDB;

DROP VIEW IF EXISTS `view_calendar_reservations`;

CREATE VIEW view_calendar_reservations AS
	SELECT cr.*, u.user_email as `email`
	FROM `calendar_reservation` cr
	LEFT OUTER JOIN `user` u ON (u.user_id = cr.calendar_reservation_user_id);

