<?php

class Magestore_Membership_Model_Package_Discounttype extends Mage_Core_Model_Abstract {

    const TYPE_FIXED = 1;
    const TYPE_PERCENT = 2;
    const TYPE_PRODUCT = 3;

    public static function getOptionHash() {
        return array(
            array(
                'value' => self::TYPE_FIXED,
                'label' => Mage::helper('membership')->__('Fixed Amount'),
            ),
            array(
                'value' => self::TYPE_PERCENT,
                'label' => Mage::helper('membership')->__('Percentage'),
            ), array(
                'value' => self::TYPE_PRODUCT,
                'label' => Mage::helper('membership')->__('Fixed Discount Price'),
            )
        );
    }

    public static function getOptions() {
        return array(
            self::TYPE_FIXED => Mage::helper('membership')->__('Fixed Amount'),
            self::TYPE_PERCENT => Mage::helper('membership')->__('Percentage'),
            self::TYPE_PRODUCT => Mage::helper('membership')->__('Fixed Discount Price')
        );
    }

}
