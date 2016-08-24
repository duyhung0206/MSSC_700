<?php
class Magestore_Membership_Block_Adminhtml_Member extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_member';
    $this->_blockGroup = 'membership';
    $this->_headerText = Mage::helper('membership')->__('Member Manager');
    $this->_addButtonLabel = Mage::helper('membership')->__('Add Member');
    parent::__construct();
  }
}