<?php

class Magestore_Customercredit_Helper_Creditproduct extends Mage_Core_Helper_Data
{
    
    public function getCreditValue($product)
    {
        $credit_type = $product->getStorecreditType();
		$currentStore = Mage::app()->getStore()->getStoreId();
		
		// convert Credit value from the old version
		if (!$credit_type) {
			$amountStr = $product->getCreditAmount();
			$amountStr = trim(str_replace(array(' ', "\r", "\t"), '', $amountStr));
			$creditAmount = Mage::helper('customercredit')->getCreditAmount($amountStr);
			
			Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
			if ($creditAmount['type'] == 'range')
			{	
				$product->setStorecreditFrom($creditAmount['from'])
						->setStorecreditTo($creditAmount['to'])
						->setStorecreditType(Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_RANGE)
						->save();
				$credit_type = Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_RANGE;		
			}	
			
			if ($creditAmount['type'] == 'dropdown')
			{			
				$product->setStorecreditDropdown($amountStr)
						->setStorecreditType(Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_DROPDOWN)
						->save();
				$credit_type = Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_DROPDOWN;		
				
			}	

			if ($creditAmount['type'] == 'static')
			{
				$product->setStorecreditValue($creditAmount['value'])
						->setStorecreditType(Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_FIX)
						->save();
				$credit_type = Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_FIX;		
			}	
				
				Mage::app()->setCurrentStore($currentStore);
			
		}	
		
        switch ($credit_type) {
            case Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_FIX:
                return array('type' => 'static', 'credit_price' => $product->getStorecreditValue() * $product->getCreditRate(), 'value' => $product->getStorecreditValue());

            case Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_RANGE:
                $data = array('type' => 'range', 'from' => $product->getStorecreditFrom(), 'to' => $product->getStorecreditTo(), 'storecredit_rate' => $product->getCreditRate());
                return $data;

            case Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_DROPDOWN:
                $options = explode(',', $product->getStorecreditDropdown());
                foreach ($options as $key => $option) {
                    if (!is_numeric($option) || $option <= 0) {
                        unset($options[$key]);
                    }
                }
                $data = array('type' => 'dropdown', 'options' => $options);
                foreach ($options as $value) {
                    $data['prices'][] = $value * $product->getCreditRate();
                }
                return $data;

            default:
                $creditAmount = Mage::helper('customercredit')->getGeneralConfig('amount');
                $options = explode(',', $creditAmount);
                return array('type' => 'dropdown', 'options' => $options, 'prices' => $options);
        }
    }

}
