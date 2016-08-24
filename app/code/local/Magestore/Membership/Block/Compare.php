<?php

class Magestore_Membership_Block_Compare extends Mage_Core_Block_Template {

    public function setTemplate($template) {
        return parent::setTemplate($template);
    }

    public function isProductInMembership() {
        return Mage::helper('membership')->isProductInMembership($this->getRequest()->getParam('id'));
    }

    public function getCompareUrl() {
        return $this->getUrl('membership/package/compare', array('product' => $this->getRequest()->getParam('id')));
    }

    public function getMembershipPackages() {
        $packageIds = Mage::helper('membership')->getMembershipPackageIds($this->getRequest()->getParam('product'));
        $packages = Mage::getModel('membership/package')->getCollection()
                ->addFieldToFilter('package_id', array('in' => $packageIds))
                ->setOrder('sort_order', 'ASC');
        return $packages;
    }

    public function getMembershipProduct($package) {
        return Mage::getModel('catalog/product')->load($package->getProductId());
    }

    public function getProduct() {
        return Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product'));
    }

    public function getDiscountProduct($package) {
        $productId = $this->getRequest()->getParam('product');
    }

    public function getPackageUrl($package) {
        return $this->getUrl('membership/package/view', array('id' => $package->getId()));
    }

}
