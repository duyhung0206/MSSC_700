<?php

class Magestore_Membership_Block_Adminhtml_Package_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('package_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('membership')->__('Package Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('membership')->__('Package Information'),
          'title'     => Mage::helper('membership')->__('Package Information'),
          'content'   => $this->getLayout()->createBlock('membership/adminhtml_package_edit_tab_form')->toHtml(),
      ));
	  
	  $this->addTab('group_section', array(
          'label'     => Mage::helper('membership')->__('Manage Groups'),
          'title'     => Mage::helper('membership')->__('Manage Groups'),
		  'url'		  => $this->getUrl('*/*/groups',array('_current'=>true,'id'=>$this->getRequest()->getParam('id'))),
		  'class'     => 'ajax',      
	  ));
     
	 $this->addTab('product_section', array(
          'label'     => Mage::helper('membership')->__('Manage Products'),
          'title'     => Mage::helper('membership')->__('Manage Products'),
		  'url'		  => $this->getUrl('*/*/products',array('_current'=>true,'id'=>$this->getRequest()->getParam('id'))),
		  'class'     => 'ajax',
	  ));
	 
	 $this->addTab('member_section', array(
          'label'     => Mage::helper('membership')->__('Members'),
          'title'     => Mage::helper('membership')->__('Members'),
		  'url'		  => $this->getUrl('*/*/members',array('_current'=>true,'id'=>$this->getRequest()->getParam('id'))),
		  'class'     => 'ajax',
	  ));
	 
      return parent::_beforeToHtml();
  }
}