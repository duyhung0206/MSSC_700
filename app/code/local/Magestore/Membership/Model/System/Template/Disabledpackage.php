<?php

class Magestore_Membership_Model_System_Template_Disabledpackage
{
    public function toOptionArray()
    {
        if(!$collection = Mage::registry('config_system_email_template')) {
            $collection = Mage::getResourceModel('core/email_template_collection')
                ->load();

            Mage::register('config_system_email_template', $collection);
        }

        $options = $collection->toOptionArray();
        
        array_unshift(
            $options,
            array(
                'value'=> 'magestore_membership_disabled_package',
                'label' => 'Notice disabled package email template (Default)'
            )
        );		
		
		return $options;
    }
}