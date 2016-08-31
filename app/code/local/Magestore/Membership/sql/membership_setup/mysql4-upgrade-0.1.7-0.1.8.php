<?php
$installer = $this;
$installer->startSetup();
$installer->run("

ALTER TABLE {$this->getTable('membership_member_package')}
  ADD COLUMN `time_save_block` DECIMAL default 0;

");

$installer->endSetup();

?>