<?php

class Magestore_Membership_Model_Groupproduct extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('membership/groupproduct');
    }
	
	public function loadGroupProduct($groupId, $productId) {
		$collection = $this->getCollection()
			->addFieldToFilter('group_id', $groupId)
			->addFieldToFilter('product_id', $productId)
			;
			
		$item = $collection->getFirstItem();
		$this->setData($item->getData());
		return $this;
	}
}