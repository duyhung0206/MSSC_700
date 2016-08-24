<?php

class Magestore_Membership_Block_Plan extends Mage_Core_Block_Template {

    public function __construct() {
        parent::__construct();
        $this->setPackages($this->getMyPackages());
    }

    public function _prepareLayout() {
        $headBlock = $this->getLayout()->getBlock('head');
        $headBlock->setTitle(Mage::helper('membership')->__(' My Membership'));

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', array('label' => Mage::helper('cms')->__('Home'), 'title' => Mage::helper('cms')->__('Home Page'), 'link' => Mage::getBaseUrl()));
        $breadcrumbs->addCrumb('account', array('label' => Mage::helper('customer')->__('My Account'), 'title' => Mage::helper('customer')->__('My Account'), 'link' => Mage::getUrl('customer/account')));
        $breadcrumbs->addCrumb('membership', array('label' => 'membership', 'title' => 'Membership', 'link' => null));

        return parent::_prepareLayout();
    }

    public function getMyPackages() {
        $memberId = $this->_getMemberId();
        $packages = Mage::getResourceModel('membership/memberpackage_collection')->getGrid($memberId)
                ->setOrder('member_package_id', 'DESC');
        return $packages;
    }

    public function getEnabledPackges() {
        return $this->getMyPackages()->addFieldToFilter('main_table.status', array('in' => array(1, 3)));
    }

    public function getFormatRemainTime($package) {
        $remainTime = $package->getRemainTime();
        if ($remainTime > 0)
            return $remainTime . $this->__(' day(s)');
        else {
            $expandTime = Mage::getStoreConfig('membership/general/days_active_package_after_expired');
            if (($remainTime + $expandTime) > 0)
                return '0 +' . ($remainTime + $expandTime) . $this->__(' day(s)');
            else
                return 0 . $this->__(' day');
        }
    }

    protected function _getMemberId() {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        return Mage::helper('membership')->getMemberId($customerId);
    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }

    public function setStatusPackage() {
        $memberId = $this->_getMemberId();
        Mage::helper('membership')->setStatusMemberPackage($memberId);
    }

    public function getMemberStatus() {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        return Mage::helper('membership')->getMemberStatus($customerId);
    }

    /*
      get renew url
      @id: package_id
     */

    public function getRenewUrl($id) {
        return $this->getUrl('membership/plan/renew', array('id' => $id));
    }

    public function getViewUrl($package_id) {
        return $this->getUrl('membership/package/view', array('id' => $package_id));
    }

    /*
      get all payment histories of a member
      @return: payment history collection

     */

    public function getPaymentHistories() {
        //get information of the logged-in customer
        $customer_id = Mage::getSingleton('customer/session')->getCustomerId();
        $member_id = Mage::helper('membership')->getMemberId($customer_id);

        //get  payment history colletion
        $payment_history_collection = Mage::getModel('membership/paymenthistory')->getCollection()
                ->addFieldToFilter('member_id', $member_id)
                ->setOrder('start_time', 'DESC');

        return $payment_history_collection;
    }

//end getPaymentHistories

    /*
      get all bought packages of the logged-in customer
     */

    public function getMemberPackages() {
        //get information of the logged-in customer
        $customer_id = Mage::getSingleton('customer/session')->getCustomerId();
        $member_id = Mage::helper('membership')->getMemberId($customer_id);

        $packages = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('member_id', $member_id);
        return $packages;
    }

// end getMemberPackage

    public function getOrderStatus($orderId) {
        return Mage::getModel('sales/order')->load($orderId)->getStatus();
    }

}
