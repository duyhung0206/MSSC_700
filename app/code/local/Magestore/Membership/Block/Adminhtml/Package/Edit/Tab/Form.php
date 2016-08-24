<?php

class Magestore_Membership_Block_Adminhtml_Package_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();

        $dataObj = new Varien_Object(array(
            'package_name',
            'description',
            'package_status'
        ));

        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getStoreData()) {
            $data = Mage::getSingleton('adminhtml/session')->getStoreData();
            Mage::getSingleton('adminhtml/session')->setStoreData(null);
        } elseif (Mage::registry('package_data')) {
            $data = Mage::registry('package_data')->getData();
        }
        if (isset($data))
            $dataObj->addData($data);
        $data = $dataObj->getData();
        $this->setForm($form);

        $inStore = $this->getRequest()->getParam('store');
        $defaultLabel = Mage::helper('membership')->__('Use Default');
        $defaultTitle = Mage::helper('membership')->__('-- Please Select --');
        $scopeLabel = Mage::helper('membership')->__('STORE VIEW');
        $websiteLabel = Mage::helper('membership')->__('WEBSITE');

        $fieldset = $form->addFieldset('membership_form', array('legend' => Mage::helper('membership')->__('General information')));
        $fieldset->addField('package_name', 'text', array(
            'label' => Mage::helper('membership')->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'package_name',
            'disabled' => ($inStore && !$data['package_name_in_store']),
            'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="package_name_default" name="package_name_default" type="checkbox" value="1" class="checkbox config-inherit" ' . ($data['package_name_in_store'] ? '' : 'checked="checked"') . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="package_name_default" class="inherit" title="' . $defaultTitle . '">' . $defaultLabel . '</label>
          </td><td class="scope-label">
			[' . $scopeLabel . ']
          ' : '</td><td class="scope-label">
			[' . $scopeLabel . ']',
        ));

        $fieldset->addField('package_price', 'text', array(
            'label' => Mage::helper('membership')->__('Package Price'),
            'required' => true,
            'name' => 'package_price',
        ));
        $fieldset->addField('discount_type', 'select', array(
            'label' => Mage::helper('membership')->__('Discount Type'),
            'name' => 'discount_type',
            'required' => true,
            'values' => Magestore_Membership_Model_Package_Discounttype::getOptionHash()
        ));
        $fieldset->addField('package_product_price', 'text', array(
            'label' => Mage::helper('membership')->__('Discount Value'),
            'name' => 'package_product_price',
            'class' => 'required-entry',
            'required' => true,
            'note'  => Mage::helper('membership')->__('If promotion tye is percentage and promotion value is 5, each product in this package will be sale off 5%')
        ));
        
        

        $fieldset->addField('custom_option_discount', 'select', array(
            'label' => Mage::helper('membership')->__('Custom Option Discount'),
            'name' => 'custom_option_discount',
            'required' => true,
            'values' => array(
                array(
                    'value' => 'yes',
                    'label' => Mage::helper('membership')->__('Yes'),
                ),
                array(
                    'value' => 'no',
                    'label' => Mage::helper('membership')->__('No'),
                ),
            ),
            'note'  => Mage::helper('membership')->__('For products with custom options')
        ));
        
        $fieldset->addField('unit_of_time', 'select', array(
            'label' => Mage::helper('membership')->__('Duration Period'),
            'name' => 'unit_of_time',
            'required' => true,
            'values' => Magestore_Membership_Model_Package_Unitoftime::getOptionHash()
        ));

        //$dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('duration', 'text', array(
            'label' => Mage::helper('membership')->__('Duration Value'),
            'required' => true,
            'name' => 'duration',
            'note' => Mage::helper('membership')->__('If duration period is month and duration value is 3, this package will be expired in 3 months.'),
        ));
        
        $fieldset->addField('url_key', 'text', array(
            'label' => Mage::helper('membership')->__('Url Key'),
            'name' => 'url_key',
        ));

        $fieldset->addField('description', 'editor', array(
            'name' => 'description',
            'label' => Mage::helper('membership')->__('Description'),
            'wysiwyg' => false,
            'required' => false,
            'disabled' => ($inStore && !$data['description_in_store']),
            'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="description_default" name="description_default" type="checkbox" value="1" class="checkbox config-inherit" ' . ($data['description_in_store'] ? '' : 'checked="checked"') . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="description_default" class="inherit" title="' . $defaultTitle . '">' . $defaultLabel . '</label>
          </td><td class="scope-label">
			[' . $scopeLabel . ']
          ' : '</td><td class="scope-label">
			[' . $scopeLabel . ']',
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => Mage::helper('membership')->__('Sort Order'),
            //'required' => true,
        ));

        $fieldset->addField('package_status', 'select', array(
            'label' => Mage::helper('membership')->__('Status'),
            'name' => 'package_status',
            //'required' => true,
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
            'disabled' => ($inStore && !$data['package_status_in_store']),
            'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="package_status_default" name="package_status_default" type="checkbox" value="1" class="checkbox config-inherit" ' . ($data['package_status_in_store'] ? '' : 'checked="checked"') . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="package_status_default" class="inherit" title="' . $defaultTitle . '">' . $defaultLabel . '</label>
          </td><td class="scope-label">
			[' . $websiteLabel . ']
          ' : '</td><td class="scope-label">
			[' . $websiteLabel . ']',
        ));


        if (Mage::getSingleton('adminhtml/session')->getPackageData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getPackageData());
            Mage::getSingleton('adminhtml/session')->setPackageData(null);
        } elseif (Mage::registry('package_data')) {
            $form->setValues(Mage::registry('package_data')->getData());
        }
        return parent::_prepareForm();
    }

}
