<?php

$installer = $this;

$installer->startSetup();
$installer->run("

ALTER TABLE {$this->getTable('membership_member_package')}
  ADD COLUMN `order_ids` varchar(255) default NULL;

");

$installer->endSetup(); 