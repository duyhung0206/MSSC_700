<?php

class Magestore_Membership_Model_Mysql4_Productbuyexchange extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('membership/productbuyexchange', 'productbuyinmembership_id');
    }
}