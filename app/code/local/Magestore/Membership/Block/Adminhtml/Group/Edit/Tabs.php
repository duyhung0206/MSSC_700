<?php

class Magestore_Membership_Block_Adminhtml_Group_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('group_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('membership')->__('Group Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('membership')->__('Group Information'),
          'title'     => Mage::helper('membership')->__('Group Information'),
          'content'   => $this->getLayout()->createBlock('membership/adminhtml_group_edit_tab_form')->toHtml(),
      ));
      
	  $this->addTab('product_section', array(
          'label'     => Mage::helper('membership')->__('Manage Products'),
          'title'     => Mage::helper('membership')->__('Manage Products'),
		  'url'		  => $this->getUrl('*/*/products',array('_current'=>true,'id'=>$this->getRequest()->getParam('id'))),
		  'class'     => 'ajax',      
	  ));
	  
      return parent::_beforeToHtml();
  }
}