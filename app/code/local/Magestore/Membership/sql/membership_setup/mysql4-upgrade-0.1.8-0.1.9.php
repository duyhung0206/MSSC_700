<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$installer->run(" 
    ALTER TABLE  {$this->getTable('sales/quote_address')} ADD  `fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/quote_address')} ADD  `base_fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/order')} ADD  `fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
    ALTER TABLE  {$this->getTable('sales/order')} ADD  `base_fee_amount` DECIMAL( 12, 4 ) NOT NULL default '0.0000';
");

$installer->endSetup();

