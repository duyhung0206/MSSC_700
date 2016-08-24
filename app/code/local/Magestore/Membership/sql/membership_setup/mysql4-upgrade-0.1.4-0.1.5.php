<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('membership_package')}
        ADD COLUMN `discount_type` tinyint default 1,
        ADD COLUMN `url_key` varchar(255) NULL,
        ADD COLUMN `unit_of_time` varchar(10) NOT NULL default 'day';
    
    ALTER TABLE {$this->getTable('membership_group')}
        MODIFY COLUMN `priority` int(10) NULL;
    
    ALTER TABLE {$this->getTable('membership_payment_history')}
        ADD COLUMN `unit_of_time` varchar(10) NOT NULL default 'day';
        
    ");
$installer->endSetup();

?>