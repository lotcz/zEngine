ALTER TABLE user_session
MODIFY COLUMN user_session_ip varchar(46);

ALTER TABLE ip_failed_attempt
MODIFY COLUMN ip_failed_attempt_ip varchar(46);

ALTER TABLE banned_ip
MODIFY COLUMN banned_ip_ip varchar(46);

DROP VIEW IF EXISTS `view_session_stats`;

CREATE VIEW view_session_stats AS
	SELECT count(*) as c, r.admin_role_name as n
	FROM user_session us
	JOIN `user` u ON (u.user_id = us.user_session_user_id)
	LEFT OUTER JOIN admin_role r ON (u.user_admin_role_id = r.admin_role_id)
	GROUP BY r.admin_role_id;
