<?php

class Magestore_Membership_Model_Sales_Quote_Address_Total_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_code = 'discount_amount';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }
        $address->setGrandTotal($address->getGrandTotal() - $address->getDiscountexchangeAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() - $address->getBaseDiscountexchangeAmount());

    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amt = $address->getDiscountexchangeAmount();
        if ($amt > 0) {
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => Mage::helper('membership')->__('Discount exchange'),
                'value' => -$amt
            ));
        }


        return $this;
    }
}