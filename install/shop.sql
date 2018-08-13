DROP TABLE IF EXISTS `customer`;

CREATE TABLE `customer` (
  `customer_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

  `customer_currency_id` TINYINT UNSIGNED NOT NULL,

  PRIMARY KEY (`customer_id`),
  INDEX `customer_email_index` (`customer_email` ASC),
  CONSTRAINT `customer_currency_fk`
    FOREIGN KEY (`customer_currency_id`)
    REFERENCES `currencies` (`currency_id`),
 CONSTRAINT `customer_language_fk`
    FOREIGN KEY (`customer_language_id`)
    REFERENCES `language` (`language_id`)
) ENGINE = InnoDB;

INSERT INTO alias (alias_url, alias_path) VALUES ('kosik', 'cart');
