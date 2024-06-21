DROP TABLE IF EXISTS `newsletter_subscription`;

CREATE TABLE IF NOT EXISTS `newsletter_subscription` (
  `newsletter_subscription_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `newsletter_subscription_email` VARCHAR(255) NOT NULL,
  `newsletter_subscription_active` tinyint not null default 1,
  PRIMARY KEY (`newsletter_subscription_id`),
  CONSTRAINT `newsletter_subscription_email_uq`
    unique KEY (`newsletter_subscription_email`)
) ENGINE = InnoDB;

DROP VIEW IF EXISTS `view_newsletter_subscriptions_stats`;

CREATE VIEW view_newsletter_subscriptions_stats AS
select newsletter_subscription_active, COUNT(*) as cnt from newsletter_subscription group by newsletter_subscription_active;
