<?php

class Magestore_Membership_Block_Adminhtml_Package_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('membership_form', array('legend'=>Mage::helper('membership')->__('Item information')));
     
      $fieldset->addField('package_name', 'text', array(
          'label'     => Mage::helper('membership')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'package_name',
      ));

      $fieldset->addField('package_price', 'text', array(
          'label'     => Mage::helper('membership')->__('Price'),
          'required'  => true,
          'name'      => 'package_price',
	  ));
	  
	  $fieldset->addField('package_product_price', 'text', array(
          'label'     => Mage::helper('membership')->__('Product Price'),
          'name'      => 'package_product_price',
		  'class'     => 'required-entry',
		  'required'  => true,
      ));
	  
	  $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
	  $fieldset->addField('duration', 'text', array(
          'label'     => Mage::helper('membership')->__('Duration'),
          'required'  => true,
          'name'      => 'duration',
		  'note'	  => 'months',
	  ));
	  
	  $fieldset->addField('description', 'editor', array(
          'name'      => 'description',
          'label'     => Mage::helper('membership')->__('Description'),
          'wysiwyg'   => false,
          'required'  => false,
      ));
     
	 $fieldset->addField('sort_order', 'text', array(
          'name'      => 'sort_order',
          'label'     => Mage::helper('membership')->__('Sort Order'),
          'required'  => true,
      ));
	  
      $fieldset->addField('package_status', 'select', array(
          'label'     => Mage::helper('membership')->__('Status'),
          'name'      => 'package_status',
		  'required'  => true,
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
     
      
      if ( Mage::getSingleton('adminhtml/session')->getPackageData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPackageData());
          Mage::getSingleton('adminhtml/session')->setPackageData(null);
      } elseif ( Mage::registry('package_data') ) {
          $form->setValues(Mage::registry('package_data')->getData());
      }
      return parent::_prepareForm();
  }
}