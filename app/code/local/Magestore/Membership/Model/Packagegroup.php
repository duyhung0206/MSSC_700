<?php

class Magestore_Membership_Model_Packagegroup extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('membership/packagegroup');
    }
	
	public function loadPackageGroup($packageId, $groupId) {
		$collection = $this->getCollection()
			->addFieldToFilter('package_id', $packageId)
			->addFieldToFilter('group_id', $groupId)
			;
			
		$item = $collection->getFirstItem();
		$this->setData($item->getData());
		return $this;
	}
}