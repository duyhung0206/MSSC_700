<?php

class Magestore_Membership_Model_Mysql4_Paymenthistory_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('membership/paymenthistory');
    }
	
	/*public function getGrid($memberId){
		$collection = $this->addFieldToFilter('member_id', $memberId)
						->getSelect()
						->joinLeft(array('package' =>$this->getTable('membership/package')),
            		'package.package_id = main_table.package_id', array('package_name'=>'package_name', 'package_price'=>'package_price', 'duration'=>'duration'));
						
		return $this;
	}*/
}