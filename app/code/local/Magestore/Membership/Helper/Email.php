<?php
class Magestore_Membership_Helper_Email extends Mage_Core_Helper_Abstract
{
	const XML_PATH_EMAIL_IDENTITY = 'trans_email/ident_sales';
	const XML_PATH_NEW_MEMBER_EMAIL = 'membership/general/new_member_email_template';
    const XML_PATH_NEW_PACKAGE_EMAIL = 'membership/general/new_package_email_template';
	const XML_PATH_NOTIFY_RENEW_PACKAGE_EMAIL = 'membership/general/notify_renew_package_email_template';
	
	public function sendEmailNewPackage($memberPackage){
		$translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
		$template = Mage::getStoreConfig(self::XML_PATH_NEW_PACKAGE_EMAIL);
		
		$member = Mage::getModel('membership/member')->load($memberPackage->getMemberId());
		$package = Mage::getModel('membership/package')->load($memberPackage->getPackageId());
		$recipient = array(
                		'email' => $member->getMemberEmail(),
                		'name'  => $member->getMemberName(),
            		);
		
		$mailTemplate = Mage::getModel('core/email_template');
		
		$storeId = Mage::app()->getStore()->getId();
		$mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
			->sendTransactional(
				$template,
				Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId),
				$recipient['email'],
				$recipient['name'],
				array(
					'member'  => $member,					
					'memberPackage' => $memberPackage,
					'package' => $package
				)
			);
		// var_dump($mailTemplate->getProcessedTemplate(array(
                      // 'member'  => $member,					
					  // 'memberPackage' => $memberPackage					
                    // )));
					// die();
		$translate->setTranslateInline(true);
		
		return $member;
	}
	
	public function sendEmailNotifyRenewPackage($memberPackage){
		$translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
		$template = Mage::getStoreConfig(self::XML_PATH_NOTIFY_RENEW_PACKAGE_EMAIL);
		
		$member = Mage::getModel('membership/member')->load($memberPackage->getMemberId());
		$package = Mage::getModel('membership/package')->load($memberPackage->getPackageId());
		$recipient = array(
                		'email' => $member->getMemberEmail(),
                		'name'  => $member->getMemberName(),
            		);
		
		$mailTemplate = Mage::getModel('core/email_template');
		
		$storeId = Mage::app()->getStore()->getId();
		$mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
			->sendTransactional(
				$template,
				Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId),
				$recipient['email'],
				$recipient['name'],
				array(
					'member'  => $member,
					'package' => $package,
					'memberPackage' => $memberPackage
				)
			);
		
		$translate->setTranslateInline(true);
		
		return $memberPackage;
	}	
}