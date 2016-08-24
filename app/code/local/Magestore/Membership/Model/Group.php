<?php

class Magestore_Membership_Model_Group extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('membership/group');
    }
	
	public function getProductIds(){
		$productIds = array();
		//get from group/product
		$collection = Mage::getModel('membership/groupproduct')->getCollection()
			->addFieldToFilter('group_id', $this->getId())
			;
		
		if(count($collection)) {
			foreach($collection as $item) {
				$productIds[] = $item->getProductId();
			}
		}
		return $productIds;
	}
}