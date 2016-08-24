<?php

class Magestore_Membership_Block_Adminhtml_Group_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('group_form', array('legend' => Mage::helper('membership')->__('Group information')));

        $fieldset->addField('group_name', 'text', array(
            'label' => Mage::helper('membership')->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'group_name',
        ));

//        $fieldset->addField('priority', 'text', array(
//            'label' => Mage::helper('membership')->__('Priority'),
//            'class' => 'required-entry',
//            'required' => true,
//            'name' => 'priority',
//        ));

        $fieldset->addField('description', 'editor', array(
            'name' => 'description',
            'label' => Mage::helper('membership')->__('Description'),
            'wysiwyg' => false,
        ));

//        $fieldset->addField('group_product_price', 'text', array(
//            'label' => Mage::helper('membership')->__('Product Price'),
//            'name' => 'group_product_price',
//            'class' => 'required-entry',
//            'required' => true,
//        ));

        $fieldset->addField('group_status', 'select', array(
            'label' => Mage::helper('membership')->__('Status'),
            'name' => 'group_status',
            'required' => true,
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('membership')->__('Enabled'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('membership')->__('Disabled'),
                ),
            ),
        ));



        if (Mage::getSingleton('adminhtml/session')->getGroupData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getGroupData());
            Mage::getSingleton('adminhtml/session')->setGroupData(null);
        } elseif (Mage::registry('group_data')) {
            $form->setValues(Mage::registry('group_data')->getData());
        }
        return parent::_prepareForm();
    }

}
