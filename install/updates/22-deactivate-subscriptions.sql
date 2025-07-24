ALTER TABLE newsletter_subscription ADD COLUMN newsletter_subscription_state ENUM('active', 'unsubscribed', 'invalid') DEFAULT 'active';
ALTER TABLE newsletter_subscription ADD COLUMN newsletter_subscription_state_changed timestamp DEFAULT CURRENT_TIMESTAMP();

UPDATE newsletter_subscription SET newsletter_subscription_state = 'unsubscribed' WHERE newsletter_subscription_active = 0; 

ALTER TABLE newsletter_subscription DROP COLUMN newsletter_subscription_active;

CREATE OR REPLACE VIEW `view_newsletter_subscriptions_stats` AS
select
    newsletter_subscription_state,
    count(0) AS `cnt`
from
    newsletter_subscription
group by
    newsletter_subscription_state;
    
CREATE INDEX newsletter_subscription_state_ix
ON newsletter_subscription(newsletter_subscription_state);
 