<?php

class Magestore_Membership_Block_Adminhtml_Member_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('member_form', array('legend'=>Mage::helper('membership')->__('Member information')));
     
      $fieldset->addField('member_name', 'text', array(
          'label'     => Mage::helper('membership')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'member_name',
      ));
	  
	  $id_member = $this->getRequest()->getParam('id');
	  $link = '';
	  if ($id_member){
		$customer_id = Mage::getModel('membership/member')->load($id_member)->getCustomerId();
		$url = $this->getUrl('adminhtml/customer/edit', array('id' => $customer_id));
		$link = '<a href="'.$url.'">'. Mage::helper('membership')->__('View Customer') .'</a>';
	  }
	  
	  $fieldset->addField('member_email', 'text', array(
          'label'     => Mage::helper('membership')->__('Email'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'member_email',
		  'note'	  => $link
      ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('membership')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('membership')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('membership')->__('Disabled'),
              ),
          ),
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getMemberData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getMemberData());
          Mage::getSingleton('adminhtml/session')->setMemberData(null);
      } elseif ( Mage::registry('member_data') ) {
          $form->setValues(Mage::registry('member_data')->getData());
      }
      return parent::_prepareForm();
  }
}