<?php

$installer = $this;
$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('membership_transaction')};
	
CREATE TABLE {$this->getTable('membership_transaction')} (
  `transaction_id` int(10) unsigned NOT NULL auto_increment,
  `customer_id` int(10) unsigned NOT NULL ,
  `detail_transaction` varchar(255)  NULL default '',
  `product_id_old` int(11) unsigned  NOT NULL,
  `order_id_old` varchar(30) NULL default '',
  `product_id_new` int(11) unsigned  NOT NULL,
  `order_id_new` varchar(30) NULL default '',
  `transaction_time` datetime NULL,
  PRIMARY KEY (`transaction_id`),
  INDEX (`customer_id`),
  FOREIGN KEY (`customer_id`)
  REFERENCES {$this->getTable('customer_entity')} (`entity_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();


$installer->endSetup();
