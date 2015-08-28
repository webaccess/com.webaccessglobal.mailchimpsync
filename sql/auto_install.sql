DROP TABLE IF EXISTS `civicrm_mailchimp`;
-- /*******************************************************
-- *
-- * civimailchimp
-- *
-- *******************************************************/
CREATE TABLE `civicrm_mailchimp` (
     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'ID',
     `list_id` varchar(255) NOT NULL   COMMENT 'List Id',
     `last_synced` datetime DEFAULT NULL  COMMENT 'Last Synced Members Date',

    PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
