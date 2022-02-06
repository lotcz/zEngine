DROP TABLE IF EXISTS `newsletter_subscription`;

CREATE TABLE IF NOT EXISTS `newsletter_subscription` (
  `newsletter_subscription_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `newsletter_subscription_email` VARCHAR(255) NOT NULL,
  `newsletter_subscription_active` tinyint not null default 1,
  PRIMARY KEY (`newsletter_subscription_id`),
  CONSTRAINT `newsletter_subscription_email_uq`
    unique KEY (`newsletter_subscription_email`)
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `viewNewsletterSubscriptionsStats`;

CREATE VIEW viewNewsletterSubscriptionsStats AS
select newsletter_subscription_active, COUNT(*) as cnt from newsletter_subscription group by newsletter_subscription_active;