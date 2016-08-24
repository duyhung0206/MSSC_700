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
class Magestore_Customercredit_Adminhtml_CheckoutController extends Mage_Adminhtml_Controller_Action
{

    //binh.td 16/4/2015
    public function customercreditPostAction()
    {
        $request = $this->getRequest();
        $result = array();
        if ($request->isPost()) {
            $creditvalue = $request->getParam('credit_value');
            $session = Mage::getSingleton('checkout/session');
//            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
            if ($creditvalue < 0.0001)
                $creditvalue = 0;
            $session->setBaseCustomerCreditAmount($creditvalue);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    protected function _isAllowed(){
         return true;
    }

}
