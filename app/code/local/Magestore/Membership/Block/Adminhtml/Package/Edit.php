<?php

class Magestore_Membership_Block_Adminhtml_Package_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'membership';
        $this->_controller = 'adminhtml_package';
        
        $this->_updateButton('save', 'label', Mage::helper('membership')->__('Save Package'));
        $this->_updateButton('delete', 'label', Mage::helper('membership')->__('Delete Package'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('package_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'package_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'package_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('package_data') && Mage::registry('package_data')->getId() ) {
            return Mage::helper('membership')->__("Edit Package '%s'", $this->htmlEscape(Mage::registry('package_data')->getPackageName()));
        } else {
            return Mage::helper('membership')->__('Add Package');
        }
    }
}