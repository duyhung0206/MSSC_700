<?php

class Magestore_Customercredit_Model_Aftertax extends Varien_Object {

    static public function getOptionArray() {
        return array(
            0 => Mage::helper('customercredit')->__('Before tax'),
            1 => Mage::helper('customercredit')->__('After tax'),
        );
    }

    public function toOptionArray() {
        $options = array();
        foreach (self::getOptionArray() as $value => $label)
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        return $options;
    }

}