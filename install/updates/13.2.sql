ALTER TABLE user_session
MODIFY COLUMN user_session_ip varchar(46);

ALTER TABLE ip_failed_attempt
MODIFY COLUMN ip_failed_attempt_ip varchar(46);

ALTER TABLE banned_ip
MODIFY COLUMN banned_ip_ip varchar(46);

DROP VIEW IF EXISTS `viewSessionStats`;

CREATE VIEW view_session_stats AS
	SELECT count(*) as c, r.admin_role_name as n
	FROM user_session us
	JOIN `user` u ON (u.user_id = us.user_session_user_id)
	LEFT OUTER JOIN admin_role r ON (u.user_admin_role_id = r.admin_role_id)
	GROUP BY r.admin_role_id;

DROP VIEW IF EXISTS `viewUsers`;

CREATE VIEW view_users AS
	SELECT *, u.user_id as admin_id
	FROM user u;

DROP VIEW IF EXISTS `viewImages`;

CREATE VIEW view_images AS
	SELECT *
	FROM image i
	LEFT OUTER JOIN gallery g ON (i.image_gallery_id = g.gallery_id);

DROP VIEW IF EXISTS `viewNewsletterSubscriptionsStats`;

CREATE VIEW view_newsletter_subscriptions_stats AS
select newsletter_subscription_active, COUNT(*) as cnt from newsletter_subscription group by newsletter_subscription_active;

DROP VIEW IF EXISTS `viewAdministrators`;

CREATE VIEW view_administrators AS
	SELECT *
  	FROM user u
  	JOIN admin_role r ON (u.user_admin_role_id = r.admin_role_id);

CREATE TABLE `trainslator_cache` (
  `trainslator_cache_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `trainslator_cache_ready` BOOL NOT NULL,
  `trainslator_cache_language_id` TINYINT UNSIGNED NOT NULL,
  `trainslator_cache_key_hash` CHAR(32) NOT NULL,
  `trainslator_cache_key` TEXT,
  `trainslator_cache_value` TEXT,
  `trainslator_cache_create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`trainslator_cache_id`),
  INDEX (`trainslator_cache_ready`, `trainslator_cache_id` desc),
  UNIQUE INDEX `trainslator_cache_key_hash_index` (`trainslator_cache_language_id`, `trainslator_cache_key_hash`),
  CONSTRAINT `trainslator_cache_language_id_fk`
	  FOREIGN KEY (`trainslator_cache_language_id`)
		  REFERENCES `language` (`language_id`)
		  ON DELETE cascade
) ENGINE = InnoDB;

CREATE VIEW view_trainslator_cache AS
	SELECT *
	FROM trainslator_cache c
	JOIN `language` l ON (l.language_id = c.trainslator_cache_language_id);

