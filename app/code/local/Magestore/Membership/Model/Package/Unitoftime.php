<?php

class Magestore_Membership_Model_Package_Unitoftime extends Mage_Core_Model_Abstract {

    const UNIT_DAY = 'day';
    const UNIT_WEEK = 'week';
    const UNIT_MONTH = 'month';
    const UNIT_YEAR = 'year';

    public static function getOptionHash() {
        return array(
            array(
                'value' => self::UNIT_DAY,
                'label' => Mage::helper('membership')->__('Day'),
            ),
            array(
                'value' => self::UNIT_WEEK,
                'label' => Mage::helper('membership')->__('Week'),
            ), array(
                'value' => self::UNIT_MONTH,
                'label' => Mage::helper('membership')->__('Month'),
            ), array(
                'value' => self::UNIT_YEAR,
                'label' => Mage::helper('membership')->__('Year'),
            )
        );
    }

    public static function getOptions() {
        return array(
            self::UNIT_DAY => Mage::helper('membership')->__('Day'),
            self::UNIT_WEEK => Mage::helper('membership')->__('Week'),
            self::UNIT_MONTH => Mage::helper('membership')->__('Month'),
            self::UNIT_YEAR => Mage::helper('membership')->__('Year')
        );
    }

}
