<?php
class Magestore_Membership_Block_Adminhtml_Package extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_package';
    $this->_blockGroup = 'membership';
    $this->_headerText = Mage::helper('membership')->__('Package Manager');
    $this->_addButtonLabel = Mage::helper('membership')->__('Add Package');
    parent::__construct();
  }
}