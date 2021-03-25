DROP TABLE IF EXISTS `customer`;

CREATE TABLE `customer` (
  `customer_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_user_id` INT UNSIGNED NOT NULL,

  `customer_phone` VARCHAR(50),
  `customer_email` VARCHAR(50),

  `customer_invoicing_identification` VARCHAR(50),
  `customer_invoicing_name` VARCHAR(50),
  `customer_invoicing_street` VARCHAR(50),
  `customer_invoicing_city` VARCHAR(50),
  `customer_invoicing_zip` INT,

  `customer_use_shipping_address` BOOL NOT NULL DEFAULT 0,

  `customer_shipping_name` VARCHAR(50),
  `customer_shipping_street` VARCHAR(50),
  `customer_shipping_city` VARCHAR(50),
  `customer_shipping_zip` INT,

  PRIMARY KEY (`customer_id`),
  UNIQUE INDEX `customer_user_unique_index` (`customer_user_id`),
  CONSTRAINT `customer_user_fk`
    FOREIGN KEY (`customer_user_id`)
    REFERENCES `user` (`user_id`)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `product_category`;

CREATE TABLE IF NOT EXISTS `product_category` (
  `product_category_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_category_parent_id` INT UNSIGNED,
  `product_category_alias_id` INT UNSIGNED,
  `product_category_name` NVARCHAR(200) NOT NULL,
  `product_category_description` TEXT NULL,
  `product_category_total_products` INT UNSIGNED NOT NULL DEFAULT 0,
  `product_category_visible` BOOL NOT NULL DEFAULT 1,
  PRIMARY KEY (`product_category_id`),
  INDEX `product_categorz_parent_id_index` (`product_category_parent_id` ASC),
  CONSTRAINT `product_category_parent_fk`
    FOREIGN KEY (`product_category_parent_id`)
    REFERENCES `product_category` (`product_category_id`)
    ON DELETE SET NULL,
  CONSTRAINT `product_category_alias_fk`
    FOREIGN KEY (`product_category_alias_id`)
    REFERENCES `alias` (`alias_id`)
    ON DELETE SET NULL
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewProductCategories`;

CREATE VIEW viewProductCategories AS
	SELECT *
    FROM product_category c
    LEFT OUTER JOIN alias a ON (a.alias_id = c.product_category_alias_id);

DROP TABLE IF EXISTS `product`;

CREATE TABLE IF NOT EXISTS `product` (
  `product_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_visible` BOOL DEFAULT 1 NOT NULL,
  `product_product_category_id` INT UNSIGNED NOT NULL,
  `product_alias_id` INT UNSIGNED NULL,
  `product_name` NVARCHAR(255) NOT NULL,
  `product_slug` NVARCHAR(255) NOT NULL,
  `product_price` DECIMAL(10,2) UNSIGNED NOT NULL,
  `product_stock` INT UNSIGNED NOT NULL DEFAULT 0,
  `product_image` VARCHAR(255) NULL,
  `product_description` TEXT NULL,
  PRIMARY KEY (`product_id`),
  CONSTRAINT `product_category_fk`
    FOREIGN KEY (`product_product_category_id`)
    REFERENCES `product_category` (`product_category_id`),
  CONSTRAINT `product_alias_fk`
    FOREIGN KEY (`product_alias_id`)
    REFERENCES `alias` (`alias_id`)
    ON DELETE SET NULL,
  UNIQUE INDEX `product_slug_unique` (`product_slug` ASC)
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewProducts`;

CREATE VIEW viewProducts AS
	SELECT *
    FROM product p
    LEFT OUTER JOIN product_category c ON (p.product_product_category_id = c.product_category_id)
    LEFT OUTER JOIN alias a ON (a.alias_id = p.product_alias_id);

DROP PROCEDURE IF EXISTS spUpdateCategoryProductCount;

DELIMITER //
CREATE PROCEDURE spUpdateCategoryProductCount(IN cat_id INT UNSIGNED)
BEGIN
	DECLARE total INT DEFAULT 0;
	SELECT COUNT(*) INTO total
	FROM product WHERE product_product_category_id = cat_id;
	UPDATE product_category SET product_category_total_products = total WHERE product_category_id = cat_id;
END //
DELIMITER ;

DROP TRIGGER IF EXISTS update_product_count_trigger;

DELIMITER //
CREATE TRIGGER update_product_count_trigger AFTER UPDATE ON product
FOR EACH ROW
	BEGIN
		IF OLD.product_product_category_id <> NEW.product_product_category_id THEN
			CALL spUpdateCategoryProductCount(OLD.product_product_category_id);
			CALL spUpdateCategoryProductCount(NEW.product_product_category_id);
		END IF;
	END //
DELIMITER ;

DROP TRIGGER IF EXISTS update_product_count_trigger_ins;

DELIMITER //
CREATE TRIGGER update_product_count_trigger_ins AFTER INSERT ON product
FOR EACH ROW
	BEGIN
		CALL spUpdateCategoryProductCount(NEW.product_product_category_id);
	END //
DELIMITER ;

DROP TRIGGER IF EXISTS update_product_count_trigger_del;

DELIMITER //
CREATE TRIGGER update_product_count_trigger_del AFTER DELETE ON product
FOR EACH ROW
	BEGIN
		CALL spUpdateCategoryProductCount(OLD.product_product_category_id);
	END //
DELIMITER ;

CREATE TABLE IF NOT EXISTS `cart` (
  `cart_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cart_customer_id` INT UNSIGNED NOT NULL,
  `cart_product_id` INT UNSIGNED NOT NULL,
  `cart_count` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`cart_id`),
  CONSTRAINT `cart_product_fk`
    FOREIGN KEY (`cart_product_id`)
    REFERENCES `product` (`product_id`)
    ON DELETE CASCADE,
  CONSTRAINT `cart_customer_fk`
    FOREIGN KEY (`cart_customer_id`)
    REFERENCES `customer` (`customer_id`)
    ON DELETE CASCADE
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewProductsInCart` ;

CREATE VIEW viewProductsInCart AS
	SELECT *
    FROM cart c
    LEFT OUTER JOIN product p ON (c.cart_product_id = p.product_id)
    LEFT OUTER JOIN alias a ON (a.alias_id = p.product_alias_id);

CREATE TABLE IF NOT EXISTS `order_state` (
 `order_state_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
 `order_state_closed` BOOL NOT NULL,
 `order_state_name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`order_state_id`)
) ENGINE = InnoDB;

INSERT INTO `order_state` (`order_state_closed`, `order_state_name`) VALUES (0,'New (waiting for payment)'),(0,'Processing'),(0,'Re-opened'),(1,'Shipped (closed)'),(1,'Cancelled');

CREATE TABLE IF NOT EXISTS `delivery_type` (
 `delivery_type_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
 `delivery_type_name` VARCHAR(50) NOT NULL,
 `delivery_type_description` TEXT,
 `delivery_type_price` DECIMAL(10,2) NOT NULL DEFAULT 0,
 `delivery_type_require_address` BOOL NOT NULL DEFAULT 0,
 `delivery_type_min_order_cost` DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`delivery_type_id`)
) ENGINE = InnoDB;

INSERT INTO `delivery_type` (`delivery_type_name`, `delivery_type_price`, `delivery_type_require_address`, `delivery_type_min_order_cost`)
VALUES ('Pick up in store', 0, 0, 0),('Czech post', 118, 1, 0),('Parcel service', 99, 1, 0);

CREATE TABLE IF NOT EXISTS `payment_type` (
 `payment_type_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
 `payment_type_name` VARCHAR(50) NOT NULL,
 `payment_type_description` TEXT,
 `payment_type_price` DECIMAL(10,2) NOT NULL DEFAULT 0,
 `payment_type_min_order_cost` DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`payment_type_id`)
) ENGINE = InnoDB;

INSERT INTO `payment_type` (`payment_type_name`, `payment_type_price`,  `payment_type_min_order_cost`)
VALUES ('Cash in store',0,0),('Cash on delivery',35,0),('Bank transfer',0,0);

CREATE TABLE IF NOT EXISTS `allowed_payment_type` (
  `allowed_payment_type_delivery_type_id` TINYINT UNSIGNED NOT NULL,
  `allowed_payment_type_payment_type_id` TINYINT UNSIGNED NOT NULL,

  PRIMARY KEY (`allowed_payment_type_delivery_type_id`, `allowed_payment_type_payment_type_id`),

  CONSTRAINT `allowed_payment_types_delivery_type_fk`
    FOREIGN KEY (`allowed_payment_type_delivery_type_id`)
    REFERENCES `delivery_type` (`delivery_type_id`),
  CONSTRAINT `allowed_payment_types_payment_type_fk`
    FOREIGN KEY (`allowed_payment_type_payment_type_id`)
    REFERENCES `payment_type` (`payment_type_id`)
) ENGINE = InnoDB;

INSERT INTO `allowed_payment_type` (`allowed_payment_type_delivery_type_id`, `allowed_payment_type_payment_type_id`)
VALUES (1,1),(2,2),(2,3),(3,2),(3,3);

CREATE TABLE IF NOT EXISTS `order` (
  `order_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` INT UNSIGNED NOT NULL,
  `order_order_state_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `order_customer_id` INT UNSIGNED NOT NULL,
  `order_delivery_type_id` TINYINT UNSIGNED NOT NULL,
  `order_payment_type_id` TINYINT UNSIGNED NOT NULL,
  `order_payment_code` INT UNSIGNED NULL,
  `order_currency_id` TINYINT UNSIGNED NOT NULL,
  `order_delivery_type_price` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0,
  `order_payment_type_price` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0,
  `order_total_cart_price` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0,
  `order_total_price` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0,

  `order_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `order_last_status_change` TIMESTAMP NULL,

  `order_shipping_name` VARCHAR(50),
  `order_shipping_street` VARCHAR(50),
  `order_shipping_city` VARCHAR(50),
  `order_shipping_zip` INT,

  PRIMARY KEY (`order_id`),
  UNIQUE INDEX `order_payment_code_unique_index` (`order_payment_code`),
  UNIQUE INDEX `order_number_unique_index` (`order_number`),
  CONSTRAINT `order_customer_fk`
    FOREIGN KEY (`order_customer_id`)
    REFERENCES `customer` (`customer_id`),
  CONSTRAINT `order_delivery_type_fk`
    FOREIGN KEY (`order_delivery_type_id`)
    REFERENCES `delivery_type` (`delivery_type_id`),
  CONSTRAINT `order_payment_type_fk`
    FOREIGN KEY (`order_payment_type_id`)
    REFERENCES `payment_type` (`payment_type_id`),
  CONSTRAINT `order_currency_fk`
    FOREIGN KEY (`order_currency_id`)
    REFERENCES `currency` (`currency_id`)
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewOrders`;

CREATE VIEW viewOrders AS
	SELECT *
	FROM `order` o
	LEFT OUTER JOIN customer c ON (c.customer_id = o.order_customer_id)
	LEFT OUTER JOIN order_state os ON (os.order_state_id = o.order_order_state_id);

CREATE TABLE IF NOT EXISTS `order_product` (
  `order_product_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_product_order_id` INT UNSIGNED NOT NULL,
  `order_product_product_id` INT UNSIGNED NULL,
  `order_product_name` VARCHAR(255) NOT NULL,
  `order_product_price` DECIMAL(10,2) UNSIGNED NOT NULL,
  `order_product_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `order_product_total_price` DECIMAL(10,2) UNSIGNED NOT NULL,

  PRIMARY KEY (`order_product_id`),
  CONSTRAINT `order_product_product_fk`
    FOREIGN KEY (`order_product_product_id`)
    REFERENCES `product` (`product_id`)
    ON DELETE SET NULL,
  CONSTRAINT `order_product_order_fk`
    FOREIGN KEY (`order_product_order_id`)
    REFERENCES `order` (`order_id`)
    ON DELETE CASCADE
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewOrderProducts`;

CREATE VIEW viewOrderProducts AS
	SELECT *
	FROM order_product o
	LEFT OUTER JOIN product p ON (p.product_id = o.order_product_product_id);
