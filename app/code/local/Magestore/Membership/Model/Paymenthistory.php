<?php

class Magestore_Membership_Model_Paymenthistory extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('membership/paymenthistory');
    }	
}