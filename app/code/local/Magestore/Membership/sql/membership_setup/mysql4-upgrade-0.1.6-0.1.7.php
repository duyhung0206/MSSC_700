<?php
$installer = $this;
$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
/* Add credit Block account attribute */
$data = array(
    'type' => 'int',
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
    'default' => 0,
    'is_global' => '',
);
$setup->addAttribute('customer', 'block_account', $data);
$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'block_account');
$attribute->setDefaultValue('0');
$attribute->save();

/* Add credit Message block attribute */
$data2 = array(
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
$setup->addAttribute('customer', 'message_block', $data2);
$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'message_block');
$attribute->setDefaultValue("");
$attribute->save();

$customers = Mage::getModel('customer/customer')->getCollection();
foreach ($customers as $customer){
    $customer->setBlockAccount(0)
        ->setMessageBlock("")
        ->save();
}
$installer->endSetup();

?>