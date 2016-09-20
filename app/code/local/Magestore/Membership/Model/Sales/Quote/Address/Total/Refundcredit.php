<?php

class Magestore_Membership_Model_Sales_Quote_Address_Total_Refundcredit extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_code = 'refund_credit';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
        $address->setGrandTotal($address->getGrandTotal());
        $address->setBaseGrandTotal($address->getBaseGrandTotal());


    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amt = $address->getRefundcreditAmount();
        if ($amt > 0) {
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => Mage::helper('membership')->__('Refund credit'),
                'value' => $amt
            ));
        }


        return $this;
    }
}