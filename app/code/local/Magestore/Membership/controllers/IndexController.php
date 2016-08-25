<?php

class Magestore_Membership_IndexController extends Mage_Core_Controller_Front_Action {

    public function autorenewAction(){


        $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();




        $package_id=2;
//        $customer_id= 1;
        $customer = Mage::getModel('customer/customer')->load($customer_id);

        require_once 'app/Mage.php';
        Mage::app();

        $storeId = $customer->getStoreId();
        $quote = Mage::getModel('sales/quote')->setStoreId($storeId);
        $quote->assignCustomer($customer);

        $customer = Mage::getModel('customer/customer')->load($customer_id);
        $package = Mage::getModel('membership/package')->load($package_id);
        $product = Mage::getModel('catalog/product')->load($package->getProductId());

        $params['qty'] = 1;
        $request = new Varien_Object();
        $request->setData($params);

        // add product(s)
        $quote->addProduct($product, $request);

        $billingAddress = $quote->getBillingAddress()->addData($customer->getPrimaryBillingAddress());

    //create method payment
        $quote->getPayment()->setMethod('cashondelivery');

        $quote->collectTotals()->save();

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();

        //Create invoice for order (change status = complete)
        $order = $service->getOrder();
        $order = Mage::getModel('sales/order')
            ->load($order->getId());

        //Add transaction history customer credit
        Mage::getModel('customercredit/transaction')->addTransactionHistory($customer_id,
            Magestore_Customercredit_Model_TransactionType::TYPE_RENEW_MEMBERSHIP_PACKAGE,
            Mage::helper('customercredit')->__('auto renew membership package #').$package->getPackageName().Mage::helper('customercredit')->__(' in order #'). $order->getIncrementId().'</a>' ,
            $order->getId(),
            -$order->getGrandTotal());
        //Sub customer credit
        Mage::getModel('customercredit/customercredit')->changeCustomerCredit(-$order->getGrandTotal());


        $invoice = $order->prepareInvoice()
            ->setTransactionId($order->getId())
            ->addComment("Auto renew membership package by storecredit")
            ->register()
            ->pay();

        $transaction_save = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        $transaction_save->save();
        //Use credit to pay for order
        $order->setCustomercreditDiscount($order->getGrandTotal());
        $order->setCustomercreditDiscountcription('Customer credit');
        $order->setBaseTotalPaid(0);
        $order->setTotalPaid(0);
        $order->setGrandTotal(0);
        $order->setBaseGrandTotal(0);
        $order->save();

    }

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function packageAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function mymembershipAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function mypackageAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function reorderAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
    }

    public function compareAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function addToCartUrlAction() {
        $productId = $this->getRequest()->getParam('productId');
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
           // $this->_redirect('checkout/cart/add', array('product' => $productId));
		   $block = Mage::getBlockSingleton('catalog/product_list');
		   $this->_redirectUrl($block->getAddToCartUrl(Mage::getModel('catalog/product')->load($productId)));
        } else {
           $this->preLogin();  
        }
    }

    public function preLogin() {
        $productId = Mage::app()->getRequest()->getParam('productId');
		$block = Mage::getBlockSingleton('catalog/product_list');
        // $backUrl = Mage::getUrl('checkout/cart/add', array('product' => $productId));
        $backUrl = $block->getAddToCartUrl(Mage::getModel('catalog/product')->load($productId));
        Mage::getSingleton('customer/session')->setBeforeAuthUrl($backUrl);
        $this->_redirect('customer/account/login');
    }

}