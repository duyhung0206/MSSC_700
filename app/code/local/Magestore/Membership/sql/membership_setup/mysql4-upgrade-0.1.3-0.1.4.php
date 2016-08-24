<?php
$installer = $this;
$installer->startSetup();
$installer->run("
   DROP TABLE IF EXISTS {$this->getTable('membership_package_value')};
   CREATE TABLE {$this->getTable('membership_package_value')} (
  `value_id` int(10) unsigned NOT NULL auto_increment,
  `package_id` int(11) unsigned NOT NULL,
  `store_id` smallint(5) unsigned  NOT NULL,
  `attribute_code` varchar(63) NOT NULL default '',
  `value` text NOT NULL,
  UNIQUE(`package_id`,`store_id`,`attribute_code`),
  INDEX (`package_id`),
  INDEX (`store_id`),
  FOREIGN KEY (`package_id`) REFERENCES {$this->getTable('membership_package')} (`package_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
$installer->endSetup();

?>