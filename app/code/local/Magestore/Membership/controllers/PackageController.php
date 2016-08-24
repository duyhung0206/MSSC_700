<?php

class Magestore_Membership_PackageController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function viewAction() {
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

}
