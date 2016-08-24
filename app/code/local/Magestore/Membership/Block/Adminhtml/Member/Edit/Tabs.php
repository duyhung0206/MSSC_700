<?php

class Magestore_Membership_Block_Adminhtml_Member_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('member_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('membership')->__('Member Information'));
    }

    protected function _beforeToHtml() {
//        $this->addTab('form_section', array(
//            'label' => Mage::helper('membership')->__('Member Information'),
//            'title' => Mage::helper('membership')->__('Member Information'),
//            'content' => $this->getLayout()->createBlock('membership/adminhtml_member_edit_tab_form')->toHtml(),
//        ));
        
        $this->addTab('customer_section', array(
            'label' => Mage::helper('membership')->__('Member Information'),
            'title' => Mage::helper('membership')->__('Member Information'),
            'content' => $this->getLayout()->createBlock('membership/adminhtml_member_edit_tab_customergrid')->toHtml(),
        ));

        $this->addTab('package_section', array(
            'label' => Mage::helper('membership')->__('View Packages'),
            'title' => Mage::helper('membership')->__('View Packages'),
            'url' => $this->getUrl('*/*/package', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
            'class' => 'ajax',
        ));
        return parent::_beforeToHtml();
    }

}
