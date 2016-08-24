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
 * Customercredit Block
 * 
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @author      Magestore Developer
 */
class Magestore_Customercredit_Block_Product_View extends Mage_Catalog_Block_Product_View_Abstract
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    public function getCreditAmount($product)
    {
        $creditValue = Mage::helper('customercredit/creditproduct')->getCreditValue($product);
        $store = Mage::app()->getStore();
        switch ($creditValue['type']) {
            case 'range':
                $creditValue['from'] = $this->convertPrice($product, $creditValue['from']);
                $creditValue['to'] = $this->convertPrice($product, $creditValue['to']);
                $creditValue['from_txt'] = $store->formatPrice($creditValue['from']);
                $creditValue['to_txt'] = $store->formatPrice($creditValue['to']);
                break;
            case 'dropdown':
                $creditValue['options'] = $this->_convertPrices($product, $creditValue['options']);
                $creditValue['prices'] = $this->_convertPrices($product, $creditValue['prices']);
                $creditValue['prices'] = array_combine($creditValue['options'], $creditValue['prices']);
                $creditValue['options_txt'] = $this->_formatPrices($creditValue['options']);
                break;
            case 'static':
                $creditValue['value'] = $this->convertPrice($product, $creditValue['value']);
                $creditValue['value_txt'] = $store->formatPrice($creditValue['value']);
                $creditValue['price'] = $this->convertPrice($product, $creditValue['credit_price']);
                break;
            default:
                $creditValue['type'] = 'any';
        }

        return $creditValue;
    }

    protected function _convertPrices($product, $basePrices)
    {
        foreach ($basePrices as $key => $price)
            $basePrices[$key] = $this->convertPrice($product, $price);
        return $basePrices;
    }

    public function convertPrice($product, $price)
    {
        $includeTax = ( Mage::getStoreConfig('tax/display/type') != 1 );
        $store = Mage::app()->getStore();

        $priceWithTax = Mage::helper('tax')->getPrice($product, $price, $includeTax);
        return $store->convertPrice($priceWithTax);
    }

    protected function _formatPrices($prices)
    {
        $store = Mage::app()->getStore();
        foreach ($prices as $key => $price)
            $prices[$key] = $store->formatPrice($price, false);
        return $prices;
    }

    public function getFormConfigData()
    {
        $request = Mage::app()->getRequest();
        $action = $request->getRequestedRouteName() . '_' . $request->getRequestedControllerName() . '_' . $request->getRequestedActionName();
        if ($action == 'checkout_cart_configure' && $request->getParam('id')) {
            $request = Mage::app()->getRequest();
            $options = Mage::getModel('sales/quote_item_option')->getCollection()->addItemFilter($request->getParam('id'));
            $formData = array();
            foreach ($options as $option)
                $formData[$option->getCode()] = $option->getValue();
            return new Varien_Object($formData);
        }
        return new Varien_Object();
    }

    public function getPriceFormatJs()
    {
        $priceFormat = Mage::app()->getLocale()->getJsPriceFormat();
        return Mage::helper('core')->jsonEncode($priceFormat);
    }
    
    public function allowSendCredit()
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        return Mage::helper('customercredit')->getGeneralConfig('enable_send_credit', $storeId);
    }

}
