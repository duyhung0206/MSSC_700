<?php

$installer = $this;

$installer->startSetup();

$entityType = Mage::getSingleton('eav/entity_type')->loadByCode('catalog_product');
$entityTypeId = $entityType->getId();

$attributeSetName = 'Membership';
$defaultSetId = Mage::getResourceModel('catalog/setup', 'core_setup')->getAttributeSetId($entityTypeId, 'Default');

$model = Mage::getModel('eav/entity_attribute_set')->load($attributeSetName,"attribute_set_name")
			->setEntityTypeId($entityTypeId)
			->setAttributeSetName($attributeSetName);
try{
	$model->save();
}
catch(Mage_Core_Exception $e){

}catch (Exception $e) {}

$model  = Mage::getModel('eav/entity_attribute_set')->load($model->getId());
$model = $model->initFromSkeleton($defaultSetId)
				->save();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('membership_group')};
CREATE TABLE {$this->getTable('membership_group')} (
  `group_id` int(10) unsigned NOT NULL auto_increment,
  `group_name` varchar(255) NOT NULL default '',
  `description` text NOT NULL default '',
  `group_product_price` varchar(10) NOT NULL default '',
  `group_status` tinyint(1) NOT NULL default '1',
  `priority` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('membership_group_product')};
CREATE TABLE {$this->getTable('membership_group_product')} (
  `group_product_id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL,
  `product_id` int(11) unsigned  NOT NULL, 
  FOREIGN KEY (`group_id`) REFERENCES {$this->getTable('membership_group')} (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog/product')}  (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`group_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('membership_package')};
CREATE TABLE {$this->getTable('membership_package')} (
  `package_id` int(10) unsigned NOT NULL auto_increment,
  `package_name` varchar(255) NOT NULL default '',
  `package_price` float NOT NULL default '0.0000',
  `duration` tinyint(2) unsigned NOT NULL default '0',
  `description` text NOT NULL default '',
  `package_product_price` varchar(10) NOT NULL default '',
  `product_id` int(11) unsigned  NOT NULL,
  `sort_order` smallint(3) unsigned NOT NULL default '0',
  `package_status` tinyint(1) NOT NULL default '1',
  `custom_option_discount` VARCHAR( 10 ) NOT NULL DEFAULT 'no',
  FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog/product')}  (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('membership_package_group')};
CREATE TABLE {$this->getTable('membership_package_group')} (
  `package_group_id` int(10) unsigned NOT NULL auto_increment,
  `package_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  INDEX (`package_id`),
  INDEX (`group_id`),
  FOREIGN KEY (`package_id`) REFERENCES {$this->getTable('membership_package')} (`package_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`group_id`) REFERENCES {$this->getTable('membership_group')} (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`package_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('membership_package_product')};
CREATE TABLE {$this->getTable('membership_package_product')} (
  `package_product_id` int(10) unsigned NOT NULL auto_increment,
  `package_id` int(10) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  INDEX (`package_id`),
  INDEX (`product_id`),
  FOREIGN KEY (`package_id`) REFERENCES {$this->getTable('membership_package')} (`package_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog/product')}  (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`package_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('membership_member')};
CREATE TABLE {$this->getTable('membership_member')} (
  `member_id` int(10) unsigned NOT NULL auto_increment,
  `customer_id` int(10) unsigned NOT NULL,
  `member_name` varchar(255) NOT NULL default '',
  `member_email` varchar(255) NOT NULL default '',
  `joined_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL default '1',
  INDEX(`customer_id`),
  FOREIGN KEY (`customer_id`) REFERENCES {$this->getTable('customer/entity')}(`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE, 
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('membership_payment_history')};
CREATE TABLE {$this->getTable('membership_payment_history')} (
  `payment_history_id` int(10) unsigned NOT NULL auto_increment,
  `member_id` int(10) unsigned NOT NULL,  
  `order_id` int(11) unsigned NOT NULL,
  `start_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `end_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `duration` int(11) unsigned NOT NULL,  
  `package_name` varchar(255) NOT NULL default '',
  `price` float NOT NULL default '0.0000',
  
  PRIMARY KEY (`payment_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('membership_member_package')};
CREATE TABLE {$this->getTable('membership_member_package')} (
  `member_package_id` int(10) unsigned NOT NULL auto_increment,
  `member_id` int(10) unsigned NOT NULL,
  `package_id` int(10) unsigned NOT NULL,  
  `end_time` datetime NOT NULL default '0000-00-00 00:00:00',      
  `status` tinyint(1) NOT NULL default '1',
  `bought_item_total` int(10) unsigned NOT NULL default '0',
  `saved_total` float NOT NULL default '0.0000',
  INDEX(`member_id`),
  INDEX(`package_id`),
  
  FOREIGN KEY (`package_id`) REFERENCES {$this->getTable('membership_package')} (`package_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`member_id`) REFERENCES {$this->getTable('membership_member')} (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  
  PRIMARY KEY (`member_package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 