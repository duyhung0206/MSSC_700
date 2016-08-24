<?php

class Magestore_Membership_Block_Adminhtml_Member_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'membership';
        $this->_controller = 'adminhtml_member';
        
        $this->_updateButton('save', 'label', Mage::helper('membership')->__('Save Member'));
        $this->_updateButton('delete', 'label', Mage::helper('membership')->__('Delete Member'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('member_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'member_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'member_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('member_data') && Mage::registry('member_data')->getId() ) {
            return Mage::helper('membership')->__("Edit Member '%s'", $this->htmlEscape(Mage::registry('member_data')->getMemberName()));
        } else {
            return Mage::helper('membership')->__('Add Member');
        }
    }
}