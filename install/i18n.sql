DROP TABLE IF EXISTS `currency`;

CREATE TABLE `currency` (
  `currency_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency_name` NVARCHAR(50) NOT NULL,
  `currency_format` NVARCHAR(20) NOT NULL,
  `currency_value` DECIMAL(20,10) NOT NULL,
  `currency_decimals` TINYINT NOT NULL DEFAULT 2,
  PRIMARY KEY (`currency_id`),
  UNIQUE INDEX `currency_name_unique` (`currency_name` ASC))
ENGINE = InnoDB;

INSERT INTO currency (currency_name, currency_format, currency_value, currency_decimals) VALUES ('Kč','%s&nbsp;Kč', 1, 0);
INSERT INTO currency (currency_name, currency_format, currency_value, currency_decimals) VALUES ('EUR','%s&nbsp;EUR', 27.02, 2);

DROP TABLE IF EXISTS `language`;

CREATE TABLE `language` (
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
    REFERENCES `currency` (`currency_id`)
)ENGINE = InnoDB;

INSERT INTO language VALUES (NULL, 'English', 'en', 'j/n/Y', 'j/n/Y h:i:s', '.', ',', 2);
INSERT INTO language VALUES (NULL, 'Čeština', 'cs', 'j.n.Y', 'j.n.Y H:i:s', ',', '&nbsp', 1);
