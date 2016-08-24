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
class Magestore_Customercredit_Model_Total_Quote_Discountbeforetax extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    protected $_hiddentBaseDiscount = 0;
    protected $_hiddentDiscount = 0;
    
    public function __construct() {
        $this->setCode('customercredit_before_tax');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address) {
        parent::collect($address);
        $session = Mage::getSingleton('checkout/session');
        $quote = $address->getQuote();
        if (Mage::getStoreConfig('customercredit/spend/tax', $quote->getStoreId()) == '1') {
            return $this;
        }
        $items = $quote->getAllItems();
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
//        if (!$address->getBaseSubtotal()) {
//            $address = $quote->getBillingAddress();
//        }
        
        $helper = Mage::helper('customercredit');

        $creditAmountEntered = $session->getBaseCustomerCreditAmount();
        if($creditAmountEntered < 0.0001)
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
                        if (Mage::helper('tax')->priceIncludesTax())
                            $itemDiscount = $child->getRowTotalInclTax() - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                        else
                            $itemDiscount = $child->getBaseRowTotal() - $child->getBaseDiscountAmount() -$child->getMagestoreBaseDiscount();
                        $baseDiscountTotal += $itemDiscount;
                    }
                }
            } else if ($item->getProduct()) {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'customercredit') {
                    if (Mage::helper('tax')->priceIncludesTax())
                        $itemDiscount = $item->getRowTotalInclTax() - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                    else
                        $itemDiscount = $item->getBaseRowTotal() - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                    $baseDiscountTotal += $itemDiscount;
                }
            }
        }
        $baseItemsPrice = $baseDiscountTotal;
        if ($helper->getSpendConfig('shipping')) {
            if (Mage::helper('tax')->shippingPriceIncludesTax())
                $shippingDiscount = $address->getShippingInclTax() - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
            else
                $shippingDiscount = $address->getBaseShippingAmount() - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
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
        //distribute discount
        $this->_prepareDiscountCreditForAmount($address, $rate, $baseCustomercreditForShipping);
        
        //update session
        $session->setBaseCustomerCreditAmount($baseCustomercreditDiscount);
        
        //update address
        $address->setBaseCustomercreditHiddenTax($this->_hiddentBaseDiscount);
        $address->setCustomercreditHiddenTax($this->_hiddentDiscount);
            
//        $address->setMagestoreBaseDiscount($address->getMagestoreBaseDiscount() + $baseTotalDiscount);

        $address->setGrandTotal($address->getGrandTotal() - $customercreditDiscount + $this->_hiddentBaseDiscount);
        $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseCustomercreditDiscount + $this->_hiddentDiscount);
        $address->setCustomercreditDiscount($customercreditDiscount);
        $address->setBaseCustomercreditDiscount($baseCustomercreditDiscount);
        
        return $this;
    }

    
    
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $quote = $address->getQuote();
        if (Mage::getStoreConfig('customercredit/spend/tax', $quote->getStoreId()) == 1) {
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
                'value' => -$customer_credit_discount,
            ));
        }
        return $this;
    }

    public function _prepareDiscountCreditForAmount(Mage_Sales_Model_Quote_Address $address, $rate, $baseCustomercreditForShipping) {
        // Update discount for each item
        $helper = Mage::helper('customercredit');
        $store = Mage::app()->getStore();
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if(!$child->isDeleted() && $child->getProduct()->getTypeId() != 'customercredit') {
                        if (Mage::helper('tax')->priceIncludesTax())
                            $baseItemPrice = $child->getRowTotalInclTax() - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                        else
                            $baseItemPrice = $child->getBaseRowTotal() - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                        $itemBaseDiscount = $baseItemPrice * $rate;
                        $itemDiscount = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
                        $child->setMagestoreBaseDiscount($child->getMagestoreBaseDiscount() + $itemBaseDiscount);
                        $child->setBaseCustomercreditDiscount($itemBaseDiscount)
                                ->setCustomercreditDiscount($itemDiscount);
                        
                        $baseTaxableAmount = $child->getBaseTaxableAmount();
                        $taxableAmount = $child->getTaxableAmount();
                        $child->setBaseTaxableAmount($baseTaxableAmount - $itemBaseDiscount);
                        $child->setTaxableAmount($taxableAmount - $itemDiscount);
                        
                        if(Mage::helper('tax')->priceIncludesTax()) {
                            $taxRate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                            $baseHiddenTax = $this->round($this->calTax($baseTaxableAmount, $taxRate) - $this->calTax($child->getBaseTaxableAmount(), $taxRate));
                            $hiddenTax = $this->round($this->calTax($taxableAmount, $taxRate) - $this->calTax($child->getTaxableAmount(), $taxRate));
                            
                            $this->_hiddentBaseDiscount += $baseHiddenTax;
                            $this->_hiddentDiscount += $hiddenTax;
                            
                            $child->setBaseCustomercreditHiddenTax($child->getBaseCustomercreditHiddenTax() + $baseHiddenTax);
                            $child->setCustomercreditHiddenTax($child->getCustomercreditHiddenTax() + $hiddenTax);
                        }
                    }
                }
            } else if ($item->getProduct()) {
                if(!$item->isDeleted() && $item->getProduct()->getTypeId() != 'customercredit') {
                    if (Mage::helper('tax')->priceIncludesTax())
                        $baseItemPrice = $item->getRowTotalInclTax() - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                    else
                        $baseItemPrice = $item->getBaseRowTotal() - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                    $itemBaseDiscount = $baseItemPrice * $rate;
                    $itemDiscount = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
                    $item->setMagestoreBaseDiscount($item->getMagestoreBaseDiscount() + $itemBaseDiscount);
                    $item->setBaseCustomercreditDiscount($itemBaseDiscount)
                            ->setCustomercreditDiscount($itemDiscount);
                    
                    $baseTaxableAmount = $item->getBaseTaxableAmount();
                    $taxableAmount = $item->getTaxableAmount();
                    $item->setBaseTaxableAmount($baseTaxableAmount - $itemBaseDiscount);
                    $item->setTaxableAmount($taxableAmount - $itemDiscount);
                    
                    if(Mage::helper('tax')->priceIncludesTax()) {
                        $taxRate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                        $baseHiddenTax = $this->round($this->calTax($baseTaxableAmount, $taxRate) - $this->calTax($item->getBaseTaxableAmount(), $taxRate));
                        $hiddenTax = $this->round($this->calTax($taxableAmount, $taxRate) - $this->calTax($item->getTaxableAmount(), $taxRate));
                        
                        $this->_hiddentBaseDiscount += $baseHiddenTax;
                        $this->_hiddentDiscount += $hiddenTax;
                        $item->setBaseCustomercreditHiddenTax($item->getBaseCustomercreditHiddenTax() + $baseHiddenTax);
                        $item->setCustomercreditHiddenTax($item->getCustomercreditHiddenTax() + $hiddenTax);
                    }
                }
            }
        }
        if ($helper->getSpendConfig('shipping') && $baseCustomercreditForShipping) {
            if (Mage::helper('tax')->shippingPriceIncludesTax())
                $baseShippingPrice = $address->getShippingInclTax() - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
            else
                $baseShippingPrice = $address->getBaseShippingAmount() - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
            $baseShippingDiscount = min($baseCustomercreditForShipping, $baseShippingPrice);
            $shippingDiscount = Mage::app()->getStore()->convertPrice($baseShippingDiscount);
            $address->setMagestoreBaseDiscountForShipping($address->getMagestoreBaseDiscountForShipping() + $baseShippingDiscount);
            $address->setBaseCustomercreditDiscountForShipping($baseShippingDiscount);
            $address->setCustomercreditDiscountForShipping($shippingDiscount);
            
            $baseTaxableShippingAmount = $address->getBaseShippingTaxable();
            $taxableShippingAmount = $address->getShippingTaxable();
            
            $address->setBaseShippingTaxable($baseTaxableShippingAmount - $address->getBaseCustomercreditDiscountForShipping());
            $address->setShippingTaxable($taxableShippingAmount - $address->getCustomercreditDiscountForShipping());
            
            if(Mage::helper('tax')->shippingPriceIncludesTax()) {
                $taxShippingRate = $this->getShipingTaxRate($address, $store);
                $this->_hiddentBaseDiscount += $this->round($this->calTax($baseTaxableShippingAmount, $taxShippingRate) - $this->calTax($address->getBaseShippingTaxable(), $taxShippingRate));
                $this->_hiddentDiscount += $this->round($this->calTax($taxableShippingAmount, $taxShippingRate) - $this->calTax($address->getShippingTaxable(), $taxShippingRate));
                //update address
                $address->setBaseCustomercreditShippingHiddenTax($this->round($this->calTax($baseTaxableShippingAmount, $taxShippingRate) - $this->calTax($address->getBaseShippingTaxable(), $taxShippingRate)));
                $address->setCustomercreditShippingHiddenTax($this->round($this->calTax($taxableShippingAmount, $taxShippingRate) - $this->calTax($address->getShippingTaxable(), $taxShippingRate)));
            }
        }
        return $this;
    }

    //get Rate
    public function getItemRateOnQuote($address, $product, $store) {
        $taxClassId = $product->getTaxClassId();
        if ($taxClassId) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest(
                    $address, $address->getQuote()->getBillingAddress(), $address->getQuote()->getCustomerTaxClassId(), $store
            );
            $rate = Mage::getSingleton('tax/calculation')
                    ->getRate($request->setProductClassId($taxClassId));
            return $rate;
        }
        return 0;
    }
    
    public function calTax($price, $rate) {
        return Mage::getSingleton('tax/calculation')->calcTaxAmount($price, $rate, true, false);
    }
    
    public function getShipingTaxRate($address, $store) {
        $request = Mage::getSingleton('tax/calculation')->getRateRequest(
                $address, $address->getQuote()->getBillingAddress(), $address->getQuote()->getCustomerTaxClassId(), $store
        );
        $request->setProductClassId(Mage::getSingleton('tax/config')->getShippingTaxClass($store));
        $rate = Mage::getSingleton('tax/calculation')->getRate($request);
        return $rate;
    }
    public function round($price) {
        return Mage::getSingleton('tax/calculation')->round($price);
    }
}
