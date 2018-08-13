DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `admin_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_user_id` INT UNSIGNED NOT NULL,

  PRIMARY KEY (`admin_id`),
  UNIQUE INDEX `admin_user_unique_index` (`admin_user_id`),
  CONSTRAINT `admin_user_fk`
    FOREIGN KEY (`admin_user_id`)
    REFERENCES `user` (`user_id`)
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewAdministrators`;

CREATE VIEW viewAdministrators AS
	SELECT *
  FROM admin a
  JOIN user u ON (a.admin_user_id = u.user_id);

DROP VIEW IF EXISTS `viewSessionsStats` ;

CREATE VIEW viewSessionsStats AS
	SELECT 'Anonymous' as n, COUNT(*) as c
    FROM user_session
    WHERE user_state = 0

    UNION

    SELECT 'Visitors' as n, COUNT(*) as c
    FROM user_session
    LEFT OUTER JOIN admin a ON (a.admin_user_id = us.user_session_user_id)
    WHERE a.admin_id IS NULL AND user_state > 0

    UNION

    SELECT 'Administrators' as n, COUNT(*) as c
    FROM user_session us
    JOIN admin a ON (a.admin_user_id = us.user_session_user_id);
