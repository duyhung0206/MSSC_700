<?php
$installer = $this;
$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
/* Add credit value attribute */
$data = array(
    'type' => 'decimal',
    'input' => 'text',
    'label' => 'Block account',
    'backend' => '',
    'frontend' => '',
    'source' => '',
    'is_visible' => 0,
    'is_visible_on_front' => 0,
    'required' => 0,
    'user_defined' => 0,
    'is_searchable' => 1,
    'is_filterable' => 0,
    'is_comparable' => 0,
    'position' => 2,
    'unique' => 0,
    'default' => 0.00,
    'is_global' => ''
);
$setup->addAttribute('customer', 'block_account', $data);
$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'block_account');
$attribute->setDefaultValue('0.00');
$attribute->save();
$installer->endSetup();

?>