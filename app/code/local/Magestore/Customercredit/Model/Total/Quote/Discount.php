<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Customercredit Model
 * 
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @author      Magestore Developer
 */
class Magestore_Customercredit_Model_Total_Quote_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract {
    
    public function __construct() {
        $this->setCode('customercredit_after_tax');
    }
    
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        parent::collect($address);
        $quote = $address->getQuote();
        $items = $address->getAllItems();
        $session = Mage::getSingleton('checkout/session');
        
        if (!count($items))
            return $this;
        if (Mage::getStoreConfig('customercredit/spend/tax', $quote->getStoreId()) == '0') {
            return $this;
        }

        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!Mage::helper('customercredit/account')->customerGroupCheck()) {
            $session->setBaseCustomerCreditAmount(0);
            $session->setBaseCustomerCreditAmountPaypal(0);
            return $this;
        }
        $helper = Mage::helper('customercredit');
        
        $creditAmountEntered = $session->getBaseCustomerCreditAmount();
        if(!$creditAmountEntered)
            return $this;
        
        $baseDiscountTotal = 0;
        $baseCustomercreditDiscount = 0;
        $baseItemsPrice = 0;
        $baseCustomercreditForShipping = 0;
        
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'customercredit') {
                        $itemDiscount = $child->getBaseRowTotal() + $child->getBaseTaxAmount() - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                        $baseDiscountTotal += $itemDiscount;
                    }
                }
            } else if ($item->getProduct()) {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'customercredit') {
                    $itemDiscount = $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                    $baseDiscountTotal += $itemDiscount;
                }
            }
        }
        $baseItemsPrice = $baseDiscountTotal;
        if ($helper->getSpendConfig('shipping')) {
            $shippingDiscount = $address->getBaseShippingAmount() + $address->getBaseShippingTaxAmount() - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
            $baseDiscountTotal += $shippingDiscount;
        }
        
        $customercreditBalance = Mage::getModel('customercredit/customercredit')->getBaseCustomerCredit();
        
        $baseCustomercreditDiscount = min($creditAmountEntered, $baseDiscountTotal, $customercreditBalance);
        $customercreditDiscount = Mage::getModel('customercredit/customercredit')
                ->getConvertedFromBaseCustomerCredit($baseCustomercreditDiscount);
        
        if ($baseCustomercreditDiscount < $baseItemsPrice)
            $rate = $baseCustomercreditDiscount / $baseItemsPrice;
        else {
            $rate = 1;
            $baseCustomercreditForShipping = $baseCustomercreditDiscount - $baseItemsPrice;
        }
        //update session
        $session->setBaseCustomerCreditAmount($baseCustomercreditDiscount);
        
        //update address
        $address->setGrandTotal($address->getGrandTotal() - $customercreditDiscount);
        $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseCustomercreditDiscount);
        $address->setCustomercreditDiscount($customercreditDiscount);
        $address->setBaseCustomercreditDiscount($baseCustomercreditDiscount);
        
        //distribute discount
        $this->_prepareDiscountCreditForAmount($address, $rate, $baseCustomercreditForShipping);
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $quote = $address->getQuote();
        if (Mage::getStoreConfig('customercredit/spend/tax', $quote->getStoreId()) == 0) {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getData('address_type') == 'billing')
            return $this;
        $session = Mage::getSingleton('checkout/session');
        $customer_credit_discount = $address->getCustomercreditDiscount();
        if ($session->getBaseCustomerCreditAmount())
            $customer_credit_discount = $session->getBaseCustomerCreditAmount();
        if ($customer_credit_discount > 0) {
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => Mage::helper('customercredit')->__('Customer Credit'),
                'value' => -Mage::helper('core')->currency($customer_credit_discount,false,false)
            ));
        }

        return $this;
    }

    public function _prepareDiscountCreditForAmount(Mage_Sales_Model_Quote_Address $address, $rate, $baseCustomercreditForShipping) {
        // Update discount for each item
        $helper = Mage::helper('customercredit');
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if(!$child->isDeleted() && $child->getProduct()->getTypeId() != 'customercredit') {
                        $baseItemPrice = $child->getBaseRowTotal() + $child->getBaseTaxAmount() - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                        $itemBaseDiscount = $baseItemPrice * $rate;
                        $itemDiscount = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
                        $child->setMagestoreBaseDiscount($child->getMagestoreBaseDiscount() + $itemBaseDiscount);
                        $child->setBaseCustomercreditDiscount($itemBaseDiscount)
                                ->setCustomercreditDiscount($itemDiscount);
                    }
                }
            } else if ($item->getProduct()) {
                if(!$item->isDeleted() && $item->getProduct()->getTypeId() != 'customercredit') {
                    $baseItemPrice = $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                    $itemBaseDiscount = $baseItemPrice * $rate;
                    $itemDiscount = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
                    $item->setMagestoreBaseDiscount($item->getMagestoreBaseDiscount() + $itemBaseDiscount);
                    $item->setBaseCustomercreditDiscount($itemBaseDiscount)
                            ->setCustomercreditDiscount($itemDiscount);
                }
            }
        }
        if ($helper->getSpendConfig('shipping') && $baseCustomercreditForShipping) {
            $baseShippingPrice = $address->getBaseShippingAmount() + $address->getBaseShippingTaxAmount() - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
            $baseShippingDiscount = min($baseShippingPrice, $baseCustomercreditForShipping);
            $shippingDiscount = Mage::app()->getStore()->convertPrice($baseShippingDiscount);
            $address->setMagestoreBaseDiscountForShipping($address->getMagestoreBaseDiscountForShipping() + $baseShippingDiscount);
            $address->setBaseCustomercreditDiscountForShipping($baseShippingDiscount);
            $address->setCustomercreditDiscountForShipping($shippingDiscount);
        }
        return $this;
    }
}
