<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('membership_updown_transaction')};
DROP TABLE IF EXISTS {$this->getTable('membership_updown_type_transaction')};

CREATE TABLE {$this->getTable('membership_updown_transaction')} (
  `transaction_id` int(10) unsigned NOT NULL auto_increment,
  `member_id` int(10) unsigned NOT NULL ,
  `type_transaction_id` int(11) NULL,
  `detail_transaction` varchar(255)  NULL default '',
  `order_increment_id` varchar(30) NULL default '',
  `price` decimal(12,4) default '0',
  `status` varchar(20) default '',
  `transaction_time` datetime NULL,
  PRIMARY KEY (`transaction_id`),
  INDEX (`member_id`),
  FOREIGN KEY (`member_id`)
  REFERENCES {$this->getTable('membership_member')} (`member_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('membership_updown_type_transaction')} (
  `type_transaction_id` int(11) unsigned NOT NULL auto_increment,
  `transaction_name` varchar(255) default '',
  PRIMARY KEY (`type_transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT  INTO {$this->getTable('membership_updown_type_transaction')}(`type_transaction_id`,`transaction_name`) 
VALUES  (1,'Upgrade membership package'),
        (2,'Downgrade membership package')
;
        
    ");
$installer->endSetup();

?>