<?php

class Magestore_Membership_Block_Package extends Mage_Core_Block_Template {

    public function __construct() {
        parent::__construct();
        $this->setProducts($this->getPackage()->getProducts());
    }

    public function _prepareLayout() {
        parent::_prepareLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        $headBlock->setTitle(Mage::helper('membership')->__('View Package Detail'));

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', array('label' => Mage::helper('cms')->__('Home'), 'title' => Mage::helper('cms')->__('Home Page'), 'link' => Mage::getBaseUrl()));
        $breadcrumbs->addCrumb('membership', array('label' => 'membership', 'title' => 'Membership', 'link' => Mage::getUrl("membership")));
        $breadcrumbs->addCrumb('membership_package', array('label' => $this->getPackage()->getPackageName(), 'title' => $this->getPackage()->getPackageName(), 'link' => null));

        if ($this->getProductCollection()) {
            $pager = $this->getLayout()->createBlock('page/html_pager', 'membership.package.product.pager')
                    ->setCollection($this->getProductCollection());
            $this->setChild('pager', $pager);
            $this->getProductCollection()->load();
        }

        return $this;
    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }

    public function getViewUrl($product) {
        return $product->getProductUrl();
    }

    public function getPackage() {
        $storeId = Mage::app()->getStore()->getId();
        $packageId = $this->getRequest()->getParam('id');
        return Mage::getModel('membership/package')->setStoreId($storeId)->load($packageId);
    }

    public function getProduct($productId) {
        return Mage::getModel('catalog/product')->load($productId);
    }

    public function getProductCollection() {
        if (!$this->hasData('package_product')) {
            $attSet = Mage::getModel('eav/entity_attribute_set')->load('Membership', "attribute_set_name");
            $package = $this->getPackage();
            $productIds = $package->getAllProductIds();
            $productCollection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id', array('in' => $productIds))
                    ->addFieldToFilter('price', array('gt' => 0))
                    ->addFieldToFilter('attribute_set_id', array('nin' => $attSet->getId()))
                    ->setOrder('price', 'DESC');
            $visibility = array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
            );
            $productCollection->addAttributeToFilter('visibility', $visibility);
            $this->setData('package_product', $productCollection);
        }
        return $this->getData('package_product');
    }

}
