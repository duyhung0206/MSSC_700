<?php
class Magestore_Membership_Block_Adminhtml_Memberpackage extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
	 parent::__construct();
    $this->_controller = 'adminhtml_Memberpackage';
    $this->_blockGroup = 'membership';
    $this->_headerText = Mage::helper('membership')->__('Member Package Manager');
    
	$this->_removeButton('add', 'label');
   
  }
}