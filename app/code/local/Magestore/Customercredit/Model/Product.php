<?php

class Magestore_Customercredit_Model_Product extends Mage_Rule_Model_Rule
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('customercredit/product');
    }

    public function loadByProduct($product)
    {
        if (is_object($product)) {
            if ($product->getId()) {
                return $this->load($product->getId(), 'product_id');
            }
            return $this;
        }
        if ($product) {
            return $this->load($product, 'product_id');
        }
        return $this;
    }

}
