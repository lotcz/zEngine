DROP TABLE IF EXISTS `gallery`;

CREATE TABLE `gallery` (
	`gallery_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`gallery_name` VARCHAR(255),
	PRIMARY KEY (`gallery_id`),
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `image`;

CREATE TABLE IF NOT EXISTS `image` (
	`image_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`image_gallery_id` INT UNSIGNED NOT NULL,
	`image_path` NVARCHAR(255) NOT NULL,
	`image_title` NVARCHAR(255) NULL,
	`image_description` TEXT NULL,
	PRIMARY KEY (`image_id`),
	CONSTRAINT `image_gallery_fk`
		FOREIGN KEY (`image_gallery_id`)
		REFERENCES `gallery` (`gallery_id`),
	UNIQUE INDEX `image_path_unique` (`image_path` ASC)
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewImages`;

CREATE VIEW viewImages AS
	SELECT *
	FROM image i
	LEFT OUTER JOIN gallery g ON (i.image_gallery_id = g.gallery_id);
