<?php
class MageStore_Membership_Model_Packagevalue extends Mage_Core_Model_Abstract{
      public function _construct()
    {
        parent::_construct();
        $this->_init('membership/packagevalue');
    }
    
    public function loadAttributeValue($packageId, $storeId, $attributeCode) {
        $attributeValue = $this->getCollection()
    		->addFieldToFilter('package_id', $packageId)
    		->addFieldToFilter('store_id', $storeId)
    		->addFieldToFilter('attribute_code',$attributeCode)
    		->getFirstItem();
		$this->setData('package_id', $packageId)
			->setData('store_id',$storeId)
			->setData('attribute_code',$attributeCode);
    	if ($attributeValue)
    		$this->addData($attributeValue->getData())
    			->setId($attributeValue->getId());
		return $this;
    }
}
?>
