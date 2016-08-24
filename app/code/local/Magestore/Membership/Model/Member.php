<?php

class Magestore_Membership_Model_Member extends Mage_Core_Model_Abstract
{
    const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 2;
	public function _construct()
    {
        parent::_construct();
        $this->_init('membership/member');
    }
	
	/*
	check the status of a member
	@return: true if member is enabled, false if member is disabled
	*/
	public function isEnable()
	{
		$status = $this->getStatus();		
		return $status == self::STATUS_ENABLED;
	}//end isEnable
	
}