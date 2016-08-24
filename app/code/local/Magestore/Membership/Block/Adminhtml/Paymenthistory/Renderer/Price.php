<?php 
class Magestore_Membership_Block_Adminhtml_Package_Renderer_Price
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row) 
	{
		$package = Mage::getModel('membership/package')->load($row->getId());
		$productPrice = $package->getPackageProductPrice();
		$pos = strpos($productPrice, '%');
		
		if($pos === false){
			$unit = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(); 
	 		return sprintf('%s%d', $unit, $productPrice);
		}else{
			return sprintf('%s', $productPrice);
		}
	}
}