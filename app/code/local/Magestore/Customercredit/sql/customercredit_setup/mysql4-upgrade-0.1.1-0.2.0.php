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

$setup = new Mage_Eav_Model_Entity_Setup('catalog_setup');

$installer->startSetup();

//update sales_order table
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'base_customercredit_discount_for_shipping', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'customercredit_discount_for_shipping', 'decimal(12,4) NULL');

$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'base_customercredit_hidden_tax', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'customercredit_hidden_tax', 'decimal(12,4) NULL');

$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'base_customercredit_shipping_hidden_tax', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'customercredit_shipping_hidden_tax', 'decimal(12,4) NULL');


//update sales_invoice
$installer->getConnection()->addColumn($installer->getTable('sales/invoice'), 'base_customercredit_hidden_tax', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($installer->getTable('sales/invoice'), 'customercredit_hidden_tax', 'decimal(12,4) NULL');

//update sales_creditmemo
$installer->getConnection()->addColumn($installer->getTable('sales/creditmemo'), 'base_customercredit_hidden_tax', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($installer->getTable('sales/creditmemo'), 'customercredit_hidden_tax', 'decimal(12,4) NULL');

//update sales_order_item
$installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'base_customercredit_hidden_tax', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'customercredit_hidden_tax', 'decimal(12,4) NULL');

//remove attribute
//$setup->removeAttribute('catalog_product', 'credit_amount');
//$setup->removeAttribute('catalog_product', 'credit_value');
$setup->removeAttribute('catalog_product', 'storecredit_value');
$setup->removeAttribute('catalog_product', 'storecredit_from');
$setup->removeAttribute('catalog_product', 'storecredit_to');
$setup->removeAttribute('catalog_product', 'storecredit_dropdown');
$setup->removeAttribute('catalog_product', 'storecredit_type');

/* add Store Credit product attributes */

/**
 * add storecredit_value attribute
 */
$att_storecredit_value = array(
    'group' => 'Prices',
    'type' => 'decimal',
    'input' => 'price',
    'class' => 'validate-number',
    'label' => 'Store Credit value',
    'backend' => '',
    'frontend' => '',
    'source' => '',
    'visible' => 1,
    'user_defined' => 1,
    'used_for_price_rules' => 1,
    'position' => 4,
    'unique' => 0,
    'default' => '',
    'sort_order' => 101,
);
$setup->addAttribute('catalog_product', 'storecredit_value', $att_storecredit_value);
$storecreditValue = Mage::getModel('catalog/resource_eav_attribute')->load($setup->getAttributeId('catalog_product', 'storecredit_value'));
$storecreditValue->addData(array(
    'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'is_required' => 1,
    'apply_to' => array('customercredit'),
    'is_configurable' => 1,
    'is_searchable' => 0,
    'is_visible_in_advanced_search' => 0,
    'is_comparable' => 0,
    'is_filterable' => 0,
    'is_filterable_in_search' => 1,
    'is_used_for_promo_rules' => 1,
    'is_html_allowed_on_front' => 0,
    'is_visible_on_front' => 0,
    'used_in_product_listing' => 1,
    'used_for_sort_by' => 0,
))->save();

//show description of Store Credit
$attr = array(
    'group' => 'Prices',
    'type' => 'int',
    'input' => 'boolean',
    'label' => 'Show description of Store Credit value',
    'backend' => '',
    'frontend' => '',
    'source' => '',
    'visible' => 1,
    'user_defined' => 1,
    'used_for_price_rules' => 1,
    'position' => 10,
    'unique' => 0,
    'default' => '',
    'sort_order' => 101,
    'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'is_required' => 0,
    'apply_to' => array('customercredit'),
    'is_configurable' => 1,
    'is_searchable' => 0,
    'is_visible_in_advanced_search' => 0,
    'is_comparable' => 0,
    'is_filterable' => 0,
    'is_filterable_in_search' => 1,
    'is_used_for_promo_rules' => 1,
    'is_html_allowed_on_front' => 0,
    'is_visible_on_front' => 0,
    'used_in_product_listing' => 1,
    'used_for_sort_by' => 0,
);

/**
 * add store credit from, to attribute for Store Credit type range
 */
$attr['type'] = 'decimal';
$attr['input'] = 'price';
$attr['is_required'] = 1;
$attr['label'] = 'Minimum Store Credit value';
$attr['position'] = 4;
$attr['sort_order'] = 101;
$attr['class'] = 'validate-number';
$setup->addAttribute('catalog_product', 'storecredit_from', $attr);
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($setup->getAttributeId('catalog_product', 'storecredit_from'));
$attribute->addData($attr)->save();
$attr['type'] = 'decimal';
$attr['input'] = 'price';
$attr['label'] = 'Maximum Store Credit value';
$attr['position'] = 5;
$attr['sort_order'] = 101;
$attr['class'] = 'validate-number';
$setup->addAttribute('catalog_product', 'storecredit_to', $attr);
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($setup->getAttributeId('catalog_product', 'storecredit_to'));
$attribute->addData($attr)->save();
/**
 * add store credit value attribute for Store Credit type dropdown
 */
$attr['type'] = 'varchar';
$attr['input'] = 'text';
$attr['label'] = 'Store Credit values';
$attr['position'] = 6;
$attr['sort_order'] = 101;
$attr['backend_type'] = 'text';
$attr['class'] = '';
$attr['note'] = Mage::helper('customercredit')->__('Seperated by comma, e.g. 10,20,30');
$setup->addAttribute('catalog_product', 'storecredit_dropdown', $attr);
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($setup->getAttributeId('catalog_product', 'storecredit_dropdown'));
$attribute->addData($attr)->save();

/**
 * add type of Store Credit value
 */
$att_storecredittype = array(
    'group' => 'Prices',
    'type' => 'int',
    'input' => 'select',
    'label' => 'Type of Store Credit value',
    'backend' => '',
    'frontend' => '',
    'source' => 'customercredit/storecredittype',
    'visible' => 1,
    'user_defined' => 1,
    'used_for_price_rules' => 1,
    'position' => 2,
    'unique' => 0,
    'default' => '',
    'sort_order' => 100,
    'apply_to' => array('customercredit'),
);
$setup->addAttribute('catalog_product', 'storecredit_type', $att_storecredittype);
$storecreditType = Mage::getModel('catalog/resource_eav_attribute')->load($setup->getAttributeId('catalog_product', 'storecredit_type'));
$storecreditType->addData(array(
    'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'is_required' => 1,
    'apply_to' => array('customercredit'),
    'is_configurable' => 1,
    'is_searchable' => 0,
    'is_visible_in_advanced_search' => 0,
    'is_comparable' => 0,
    'is_filterable' => 0,
    'is_filterable_in_search' => 1,
    'is_used_for_promo_rules' => 1,
    'is_html_allowed_on_front' => 0,
    'is_visible_on_front' => 0,
    'used_in_product_listing' => 1,
    'used_for_sort_by' => 0,
))->save();

$installer->endSetup();
