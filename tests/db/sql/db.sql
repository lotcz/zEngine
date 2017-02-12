DROP DATABASE IF EXISTS zenginetest;
CREATE DATABASE zenginetest DEFAULT char set utf8;
USE zenginetest;

CREATE TABLE `test` (
  `test_id` INT NOT NULL AUTO_INCREMENT,
  `test_value` varchar(100),
  
  PRIMARY KEY (`test_id`)
) ENGINE=InnoDB;
