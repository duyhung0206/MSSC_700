<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('membership_member_package')}
        ADD COLUMN `auto_renew` tinyint(1) NOT NULL default '1';
        
    ");
$installer->endSetup();

?>