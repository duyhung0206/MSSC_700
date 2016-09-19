<?php
$installer = $this;
$installer->startSetup();


$installer->run(" 
 
    ALTER TABLE  {$this->getTable('sales/invoice')} ADD  `fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/invoice')} ADD  `base_fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/invoice')} ADD  `discountexchange_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/invoice')} ADD  `base_discountexchange_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';

    ALTER TABLE  {$this->getTable('sales/creditmemo')} ADD  `fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/creditmemo')} ADD  `base_fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/creditmemo')} ADD  `discountexchange_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/creditmemo')} ADD  `base_discountexchange_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';

  ALTER TABLE  {$this->getTable('sales/invoice')} ADD  `refundcredit_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/invoice')} ADD  `base_refundcredit_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';

    ALTER TABLE  {$this->getTable('sales/creditmemo')} ADD  `refundcredit_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/creditmemo')} ADD  `base_refundcredit_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';

");

$installer->endSetup();

