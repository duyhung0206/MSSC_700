<?php
class Magestore_Membership_Block_Adminhtml_Group extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_group';
    $this->_blockGroup = 'membership';
    $this->_headerText = Mage::helper('membership')->__('Group Manager');
    $this->_addButtonLabel = Mage::helper('membership')->__('Add Group');
    parent::__construct();
  }
}