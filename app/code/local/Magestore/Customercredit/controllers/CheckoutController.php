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
 * Customercredit Controller
 * 
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @author      Magestore Developer
 */
class Magestore_Customercredit_CheckoutController extends Mage_Core_Controller_Front_Action
{

    /**
     * change use customer credit to spend
     */
    public function setAmountPostAction()
    {
        $request = $this->getRequest();
        $session = Mage::getSingleton('checkout/session');
        if ($request->isPost()) {

            if (is_numeric($request->getParam('customer_credit')) && Mage::helper('customercredit')->getGeneralConfig('enable')) {
                $credit_amount = $request->getParam('customer_credit');
                $base_credit_amount = Mage::getModel('customercredit/customercredit')
                    ->getConvertedToBaseCustomerCredit($credit_amount);
                $base_customer_credit = Mage::getModel('customercredit/customercredit')->getBaseCustomerCredit();

                $base_credit_amount = ($base_credit_amount > $base_customer_credit) ? $base_customer_credit : $base_credit_amount;

                $session->setBaseCustomerCreditAmount($base_credit_amount);
                $session->setUseCustomerCredit(true);

                $this->_redirect('checkout/cart');
            }


            if (is_numeric($request->getParam('credit_amount'))) {
                $amount = $request->getParam('credit_amount');
                $base_amount = Mage::getModel('customercredit/customercredit')
                    ->getConvertedToBaseCustomerCredit($amount);
                $base_customer_credit = Mage::getModel('customercredit/customercredit')->getBaseCustomerCredit();
                $base_credit_amount = ($base_amount > $base_customer_credit) ? $base_customer_credit : $base_amount;

                $session->setBaseCustomerCreditAmount($base_credit_amount);

                $session->setUseCustomerCredit(true);
                $result = array();
                $result['success'] = 1;
                $result['price0'] = 0;

                $state = $request->getParam('state');
                
                //Tich hop One step checkout - Marko
                $moduleOnestepActive = Mage::getConfig()->getModuleConfig('Magestore_Onestepcheckout')->is('active', 'true');
                $moduleWebposActive = Mage::getConfig()->getModuleConfig('Magestore_Webpos')->is('active', 'true');
                if ($moduleOnestepActive || ($moduleWebposActive && $state == 'webpos')) {
                    if (Mage::getStoreConfig('onestepcheckout/general/active') == '1' && $state == 'onestepcheckout') {
                        $result['saveshippingurl'] = Mage::getUrl('onestepcheckout/index/save_shipping', array('_secure' => true));
                    }
                    if ($state == 'webpos') {
                        $result['saveshippingurl'] = Mage::getUrl('webpos/index/save_shipping', array('_secure' => true));
                    }
                    $result['amount'] = Mage::getModel('customercredit/customercredit')
                        ->getConvertedFromBaseCustomerCredit($session->getBaseCustomerCreditAmount());
                    $result['current_balance'] = Mage::getModel('customercredit/customercredit')->getAvaiableCustomerCreditLabel();
                } else {
                    //update lai payment khi khong co one step
                    Mage::getSingleton('checkout/type_onepage')->getQuote()->collectTotals()->save();
                    $result['amount'] = Mage::getModel('customercredit/customercredit')
                        ->getConvertedFromBaseCustomerCredit($session->getBaseCustomerCreditAmount());
                    if ($session->getBaseCustomerCreditAmount() == Mage::getSingleton('checkout/session')->getQuote()->getBaseGrandTotal())
                        $result['price0'] = 1;
                    $result['current_balance'] = Mage::getModel('customercredit/customercredit')->getAvaiableCustomerCreditLabel();
                    $html = $this->_getPaymentMethodsHtml();
                    $result['payment_html'] = $html;
                }
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
        if (!$request->isPost()) {
            $credit_amount = 0;
            $base_credit_amount = Mage::getModel('customercredit/customercredit')
                ->getConvertedToBaseCustomerCredit($credit_amount);
            $base_customer_credit = Mage::getModel('customercredit/customercredit')->getBaseCustomerCredit();

            $base_credit_amount = ($base_credit_amount > $base_customer_credit) ? $base_customer_credit : $base_credit_amount;
            $session->setBaseCustomerCreditAmount($base_credit_amount);

            $session->setUseCustomerCredit(true);

            $this->_redirect('checkout/cart');
        }
    }

    protected function _getPaymentMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_paymentmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

}
