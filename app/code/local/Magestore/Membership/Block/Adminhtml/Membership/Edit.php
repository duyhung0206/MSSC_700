<?php

class Magestore_Membership_Block_Adminhtml_Membership_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'membership';
        $this->_controller = 'adminhtml_membership';
        
        $this->_updateButton('save', 'label', Mage::helper('membership')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('membership')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('membership_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'membership_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'membership_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('membership_data') && Mage::registry('membership_data')->getId() ) {
            return Mage::helper('membership')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('membership_data')->getTitle()));
        } else {
            return Mage::helper('membership')->__('Add Item');
        }
    }
}