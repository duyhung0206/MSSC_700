<?php
class Magestore_Membership_Block_Adminhtml_Membership extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_membership';
    $this->_blockGroup = 'membership';
    $this->_headerText = Mage::helper('membership')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('membership')->__('Add Item');
    parent::__construct();
  }
}