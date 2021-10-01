DROP TABLE IF EXISTS `admin_role`;

CREATE TABLE `admin_role` (
 `admin_role_id` INT UNSIGNED NOT NULL,
 `admin_role_name` NVARCHAR(100) NOT NULL,

 PRIMARY KEY (`admin_role_id`),
 UNIQUE INDEX `admin_user_unique_index` (`admin_role_name`)
) ENGINE = InnoDB;

insert into admin_role values (1, 'Superuser');
insert into admin_role values (2, 'Admin');

ALTER TABLE `user` ADD `user_admin_role_id` INT UNSIGNED NULL;
ALTER TABLE `user` add CONSTRAINT `user_admin_role_fk`
    FOREIGN KEY (`user_admin_role_id`)
    REFERENCES `admin_role` (`admin_role_id`);

DROP VIEW IF EXISTS `viewAdministrators`;

CREATE VIEW viewAdministrators AS
	SELECT *, u.user_id as admin_id
  	FROM user u
  	JOIN admin_role r ON (u.user_admin_role_id = r.admin_role_id);

DROP VIEW IF EXISTS `viewSessionsStats` ;

CREATE VIEW viewSessionsStats AS
	SELECT 'Anonymous' as n, COUNT(*) as c
    FROM user_session us
    JOIN user u ON (u.user_id = us.user_session_user_id)
    WHERE u.user_state = 0

    UNION

    SELECT 'Administrators' as n, COUNT(*) as c
    FROM user_session us
    JOIN `user` u ON (u.user_id = us.user_session_user_id)
    JOIN admin_role r ON (u.user_admin_role_id = r.admin_role_id);
