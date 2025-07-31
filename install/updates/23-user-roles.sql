ALTER TABLE email
	MODIFY COLUMN `email_sent` BIT DEFAULT 0 NULL;

ALTER TABLE admin_role RENAME user_role;
ALTER TABLE user_role RENAME COLUMN admin_role_id TO user_role_id;
ALTER TABLE user_role RENAME COLUMN admin_role_name TO user_role_name;
ALTER TABLE `user` RENAME COLUMN user_admin_role_id TO user_user_role_id;
ALTER TABLE user_role ADD COLUMN user_role_is_external BOOL NOT NULL DEFAULT false;

insert into user_role values (3, 'External', true);

UPDATE `user` set user_user_role_id = 3 where user_user_role_id is null;

DROP VIEW IF EXISTS `view_administrators`;

DROP VIEW IF EXISTS `view_users`;

CREATE VIEW view_users AS
	SELECT *
	FROM user u
	JOIN user_role r ON (u.user_user_role_id = r.user_role_id)
	WHERE r.user_role_is_external = false;

DROP VIEW IF EXISTS `view_external_users`;

CREATE VIEW view_external_users AS
	SELECT *
	FROM user u
	JOIN user_role r ON (u.user_user_role_id = r.user_role_id)
	WHERE r.user_role_is_external = true;

DROP VIEW IF EXISTS `view_session_stats`;

CREATE VIEW view_session_stats AS
	SELECT count(*) as c, r.user_role_name as n
	FROM user_session us
	JOIN `user` u ON (u.user_id = us.user_session_user_id)
	JOIN user_role r ON (u.user_user_role_id = r.user_role_id)
	GROUP BY r.user_role_id;
