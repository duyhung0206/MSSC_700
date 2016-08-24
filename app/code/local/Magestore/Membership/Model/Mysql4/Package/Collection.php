<?php

class Magestore_Membership_Model_Mysql4_Package_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected $_store_id = null;
    protected $_addedTable = array();

    public function setStoreId($value) {
        $this->_store_id = $value;
        return $this;
    }

    public function getStoreId() {
        return $this->_store_id;
    }
    
    public function _construct() {
        parent::_construct();
        if ($storeId = Mage::app()->getStore()->getId()) {
            $this->setStoreId($storeId);
        }
        $this->_init('membership/package');
    }
    
    protected function _afterLoad() {
        parent::_afterLoad();
        if ($storeId = $this->getStoreId()) {
            foreach ($this->_items as $item) {
                $item->setStoreId($storeId)->loadPackageValue();
            }
        }
        return $this;
    }

    public function addFieldToFilter($field, $condition = null) {
        $attributes = array(
            'package_name',
            'description',
            'package_status',
        );
        $storeId = $this->getStoreId();
        $newStoreId = Mage::app()->getStore($storeId)->getWebsite()->getDefaultStore()->getId();
        if (in_array($field, $attributes) && $storeId) {
                if (!in_array($field, $this->_addedTable)) {
                    if($field == 'package_status'){
                        $this->getSelect()
                        ->joinLeft(array($field => $this->getTable('membership/packagevalue')), 
                                "main_table.package_id = $field.package_id" .
                                " AND $field.store_id = $newStoreId" .
                                " AND $field.attribute_code = '$field'", array()
                        );
                    }else{
                        $this->getSelect()
                        ->joinLeft(array($field => $this->getTable('membership/packagevalue')), 
                                "main_table.package_id = $field.package_id" .
                                " AND $field.store_id = $storeId" .
                                " AND $field.attribute_code = '$field'", array()
                        );
                    }
                    $this->_addedTable[] = $field;
                }
            $expression = 'IF('.$field.'.value IS NULL, main_table.'.$field.', '.$field.'.value)';
            $con = parent::_getConditionSql($expression, $condition);
            $this->_select->where($con);
            return $this;
        }
        if ($field == 'package_id') {
            $field = 'main_table.package_id';
        }
        return parent::addFieldToFilter($field, $condition);
    }

}