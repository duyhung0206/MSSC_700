<?php

class Magestore_Membership_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('membership')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('membership')->__('Disabled')
        );
    }
}