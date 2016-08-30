<?php

class Magestore_Membership_IndexController extends Mage_Core_Controller_Front_Action {

    public function blockaccountAction(){
        $this->loadLayout();
        $this->renderLayout();
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