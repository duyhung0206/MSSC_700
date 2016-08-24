<?php

class Magestore_Membership_Model_System_Template_Newpackage
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
                'value'=> 'magestore_membership_new_package',
                'label' => 'New package email template (Default)'
            )
        );		
		
		return $options;
    }
}