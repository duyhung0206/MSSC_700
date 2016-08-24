<?php

class Magestore_Membership_Block_Membership extends Mage_Core_Block_Template {

    const XML_PATH_MEMBERSHIP_ABOUT_MEMBERSHIP = 'membership/general/about_membership';

    public function __construct() {
        parent::__construct();
        $this->setPackages($this->getAllPackages());
    }

    public function _prepareLayout() {
        $headBlock = $this->getLayout()->getBlock('head');
        $headBlock->setTitle(Mage::helper('membership')->__('Membership'));

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', array('label' => Mage::helper('cms')->__('Home'), 'title' => Mage::helper('cms')->__('Home Page'), 'link' => Mage::getBaseUrl()));
        $breadcrumbs->addCrumb('membership', array('label' => 'membership', 'title' => 'Membership', 'link' => Mage::getUrl("membership")));
        return parent::_prepareLayout();
    }

    public function getAllPackages() {
        $storeId = Mage::app()->getStore()->getId();
        $packages = Mage::getModel('membership/package')->getCollection()
                ->addFieldToFilter('package_status', 1)
                ->setOrder('sort_order', 'ASC')
                ->setStoreId($storeId);
        return $packages;
    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }

    public function getViewUrl($package) {
        if($url_key = $package->getUrlKey())
            return $this->getUrl('', array('_direct'=>$url_key.'.html'));
        return $this->getUrl('membership/package/view', array('id' => $package->getId()));
    }

    public function getWelcomeMessage() {
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(self::XML_PATH_MEMBERSHIP_ABOUT_MEMBERSHIP, $storeId);
    }
    public function limitString($string, $limit = 100) {
        if (strlen($string) < $limit) {
            return $string;
        }

        $regex = "/(.{1,$limit})\b/";
        preg_match($regex, $string, $matches);
        return $matches[1].'...';
    }
}
