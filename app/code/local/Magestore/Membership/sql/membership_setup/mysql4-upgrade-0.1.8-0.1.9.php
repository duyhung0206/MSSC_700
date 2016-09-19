<?php
$installer = $this;
$installer->startSetup();
$installer->run("
  DROP TABLE IF EXISTS {$this->getTable('membership_group_price')};
CREATE TABLE {$this->getTable('membership_group_price')} (
 `group_price_id` int(10) unsigned NOT NULL auto_increment,
 `group_from` int(10) unsigned NOT NULL,
 `group_to` int(10) unsigned NOT NULL,  
 `price` float NOT NULL default '0.0000',
 
 FOREIGN KEY (`group_from`) REFERENCES {$this->getTable('membership_group')} (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 FOREIGN KEY (`group_to`) REFERENCES {$this->getTable('membership_group')} (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 PRIMARY KEY (`group_price_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");


$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$installer->run(" 
    ALTER TABLE  {$this->getTable('sales/quote_address')} ADD  `fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/quote_address')} ADD  `base_fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/order')} ADD  `fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/order')} ADD  `base_fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
");

$installer->endSetup();

