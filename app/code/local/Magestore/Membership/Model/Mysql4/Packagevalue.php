<?php
    class Magestore_Membership_Model_Mysql4_Packagevalue extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('membership/packagevalue', 'value_id');
    }
}
?>
