<?php

class Magestore_Membership_Block_Adminhtml_Membership_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('membership_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('membership')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('membership')->__('Item Information'),
          'title'     => Mage::helper('membership')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('membership/adminhtml_membership_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}