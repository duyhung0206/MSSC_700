<?php
class Magestore_Membership_Model_Packageproduct extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('membership/packageproduct');
    }
	
	public function loadPackageProduct($packageId, $productId) {
		$collection = $this->getCollection()
			->addFieldToFilter('package_id', $packageId)
			->addFieldToFilter('product_id', $productId)
			;
			
		$item = $collection->getFirstItem();
		$this->setData($item->getData());
		return $this;
	}
}