<?php

class Magestore_Membership_Model_Package extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('membership/package');
    }

    public function getGroupIds() {
        $groupIds = array();
        //get from group/group
        $collection = Mage::getModel('membership/packagegroup')->getCollection()
                ->addFieldToFilter('package_id', $this->getId())
        ;

        if (count($collection)) {
            foreach ($collection as $item) {
                $groupIds[] = $item->getGroupId();
            }
        }
        return $groupIds;
    }

    public function getProductIds() {
        $productIds = array();
        $collection = Mage::getModel('membership/packageproduct')->getCollection()
                ->addFieldToFilter('package_id', $this->getId());

        if (count($collection)) {
            foreach ($collection as $item) {
                $productIds[] = $item->getProductId();
            }
        }
        return $productIds;
    }

    public function getAllProductIds() {
        $productIds = $this->getProductIds();
        $groupIds = $this->getGroupIds();
        $collection = Mage::getModel('membership/groupproduct')->getCollection()
                ->addFieldToFilter('group_id', array('in' => $groupIds));

        if (count($collection)) {
            foreach ($collection as $item) {
                $productIds[] = $item->getProductId();
            }
        }
        if (count($productIds) > 1)
            $productIds = array_unique($productIds);

        return $productIds;
    }

    /*
      get list product of a package.
      The order is sorted by the total of money saved.
     */

    public function getSortedProductByMembershipPrice() {
        $productIds = $this->getAllProductIds();
        $listToSort = array();
        if (count($productIds)) {
            foreach ($productIds as $productId) {
                $product = Mage::getModel("catalog/product")->load($productId);
                $listToSort[$productId] = Mage::helper('membership')->getMembershipPrice($productId, $this) - $product->getPrice();
            }
        }
        asort($listToSort, SORT_NUMERIC);

        $productIds = array_keys($listToSort);

        $products = array();
        if (count($productIds)) {
            foreach ($productIds as $productId) {
                $products[] = Mage::getModel("catalog/product")->load($productId);
            }
        }
        return $products;
    }

//getSortedProductByMembershipPrice

    public function getProducts() {
        return Mage::getModel('catalog/product')->getCollection()
                        ->addFieldToFilter('entity_id', array('in' => $this->getAllProductIds()))
                        ->addAttributeToSelect('name')
                        ->addAttributeToSelect('price');
    }

    public function getFormatPrice() {
        return Mage::helper('core')->currency($this->getPrice());
    }

    public function getViewUrl() {
        return Mage::getUrl('membership/package/view', array('id' => $this->getId()));
    }

    /* @var $_store_id Support Multiple Store */

    protected $_store_id = null;

    public function setStoreId($value) {
        $this->_store_id = $value;
        return $this;
    }

    public function getStoreId() {
        return $this->_store_id;
    }

    public function getStoreAttributes() {
        return array(
            'package_name',
            'description',
            'package_status'
        );
    }

    public function load($id, $field = null) {
        parent::load($id, $field);
        if ($this->getStoreId()) {
            $this->loadPackageValue();
        }
        return $this;
    }

    public function loadPackageValue($storeId = null) {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }
        if (!$storeId) {
            return $this;
        }
        $storeValues = Mage::getModel('membership/packagevalue')->getCollection()
                ->addFieldToFilter('package_id', $this->getId())
                ->addFieldToFilter('store_id', $storeId);
        foreach ($storeValues as $value) {
            $this->setData($value->getAttributeCode() . '_in_store', true);
            $this->setData($value->getAttributeCode(), $value->getValue());
        }

        $newStoreId = Mage::app()->getStore($storeId)->getWebsite()->getDefaultStore()->getId();
        $newstoreValues = Mage::getModel('membership/packagevalue')->getCollection()
                ->addFieldToFilter('package_id', $this->getId())
                ->addFieldToFilter('attribute_code', 'package_status')
                ->addFieldToFilter('store_id', $newStoreId);
        foreach ($newstoreValues as $value) {
            $this->setData($value->getAttributeCode() . '_in_store', true);
            $this->setData($value->getAttributeCode(), $value->getValue());
        }
        return $this;
    }

    protected function _beforeSave() {
        if ($this->getId()) {
            $model = Mage::getModel('membership/package')->load($this->getId());
            if ($model->getId()) {
                $this->setOldUrlKey($model->getUrlKey());
            }
        }
        if ($storeId = $this->getStoreId()) {
            $defaultStore = Mage::getModel('membership/package')->load($this->getId());
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                if ($this->getData($attribute . '_default')) {
                    $this->setData($attribute . '_in_store', false);
                } else {
                    $this->setData($attribute . '_in_store', true);
                    $this->setData($attribute . '_value', $this->getData($attribute));
                }
                $this->setData($attribute, $defaultStore->getData($attribute));
            }
        }
        return parent::_beforeSave();
    }

    protected function _afterSave() {
        if ($storeId = $this->getStoreId()) {
            $newStoreId = Mage::app()->getStore($storeId)->getWebsite()->getDefaultStore()->getId();
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                if ($attribute == 'package_status') {
                    $attributeValue = Mage::getModel('membership/packagevalue')
                            ->loadAttributeValue($this->getId(), $newStoreId, $attribute);
                } else {
                    $attributeValue = Mage::getModel('membership/packagevalue')
                            ->loadAttributeValue($this->getId(), $storeId, $attribute);
                }

                if ($this->getData($attribute . '_in_store')) {
                    try {
                        $attributeValue->setValue($this->getData($attribute . '_value'))->save();
                    } catch (Exception $e) {
                        
                    }
                } elseif ($attributeValue && $attributeValue->getId()) {
                    try {
                        $attributeValue->delete();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
        $url_key = $this->getData('url_key');
        $old_url_key = $this->getOldUrlKey();
        if ($url_key && $url_key != $old_url_key) {
            $urlRewriteNew = Mage::getModel('core/url_rewrite')
                    ->setStoreId(0)
                    ->setIdPath($url_key)
                    ->setRequestPath($url_key . '.html')
                    ->setTargetPath('membership/package/view/id/' . $this->getId());
            $urlRewriteOld = Mage::getModel('core/url_rewrite')
                    ->getCollection()
                    ->addFieldToFilter('id_path', $old_url_key)
                    ->getFirstItem();
            try {
                $urlRewriteNew->save();
                if ($urlRewriteOld->getId()) {
                    $urlRewriteOld->delete();
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
        return parent::_afterSave();
    }

    /* end code for multiple store */
    /*
     * get Unit of time label
     */

    public function getUnitTimeLabel() {
        switch ($this->getUnitOfTime()) {
            case Magestore_Membership_Model_Package_Unitoftime::UNIT_DAY:
                $label = __('day(s)');
                break;
            case Magestore_Membership_Model_Package_Unitoftime::UNIT_WEEK:
                $label = __('week(s)');
                break;
            case Magestore_Membership_Model_Package_Unitoftime::UNIT_MONTH:
                $label = __('month(s)');
                break;
            case Magestore_Membership_Model_Package_Unitoftime::UNIT_YEAR:
                $label = __('year(s)');
                break;
            default: $label = '';
                break;
        }
        return $label;
    }

}
