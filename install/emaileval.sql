CREATE TABLE email_evaluation_cache (
	email_evaluation_cache_id INT UNSIGNED NOT NULL,
	email_evaluation_cache_text TEXT,
	email_evaluation_cache_hash CHAR(32) NOT NULL,
	email_evaluation_cache_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	email_evaluation_cache_state ENUM('active', 'unsubscribed', 'invalid') DEFAULT 'active',
	PRIMARY KEY (`email_evaluation_cache_id`),
	INDEX (`email_evaluation_cache_hash`)
);