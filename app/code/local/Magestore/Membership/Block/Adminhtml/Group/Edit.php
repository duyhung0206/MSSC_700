<?php

class Magestore_Membership_Block_Adminhtml_Group_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'membership';
        $this->_controller = 'adminhtml_group';
        
        $this->_updateButton('save', 'label', Mage::helper('membership')->__('Save Group'));
        $this->_updateButton('delete', 'label', Mage::helper('membership')->__('Delete Group'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('group_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'group_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'group_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('group_data') && Mage::registry('group_data')->getId() ) {
            return Mage::helper('membership')->__("Edit Group '%s'", $this->htmlEscape(Mage::registry('group_data')->getGroupName()));
        } else {
            return Mage::helper('membership')->__('Add Group');
        }
    }
}