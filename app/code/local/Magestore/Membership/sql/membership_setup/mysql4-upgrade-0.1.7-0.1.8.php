<?php
$installer = $this;
$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
/* Add credit value attribute */
$data = array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Message block',
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
    'default' => "",
    'is_global' => '',
);
$setup->addAttribute('customer', 'message_block', $data);
$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'message_block');
$attribute->setDefaultValue("");
$attribute->save();
$installer->endSetup();

?>