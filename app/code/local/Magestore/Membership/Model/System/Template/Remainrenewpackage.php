<?php

class Magestore_Membership_Model_System_Template_Remainrenewpackage
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
                'value'=> 'magestore_membership_remain_renew_package',
                'label' => 'Renew package email template (Default)'
            )
        );		
		
		return $options;
    }
}
