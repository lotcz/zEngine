DROP DATABASE IF EXISTS zenginetest;
CREATE DATABASE zenginetest DEFAULT char set utf8;
USE zenginetest;

CREATE TABLE IF NOT EXISTS `currencies` (
  `currency_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency_name` NVARCHAR(50) NOT NULL,
  `currency_format` NVARCHAR(20) NOT NULL,
  `currency_value` DECIMAL(20,10) NOT NULL,
  `currency_decimals` TINYINT NOT NULL DEFAULT 2,
  PRIMARY KEY (`currency_id`),
  UNIQUE INDEX `currency_name_unique` (`currency_name` ASC))
ENGINE = InnoDB;

INSERT INTO currencies (currency_name, currency_format, currency_value, currency_decimals) VALUES ('Kč','%s&nbsp;Kč', 1, 0);
INSERT INTO currencies (currency_name, currency_format, currency_value, currency_decimals) VALUES ('EUR','EUR%s', 27.02, 2);

CREATE TABLE IF NOT EXISTS `languages` (
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

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_deleted` BIT DEFAULT 0 NOT NULL,
  `user_is_superuser` BIT DEFAULT 0 NOT NULL,
  `user_login` VARCHAR(50),
  `user_email` VARCHAR(50) NOT NULL,
  `user_password_hash` VARCHAR(255) NULL,
  `user_failed_attempts` INT NOT NULL DEFAULT 0,
  `user_last_access` TIMESTAMP,
  `user_reset_password_hash` VARCHAR(255) NULL,
  `user_reset_password_until` TIMESTAMP NULL,
  `user_language_id` TINYINT UNSIGNED NOT NULL,
 
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `users_email_unique` (`user_email` ASC),
  UNIQUE INDEX `users_login_unique` (`user_login` ASC),
  CONSTRAINT `user_language_fk`
    FOREIGN KEY (`user_language_id`)
    REFERENCES `languages` (`language_id`)
) ENGINE = InnoDB;

INSERT INTO `users` (`user_login`, `user_email`, `user_password_hash`, `user_is_superuser`, `user_language_id`) VALUES ( 'karel', 'mojemejly@centrum.cz', '$2y$10$bhLm9lallISZPBloadZvH.NgvbGnijLJCvRAWkIlYNzycdTWI2w4S', 1, 1); /*karel/karel123*/

CREATE TABLE IF NOT EXISTS `user_sessions` (
  `user_session_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_session_token_hash` VARCHAR(255) NOT NULL,
  `user_session_user_id` INT(10) UNSIGNED NOT NULL,
  `user_session_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_session_expires` TIMESTAMP NOT NULL,
  PRIMARY KEY (`user_session_id`),
  CONSTRAINT `user_session_user_fk`
    FOREIGN KEY (`user_session_user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_name` NVARCHAR(50) NOT NULL,
  `role_description` NVARCHAR(255),
  PRIMARY KEY (`role_id`),
  UNIQUE INDEX `role_name_unique` (`role_name` ASC)
) ENGINE = InnoDB;

INSERT INTO roles (role_name) VALUES (N'Users admin');
INSERT INTO roles (role_name) VALUES (N'Products admin');
INSERT INTO roles (role_name) VALUES (N'Orders admin');

CREATE TABLE IF NOT EXISTS `user_roles` (
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

CREATE TABLE IF NOT EXISTS `permissions` (
  `permission_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_name` VARCHAR(50) NOT NULL,  
  `permission_description` VARCHAR(255),  
  PRIMARY KEY (`permission_id`),
  UNIQUE INDEX `permissions_name_unique` (`permission_name`)
) ENGINE = InnoDB;

INSERT INTO permissions (permission_name, permission_description) VALUES ('edit user', 'Edit users');
INSERT INTO permissions (permission_name, permission_description) VALUES ('browse order', 'Browse orders');
INSERT INTO permissions (permission_name, permission_description) VALUES ('edit order', 'Edit orders');
INSERT INTO permissions (permission_name, permission_description) VALUES ('browse product', 'Browse products');
INSERT INTO permissions (permission_name, permission_description) VALUES ('edit product', 'Edit products');

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_permission_role_id` INT UNSIGNED NOT NULL,
  `role_permission_permission_id` INT UNSIGNED NOT NULL,  
  PRIMARY KEY (`role_permission_role_id`,  `role_permission_permission_id`)
) ENGINE = InnoDB;
