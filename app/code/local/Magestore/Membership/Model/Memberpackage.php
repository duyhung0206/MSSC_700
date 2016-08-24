<?php

class Magestore_Membership_Model_Memberpackage extends Mage_Core_Model_Abstract {

    const STATUS_ENABLED = 1;
    const STATUS_WARNING = 2;
    const STATUS_EXPIRED = 3;
    const XML_PATH_MEMBERSHIP_WARNING_DAYS = 'membership/general/warning_day';

    public function _construct() {
        parent::_construct();
        $this->_init('membership/memberpackage');
    }

    /*
      Update status of a member packpage based on its expired date.
      start time <---STATUS_ENABLED----> waring time <---STATUS_WARNING--->  end time <---STATUS_EXPIRED------> future
      we compare now with three above period of time.
     */

    public function updatePackageStatus() {
        $status = self::STATUS_ENABLED;
        $endTime = $this->getEndTime();

        $warningDays = Mage::getStoreConfig(self::XML_PATH_MEMBERSHIP_WARNING_DAYS);
        $warningDays = $warningDays ? $warningDays : 5;
        $warningTime = date('Y-m-d H:i:s', strtotime($endTime . '-' . $warningDays . ' days'));
        if (now() >= $endTime) {
            $status = self::STATUS_EXPIRED;
        }
        if ((now() < $endTime) && now() >= $warningTime) {
            $status = self::STATUS_WARNING;
        }

        $this->setStatus($status);
    }

    public function getFormatStartTime() {
        return Mage::helper('core')->formatDate($this->getStartTime());
    }

    public function getFormatEndTime() {
        return Mage::helper('core')->formatDate($this->getEndTime());
    }

    public function getFormatDisabledTime() {
        $day = Mage::getStoreConfig('days_active_package_after_expired');
        $time = strtotime($this->getEndTime()) + $day * 24 * 60 * 60;
        return Mage::helper('core')->formatDate(date('Y-m-d H:i:s', $time));
    }

    public function getRemainTime() {
        $diffTime = strtotime($this->getEndTime()) - strtotime(now());
        //print_r($diffTime);die();
        return ceil($diffTime / 24 / 60 / 60);
    }

    public function getRenewUrl() {
        return $this->getUrl('membership/plan/renew', array('id' => $this->getPackageId()));
    }

    public function getPackageViewUrl() {
        return Mage::getUrl('membership/package/view', array('id' => $this->getPackageId()));
    }

}
