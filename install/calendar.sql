DROP TABLE IF EXISTS `calendar_reservation`;

CREATE TABLE `calendar_reservation` (
  `calendar_reservation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `calendar_reservation_start` DATETIME NOT NULL,
  `calendar_reservation_duration` INT not null default 15,
  `calendar_reservation_user_id` INT UNSIGNED NOT NULL,
  `calendar_reservation_cosmetic_service_id` INT UNSIGNED NOT NULL,
  `calendar_reservation_whole_day` BOOLEAN NOT NULL DEFAULT false,
  `calendar_reservation_note` text,
  `calendar_reservation_creation_notification_sent` bool DEFAULT false,
  `calendar_reservation_incoming_notification_sent` bool DEFAULT false,

  PRIMARY KEY (`calendar_reservation_id`),
  CONSTRAINT `calendar_reservation_user_fk`
	 FOREIGN KEY (`calendar_reservation_user_id`)
		REFERENCES `user` (`user_id`)
		ON DELETE cascade,
  CONSTRAINT `calendar_reservation_cosmetic_service_fk`
	 FOREIGN KEY (`calendar_reservation_cosmetic_service_id`)
		REFERENCES `cosmetic_service` (`cosmetic_service_id`)
		ON DELETE cascade
) ENGINE=InnoDB;

ALTER TABLE calendar_reservation
	ADD COLUMN `calendar_reservation_end` DATETIME GENERATED ALWAYS AS (
		CASE
			 WHEN calendar_reservation_whole_day = 1 THEN DATE_ADD(calendar_reservation_start, INTERVAL calendar_reservation_duration DAY)
			 ELSE DATE_ADD(calendar_reservation_start, INTERVAL calendar_reservation_duration MINUTE)
		END
	) STORED;

CREATE UNIQUE INDEX idx_calendar_reservation_end
	ON calendar_reservation (calendar_reservation_start, calendar_reservation_end);

CREATE INDEX idx_calendar_reservation_creation_notification_sent
	ON calendar_reservation (calendar_reservation_creation_notification_sent);

CREATE INDEX idx_calendar_reservation_incoming_notification_sent
	ON calendar_reservation (calendar_reservation_incoming_notification_sent);

DROP VIEW IF EXISTS `view_calendar_reservations`;

CREATE VIEW view_calendar_reservations AS
	SELECT cr.*,
		u.user_email as `email`,
	  	u.user_phone as `phone`,
	  	u.user_name as `name`,
	   	cs.cosmetic_service_name as `service`
	FROM `calendar_reservation` cr
	LEFT OUTER JOIN `user` u ON (u.user_id = cr.calendar_reservation_user_id)
	LEFT OUTER JOIN `cosmetic_service` cs ON (cs.cosmetic_service_id = cr.calendar_reservation_cosmetic_service_id);
