<?php

class Magestore_Membership_Model_Mysql4_Memberpackage_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('membership/memberpackage');
    }

    public function getGrid($memberId) {
        $collection = $this->addFieldToFilter('member_id', $memberId)
                ->getSelect()
                ->joinLeft(array('package' => $this->getTable('membership/package')), 'package.package_id = main_table.package_id', array('package_name' => 'package_name', 'package_price' => 'package_price', 'duration' => 'duration'));

        return $this;
    }

    public function getJoinedCollection() {
        $collection = $this->getSelect()
                ->joinLeft(array('package' => $this->getTable('membership/package')), 'package.package_id = main_table.package_id', array('package_name' => 'package_name', 'package_price' => 'package_price', 'duration' => 'duration'));

        return $this;
    }

}
