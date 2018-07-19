DROP TABLE IF EXISTS `form_xsrf_tokens`;
DROP TABLE IF EXISTS `user_sessions`;
DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `customer_sessions`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `translations`;
DROP TABLE IF EXISTS `languages`;
DROP TABLE IF EXISTS `static_pages`;

DROP TABLE IF EXISTS `site_globals`;

CREATE TABLE `site_globals` (
  `site_global_name` varchar(100) NOT NULL,
  `site_global_label` varchar(100),
  `site_global_type` varchar(100) NOT NULL DEFAULT 'text',
  `site_global_validations_json` text,
  `site_global_value` text,
  
  PRIMARY KEY (`site_global_name`)
) ENGINE=InnoDB;

INSERT INTO site_globals (`site_global_name`,`site_global_value`) VALUES ('site_title','zShop');

DROP TABLE IF EXISTS `currencies`;

CREATE TABLE `currencies` (
  `currency_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency_name` NVARCHAR(50) NOT NULL,
  `currency_format` NVARCHAR(20) NOT NULL,
  `currency_value` DECIMAL(20,10) NOT NULL,
  `currency_decimals` TINYINT NOT NULL DEFAULT 2,
  PRIMARY KEY (`currency_id`),
  UNIQUE INDEX `currency_name_unique` (`currency_name` ASC))
ENGINE = InnoDB;

INSERT INTO currencies (currency_name, currency_format, currency_value, currency_decimals) VALUES ('Kč','%s&nbsp;Kč', 1, 0);
INSERT INTO currencies (currency_name, currency_format, currency_value, currency_decimals) VALUES ('EUR','%s&nbsp;EUR', 27.02, 2);

DROP TABLE IF EXISTS `ip_failed_attempts`;

CREATE TABLE `ip_failed_attempts` (
  `ip_failed_attempt_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_failed_attempt_ip` VARCHAR(15),
  `ip_failed_attempt_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `ip_failed_attempt_first` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_failed_attempt_last` TIMESTAMP  NULL ,
  PRIMARY KEY (`ip_failed_attempt_id`),
   UNIQUE INDEX `ip_failed_attempt_ip_unique` (`ip_failed_attempt_ip` ASC)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `languages`;

CREATE TABLE `languages` (
  `language_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `language_name` VARCHAR(100) NOT NULL,
  `language_code` VARCHAR(5) NOT NULL,
  `language_date_format` VARCHAR(50) NOT NULL,
  `language_datetime_format` VARCHAR(100) NOT NULL,
  `language_decimal_separator` VARCHAR(10) NOT NULL,
  `language_thousands_separator` VARCHAR(10) NOT NULL,
  `language_default_currency_id` TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY (`language_id`),
  CONSTRAINT `language_currency_fk`
    FOREIGN KEY (`language_default_currency_id`)
    REFERENCES `currencies` (`currency_id`)
)ENGINE = InnoDB;

INSERT INTO languages VALUES (NULL, 'English','en','j/n/Y','j/n/Y h:i:s', '.', ',',2);
INSERT INTO languages VALUES (NULL, 'Čeština','cs','j.n.Y','j.n.Y h:i:s', ',', '&nbsp',1);

DROP TABLE IF EXISTS `aliases`;

CREATE TABLE `aliases` (
  `alias_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alias_url` VARCHAR(200) NOT NULL,
  `alias_path` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`alias_id`),
  UNIQUE INDEX `aliases_url_unique` (`alias_url` ASC))
ENGINE = InnoDB;

INSERT INTO aliases (alias_url, alias_path) VALUES ('kosik','cart');

DROP TABLE IF EXISTS `translations`;

CREATE TABLE `translations` (
  `translation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `translation_language_id` TINYINT UNSIGNED NOT NULL,
  `translation_name` VARCHAR(255) NOT NULL,
  `translation_translation` TEXT,
   PRIMARY KEY (`translation_id`),
  UNIQUE INDEX (`translation_language_id`, `translation_name`),
  CONSTRAINT `translation_language_fk`
    FOREIGN KEY (`translation_language_id`)
    REFERENCES `languages` (`language_id`)
)ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewTranslations`;

CREATE VIEW viewTranslations AS
	SELECT *
    FROM translations t
    LEFT OUTER JOIN languages l ON (t.translation_language_id = l.language_id);
  
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_deleted` BIT DEFAULT 0 NOT NULL,
  `user_is_superuser` BIT DEFAULT 0 NOT NULL,
  `user_login` VARCHAR(50),
  `user_email` VARCHAR(50) NOT NULL,
  `user_password_hash` VARCHAR(255) NULL,
  `user_failed_attempts` INT NOT NULL DEFAULT 0,
  `user_last_access` TIMESTAMP,
  `user_reset_password_hash` VARCHAR(255) NULL,
  `user_reset_password_expires` DATETIME NULL,
  `user_language_id` TINYINT UNSIGNED NOT NULL,
 
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `users_email_unique` (`user_email` ASC),
  UNIQUE INDEX `users_login_unique` (`user_login` ASC),
  CONSTRAINT `user_language_fk`
    FOREIGN KEY (`user_language_id`)
    REFERENCES `languages` (`language_id`)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `user_sessions`;

CREATE TABLE `user_sessions` (
  `user_session_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_session_token_hash` VARCHAR(255) NOT NULL,
  `user_session_user_id` INT(10) UNSIGNED NOT NULL,
  `user_session_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_session_expires` DATETIME NULL,
  `user_session_ip` VARCHAR(15),
  PRIMARY KEY (`user_session_id`),
  CONSTRAINT `user_session_user_fk`
    FOREIGN KEY (`user_session_user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_name` NVARCHAR(50) NOT NULL,
  `role_description` NVARCHAR(255),
  PRIMARY KEY (`role_id`),
  UNIQUE INDEX `role_name_unique` (`role_name` ASC)
) ENGINE = InnoDB;

INSERT INTO roles (role_name) VALUES (N'Users admin');
INSERT INTO roles (role_name) VALUES (N'Products admin');
INSERT INTO roles (role_name) VALUES (N'Orders admin');

DROP TABLE IF EXISTS `user_roles`;

CREATE TABLE `user_roles` (
  `user_role_user_id` INT UNSIGNED NOT NULL,
  `user_role_role_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`user_role_user_id`, `user_role_role_id`),
  INDEX `user_roles_user_index` (`user_role_user_id`),
  CONSTRAINT `user_roles_user_fk`
    FOREIGN KEY (`user_role_user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE CASCADE,
  CONSTRAINT `user_roles_role_fk`
    FOREIGN KEY (`user_role_role_id`)
    REFERENCES `roles` (`role_id`)
    ON DELETE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `permissions`;

CREATE TABLE `permissions` (
  `permission_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_name` VARCHAR(50) NOT NULL,  
  `permission_description` VARCHAR(255),  
  PRIMARY KEY (`permission_id`),
  UNIQUE INDEX `permissions_name_unique` (`permission_name`)
) ENGINE = InnoDB;

INSERT INTO permissions (permission_name, permission_description) VALUES ('edit user', 'Edit users');

DROP TABLE IF EXISTS `role_permissions`;

CREATE TABLE `role_permissions` (
  `role_permission_role_id` INT UNSIGNED NOT NULL,
  `role_permission_permission_id` INT UNSIGNED NOT NULL,  
  PRIMARY KEY (`role_permission_role_id`,  `role_permission_permission_id`)
) ENGINE = InnoDB;

INSERT INTO permissions (permission_name, permission_description) VALUES ('browse order', 'Browse orders');
INSERT INTO permissions (permission_name, permission_description) VALUES ('edit order', 'Edit orders');
INSERT INTO permissions (permission_name, permission_description) VALUES ('browse product', 'Browse products');
INSERT INTO permissions (permission_name, permission_description) VALUES ('edit product', 'Edit products');

DROP TABLE IF EXISTS `customers`;

CREATE TABLE `customers` (
  `customer_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_state` TINYINT UNSIGNED NOT null default 0,  
  `customer_name` NVARCHAR(50),
  `customer_email` VARCHAR(100),
  `customer_password_hash` VARCHAR(255) ,
  `customer_failed_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `customer_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customer_last_access` DATETIME,
  `customer_reset_password_hash` VARCHAR(255) NULL,
  `customer_reset_password_expires` DATETIME NULL,
  
  `customer_language_id` TINYINT UNSIGNED NOT NULL,  
  `customer_currency_id` TINYINT UNSIGNED NOT NULL,
    
  PRIMARY KEY (`customer_id`),
  INDEX `customers_email_index` (`customer_email` ASC),  
  CONSTRAINT `customer_currency_fk`
    FOREIGN KEY (`customer_currency_id`)
    REFERENCES `currencies` (`currency_id`),
 CONSTRAINT `customer_language_fk`
    FOREIGN KEY (`customer_language_id`)
    REFERENCES `languages` (`language_id`)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `customer_sessions`;

CREATE TABLE `customer_sessions` (
  `customer_session_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_session_token_hash` VARCHAR(255) NOT NULL,
  `customer_session_customer_id` INT(10) UNSIGNED NOT NULL,
  `customer_session_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customer_session_expires` DATETIME NULL,
  `customer_session_ip` VARCHAR(15) NOT NULL,
  PRIMARY KEY (`customer_session_id`),
  CONSTRAINT `customer_session_customer_fk`
    FOREIGN KEY (`customer_session_customer_id`)
    REFERENCES `customers` (`customer_id`)
    ON DELETE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `form_xsrf_tokens`;

CREATE TABLE `form_xsrf_tokens` (
  `form_xsrf_token_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `form_xsrf_token_user_session_id` INT UNSIGNED NULL,
  `form_xsrf_token_customer_session_id` INT UNSIGNED NULL,
  `form_xsrf_token_hash` VARCHAR(255) NOT NULL,  
  `form_xsrf_token_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `form_xsrf_token_expires` DATETIME NOT NULL,
  `form_xsrf_token_ip` VARCHAR(15) NOT NULL,
  `form_xsrf_token_form_name` VARCHAR(50),
  
  PRIMARY KEY (`form_xsrf_token_id`),
  
  CONSTRAINT `form_xsrf_token_user_session_user_fk`
    FOREIGN KEY (`form_xsrf_token_user_session_id`)
    REFERENCES `user_sessions` (`user_session_id`)
    ON DELETE CASCADE,
  
  CONSTRAINT `form_xsrf_token_customer_session_customer_fk`
    FOREIGN KEY (`form_xsrf_token_customer_session_id`)
    REFERENCES `customer_sessions` (`customer_session_id`)
    ON DELETE CASCADE

) ENGINE = InnoDB;

DROP TABLE IF EXISTS `static_pages`;

CREATE TABLE `static_pages` (
 `static_page_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
 `static_page_title` VARCHAR(100) NOT NULL,
 `static_page_alias_id` INT UNSIGNED,
 `static_page_content` TEXT,
 
  PRIMARY KEY (`static_page_id`),
   CONSTRAINT `static_page_alias_fk`
    FOREIGN KEY (`static_page_alias_id`)
    REFERENCES `aliases` (`alias_id`)
    ON DELETE SET NULL
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewStaticPages`;

CREATE VIEW viewStaticPages AS
	SELECT *
    FROM static_pages sp
    LEFT OUTER JOIN aliases a ON (a.alias_id = sp.static_page_alias_id);

DROP VIEW IF EXISTS `viewPermissionsByUser`;
  
CREATE VIEW viewPermissionsByUser AS
	SELECT *
    FROM permissions p
    JOIN role_permissions rp ON (rp.role_permission_permission_id = p.permission_id)
    JOIN user_roles ur ON (ur.user_role_role_id = rp.role_permission_role_id);

DROP VIEW IF EXISTS `viewSessionsStats` ;

CREATE VIEW viewSessionsStats AS
	SELECT 'Anonymous' as n, COUNT(*) as c
    FROM customer_sessions cs
    LEFT OUTER JOIN customers c ON (c.customer_id = cs.customer_session_customer_id)
    WHERE c.customer_state = 0
    
    UNION
    
    SELECT 'Customers' as n, COUNT(*) as c
    FROM customer_sessions cs
    LEFT OUTER JOIN customers c ON (c.customer_id = cs.customer_session_customer_id)
    WHERE c.customer_state > 0
    
    UNION
    
    SELECT 'Admins' as n, COUNT(*) as c FROM user_sessions;
