<?php

class Magestore_Membership_Block_Exchangeproduct extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setPackages($this->getMyPackages());
    }

    public function getNameOrderExchange($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        return "#" . $order->getIncrementId();
    }
    public function _prepareLayout()
    {
        $headBlock = $this->getLayout()->getBlock('head');
        $headBlock->setTitle(Mage::helper('membership')->__(' My exchangeproduct'));

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', array('label' => Mage::helper('cms')->__('Home   '), 'title' => Mage::helper('cms')->__('Home Page'), 'link' => Mage::getBaseUrl()));
        $breadcrumbs->addCrumb('account', array('label' => Mage::helper('customer')->__('My Account'), 'title' => Mage::helper('customer')->__('My Account'), 'link' => Mage::getUrl('customer/account')));
        $breadcrumbs->addCrumb('membership', array('label' => 'exchangeproduct', 'title' => 'Exchangeproduct', 'link' => null));

        return parent::_prepareLayout();
    }


    public function getListProductBoughts()
    {
        $store = Mage::app()->getStore();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId();
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('store_id', $store->getId())
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', 'complete');

        $listProductMember = array();
        $membershipPackages = Mage::getModel('membership/package')->getCollection();
        foreach ($membershipPackages as $membershipPackage) {
            $listProductMember[] = $membershipPackage->getProductId();
        }

        $listProductBoughts = array();
        foreach ($orders as $order) {
            $items = $order->getAllVisibleItems();
            foreach ($items as $i):
                if (in_array($i->getProductId(), $listProductMember))
                    continue;
                $qty = (int)$i->getQtyShipped() - (int)$i->getQtyRefunded();
                if (isset($listProductBoughts[$i->getProductId()])) {
                    $oldQty = $listProductBoughts[$i->getProductId()];
                    $listProductBoughts[$i->getProductId()] = $qty + $oldQty;
                } else {
                    $listProductBoughts[$i->getProductId()] = $qty;
                }
                if ($listProductBoughts[$i->getProductId()] == 0)
                    unset($listProductBoughts[$i->getProductId()]);
            endforeach;
        }
        return $listProductBoughts;
    }

    public function getListProductBoughtsCanExchange()
    {
        $data = array();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId)
            return;
        $listProductBoughts = $this->getListProductBoughts();
        $array_product_id_boughts = array_keys($listProductBoughts);
        foreach ($array_product_id_boughts as $array_product_id_bought) {
            if (!Mage::helper('membership')->getMemberStatus($customerId)) {
                unset($listProductBoughts[$array_product_id_bought]);
                continue;
            };
            $product = Mage::getModel('catalog/product')->load($array_product_id_bought);

            if ($product->getTypeId() == "simple") {
                $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
                if (!$parentIds[0])
                    $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                $parentIds = $parentIds[0];

            }
            $groupName2 = Mage::helper('membership')->isGroupDiscount($customerId, $parentIds);
            $groupName1 = Mage::helper('membership')->isGroupDiscount($customerId, $product->getId());
            if (!$groupName1 && !$groupName2) {
                unset($listProductBoughts[$array_product_id_bought]);
                continue;
            }
        }
        return $listProductBoughts;
    }

    public function getProduct($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product;
    }

    public function getGroupProductExchange()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId();
        $collection = Mage::helper('membership')->isGroupDiscount($customerId, null);
        $groupProduct = array();
        if (!$collection)
            return $groupProduct;
        foreach ($collection->getData() as $item) {
            $store = Mage::app()->getStore();
            $product = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', $item['product_id']);
            if ($store)
                $product->addStoreFilter($store);
            $product->addAttributeToFilter('status', 1);
            $visibleStatus = array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
            );

            $product->addAttributeToFilter('visibility', array('in' => $visibleStatus));
            if (count($product) == 0)
                continue;
            if (Mage::getModel('catalog/product')->load($product->getData()[0]["entity_id"])->getStockItem()->getQty() <= 0)
                continue;
            $groupProduct[$item['group_id']][] = $item['product_id'];
        }
        return $groupProduct;
    }

    public function getTransaction()
    {
        //get information of the logged-in customer
        $customer_id = Mage::getSingleton('customer/session')->getCustomerId();

        //get  transaction colletion
        $transaction_collection = Mage::getModel('membership/transaction')->getCollection()
            ->addFieldToFilter('customer_id', $customer_id)
            ->setOrder('transaction_time', 'DESC');

        return $transaction_collection;
    }

    public function isGroupProduct($productId)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId();
        $collection = Mage::helper('membership')->isGroupDiscount($customerId, $productId);

        if (!$collection) {
            return false;
        } else {
            if (!count($collection->getData()))
                return false;
        }

        return $collection->getData()[0]['group_name'];
    }

    public function getExchangeProduct()
    {
        return $this->getUrl('membership/exchangeproduct/exchange');
    }
}
