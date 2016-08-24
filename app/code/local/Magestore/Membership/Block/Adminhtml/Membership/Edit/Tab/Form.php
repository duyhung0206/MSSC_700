<?php

class Magestore_Membership_Block_Adminhtml_Membership_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('membership_form', array('legend'=>Mage::helper('membership')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('membership')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('membership')->__('File'),
          'required'  => false,
          'name'      => 'filename',
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
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('membership')->__('Content'),
          'title'     => Mage::helper('membership')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getMembershipData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getMembershipData());
          Mage::getSingleton('adminhtml/session')->setMembershipData(null);
      } elseif ( Mage::registry('membership_data') ) {
          $form->setValues(Mage::registry('membership_data')->getData());
      }
      return parent::_prepareForm();
  }
}