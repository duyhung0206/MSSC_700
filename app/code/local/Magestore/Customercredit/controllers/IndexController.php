<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Customercredit Index Controller
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @author      Magestore Developer
 */
class Magestore_Customercredit_IndexController extends Mage_Core_Controller_Front_Action
{

    public function prepareredirectAction()
    {
        $session = Mage::getSingleton('customer/session');
        $cartUrl = Mage::getUrl('checkout/cart');
        $session->setBeforeAuthUrl($cartUrl);
        $this->_redirect('customer/account/login');
    }

    /**
     * index action
     */
    public function checkemailAction()
    {
        $result = array();
        $email = $this->getRequest()->getParam('email');
        $websiteId = Mage::app()->getWebsite()->getId();
        $existed = Mage::getModel('customer/customer')->getCollection()
                ->addFieldToFilter('email', $email)
//                ->addFieldToFilter('website_id',$websiteId)
                ->getSize();
        if ($existed)
            $result['existed'] = 1;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function sendemailAction()
    {
        if (!Mage::helper('customercredit/account')->isLoggedIn())
            return $this->_redirect('customer/account/login');
        Mage::getSingleton('core/session')->setData("sentemail", 'yes');
        Mage::getSingleton('core/session')->setData("is_credit_code", 'yes');
        $email = $this->getRequest()->getParam('email');
        $value = $this->getRequest()->getParam('value');
        $message = $this->getRequest()->getParam('message');
        $ran_num = rand(1, 1000000);
        $keycode = md5(md5(md5($ran_num)));
        Mage::getSingleton('core/session')->setData("emailcode", $keycode);
        Mage::getModel('customercredit/customercredit')->sendVerifyEmail($email, $value, $message, $keycode);
        $result = array();
        $result['success'] = 1;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function indexAction()
    {
        if (!Mage::helper('customercredit/account')->isLoggedIn())
            return $this->_redirect('customer/account/login');
        $this->loadLayout();
        $this->renderLayout();
    }

    public function shareAction()
    {
        if (!Mage::helper('customercredit/account')->isLoggedIn())
            return $this->_redirect('customer/account/login');
        $this->loadLayout();
        $this->renderLayout();
    }

    public function redeemAction()
    {
        if (!Mage::helper('customercredit/account')->isLoggedIn())
            return $this->_redirect('customer/account/login');
        $this->loadLayout();
        $this->renderLayout();
    }

    public function redeempostAction()
    {
        $customer_id = Mage::getSingleton('customer/session')->getCustomerId();
        $credit_code = $this->getRequest()->getParam('redeem_credit_code');
        $credit = Mage::getModel('customercredit/creditcode')->getCollection()
            ->addFieldToFilter('credit_code', $credit_code);
        if ($credit->getSize() == 0) {
            Mage::getSingleton('core/session')->addError('Code is invalid. Please check again!');
            $this->_redirect('customercredit/index/redeem');
        } elseif ($credit->getFirstItem()->getStatus() != 1) {
            Mage::getSingleton('core/session')->addError('Code was used. Please check again!');
            $this->_redirect('customercredit/index/redeem');
        } else {
            Mage::getModel('customercredit/creditcode')
                ->changeCodeStatus($credit->getFirstItem()->getId(), Magestore_Customercredit_Model_Status::STATUS_USED);
            $credit_amount = $credit->getFirstItem()->getAmountCredit();
            Mage::getModel('customercredit/transaction')->addTransactionHistory($customer_id, Magestore_Customercredit_Model_TransactionType::TYPE_REDEEM_CREDIT, "redeem credit by code '" . $credit_code . "'", "", $credit_amount);
            Mage::getModel('customercredit/customercredit')->changeCustomerCredit($credit_amount);
            Mage::getSingleton('core/session')->addSuccess('Redeem success!');
            $this->_redirect('customercredit/index/index');
        }
    }

    public function listAction()
    {
//        if (!Mage::helper('customercredit/account')->isLoggedIn())
//            return $this->_redirect('customer/account/login');
        $this->loadLayout();
        $this->renderLayout();
    }

    public function cancelAction()
    {
        if (!Mage::helper('customercredit/account')->isLoggedIn())
            return $this->_redirect('customer/account/login');
        $credit_code_id = $this->getRequest()->getParam('id');
        $customer_id = Mage::getSingleton('customer/session')->getCustomerId();
        $credit_code = Mage::getModel('customercredit/creditcode')->load($credit_code_id);
        $add_balance = $credit_code->getAmountCredit();
        $credit_code_status = $credit_code->getStatus();
        if($credit_code_status == 2 || $credit_code_status == 3 ){
            $warning = $this->__('Credit code %s has been used.',$credit_code->getCreditCode());
            Mage::getSingleton('core/session')->addError($warning);
            return $this->_redirect('*/index/share');             
        }
        Mage::getModel('customercredit/transaction')->addTransactionHistory($customer_id, Magestore_Customercredit_Model_TransactionType::TYPE_CANCEL_SHARE_CREDIT, "cancel share credit ", "", $add_balance);
        Mage::getModel('customercredit/customercredit')->changeCustomerCredit($add_balance);
        Mage::getModel('customercredit/creditcode')->changeCodeStatus($credit_code_id, Magestore_Customercredit_Model_Status::STATUS_CANCELLED);
        return $this->_redirect('*/index/share');
    }

    public function sharepostAction()
    {
        if (!Mage::helper('customercredit/account')->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }
        $customer = Mage::helper('customercredit')->getCustomer();
        $customer_credit = Mage::getModel('customercredit/customercredit')->getCustomerCredit();
        if ($customer_credit <= 0) {
            Mage::getSingleton('core/session')->addError('Your credit amount not enough to share!');
            return $this->_redirect("customercredit/index/share");
        }
        $customer_id = $customer->getId();
        $customer_name = $customer->getFirstname() . " " . $customer->getLastname();
        $customer_email = $customer->getEmail();
        $credit_code_id = $this->getRequest()->getParam('credit_code_id_hide');
        $website_id = Mage::app()->getWebsite()->getId();
        if (Mage::helper('customercredit')->getGeneralConfig('validate')) {

            if (Mage::getSingleton('core/session')->getData("sentemail") != 'yes') {
                return $this->_redirect("customercredit/index/share");
            }
            $keycode = $this->getRequest()->getParam('customercreditcode');
            $email = $this->getRequest()->getParam('email_hide');
            $amount = $this->getRequest()->getParam('amount_hide');
            $amount = Mage::getModel('customercredit/customercredit')->getConvertedToBaseCustomerCredit($amount);
            $message = $this->getRequest()->getParam('message_hide');

            if ($email == $customer_email) {
                Mage::getSingleton('core/session')->addError('Invalid email. Please check again!');
                return $this->_redirect("customercredit/index/share");
            }
            if ($amount < 0 || $amount > $customer_credit) {
                Mage::getSingleton('core/session')->addError('Invalid amount. Please check again!');
                return $this->_redirect("customercredit/index/share");
            }

            $friend_account_id = Mage::getModel('customer/customer')
                ->getCollection()
//                ->addFieldToFilter('website_id',$website_id)
                ->addFieldToFilter('email', $email)
                ->getFirstItem()
                ->getId();
            if (trim($keycode) == trim(Mage::getSingleton('core/session')->getData("emailcode"))) {

                Mage::getModel('customercredit/transaction')->addTransactionHistory($customer_id, Magestore_Customercredit_Model_TransactionType::TYPE_SHARE_CREDIT_TO_FRIENDS, $customer_email . " sent " . $amount . " credit to " . $email, "", -$amount);
                Mage::getModel('customercredit/customercredit')->changeCustomerCredit(-$amount);
                if (isset($friend_account_id)) {
                    Mage::getModel('customercredit/transaction')
                        ->addTransactionHistory($friend_account_id, Magestore_Customercredit_Model_TransactionType::TYPE_RECEIVE_CREDIT_FROM_FRIENDS, $email . " received " . $amount . " credit from " . $customer_name, "", $amount);
                    Mage::getModel('customercredit/customercredit')->addCreditToFriend($amount, $friend_account_id);
                } else {
                    if (isset($credit_code_id)) {
                        Mage::getModel('customercredit/creditcode')->changeCodeStatus($credit_code_id, Magestore_Customercredit_Model_Status::STATUS_UNUSED);
                        Mage::getModel('customercredit/customercredit')->sendCreditToFriendByEmailAfterVerify($credit_code_id, $amount, $email, $message);                        
                    } else {
                        Mage::getModel('customercredit/customercredit')->sendCreditToFriendByEmail($amount, $email, $message, $customer_id);
                    }
                }
                Mage::getModel('customercredit/customercredit')->sendSuccessEmail($customer_email, $customer_name, $email, false);
                Mage::getSingleton('core/session')->setData("sentemail", 'no');
                Mage::getSingleton('core/session')->addSuccess('Credit has been successfully sent to ' . $email);
                $session = Mage::getSingleton('core/session');
                $session->setVerify(false);
                $session->setEmail(false);
                $session->setValue(false);
                $session->setId(false);
                $session->setDescription(false);
                return $this->_redirect("customercredit/index/share");
            } else {
                Mage::getSingleton('core/session')->addError('Invalid verify code. Please check again!');
                return $this->_redirect("customercredit/index/share");
            }
        } else {
            $email = $this->getRequest()->getParam('customercredit_email_input');
            $amount = $this->getRequest()->getParam('customercredit_value_input');
            $amount = Mage::getModel('customercredit/customercredit')->getConvertedToBaseCustomerCredit($amount);
            $message = $this->getRequest()->getParam('customer-credit-share-message');
            $friend_account_id = Mage::getModel('customer/customer')
                ->getCollection()
//                ->addFieldToFilter('website_id',$website_id)
                ->addFieldToFilter('email', $email)
                ->getFirstItem()
                ->getId();
            if ($email == $customer_email) {
                Mage::getSingleton('core/session')->addError('Invalid email. Please check again!');
                return $this->_redirect("customercredit/index/share");
            }
            if ($amount < 0 || $amount > $customer_credit) {
                Mage::getSingleton('core/session')->addError('Invalid amount. Please check again!');
                return $this->_redirect("customercredit/index/share");
            }
            Mage::getModel('customercredit/transaction')->addTransactionHistory($customer_id, Magestore_Customercredit_Model_TransactionType::TYPE_SHARE_CREDIT_TO_FRIENDS, $customer_email . " sent " . $amount . " credit to " . $email, "", -$amount);

            Mage::getModel('customercredit/customercredit')->changeCustomerCredit(-$amount);

            if (isset($friend_account_id)) {
                Mage::getModel('customercredit/transaction')
                    ->addTransactionHistory($friend_account_id, Magestore_Customercredit_Model_TransactionType::TYPE_RECEIVE_CREDIT_FROM_FRIENDS, $email . " received " . $amount . " credit from " . $customer_name, "", $amount);
                Mage::getModel('customercredit/customercredit')->addCreditToFriend($amount, $friend_account_id);
//                Mage::getModel('customercredit/customercredit')->sendCodeToFriendEmail($email, $amount, $message);
            } else {
                Mage::getModel('customercredit/customercredit')->sendCreditToFriendByEmail($amount, $email, $message, $customer_id);
            }
            Mage::getModel('customercredit/customercredit')->sendSuccessEmail($customer_email, $customer_name, $email, false);
            Mage::getSingleton('core/session')->setData("sentemail", 'no');
            Mage::getSingleton('core/session')->addSuccess('Credit has been successfully sent to ' . $email);
            $this->_redirect("customercredit/index/share");
        }
    }

    public function validateCustomerAction()
    {
        $session = Mage::getSingleton('core/session');
        if (!Mage::helper('customercredit/account')->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }
        if ($validate_config = Mage::helper('customercredit')->getGeneralConfig('validate', null) == 0) {
            $this->_redirect("customercredit/index/share");
        }
        $website_id = Mage::app()->getWebsite()->getId();
        $sender_id = Mage::getSingleton('customer/session')->getCustomerId();
        $customer = Mage::getModel('customer/customer');
        $sender_email = $customer->load($sender_id)->getEmail();
        $recipient_email = $this->getRequest()->getPost('customercredit_email_input');
        $credit_amount = $this->getRequest()->getPost('customercredit_value_input');
        $description = $this->getRequest()->getPost('customer-credit-share-message');
        $is_send_email = Mage::getSingleton('core/session')->getData('is_credit_code');
        $customer_id = $customer->getCollection()
//                ->addFieldToFilter('website_id',$website_id)
                ->addFieldToFilter('email', $recipient_email)
                ->getFirstItem()->getId();
        $session->setEmail($recipient_email);
        $session->setValue($credit_amount);

        if (isset($description) && $description != "") {
            $session->setDescription($description);
        }
        if ($recipient_email && $credit_amount && !isset($customer_id) && ($is_send_email == 'yes')) {
            $credit_code = Mage::getModel('customercredit/creditcode')->addCreditCode($recipient_email, $credit_amount, Magestore_Customercredit_Model_Status::STATUS_AWAITING_VERIFICATION, $sender_id);
            $credit_code_id = Mage::getModel('customercredit/creditcode')->getCollection()
                    ->addFieldToFilter('credit_code', $credit_code)
                    ->getFirstItem()->getId();
            if (isset($credit_code_id)) {
                $this->getRequest()->setParam('id', $credit_code_id);
                $session->setId($credit_code_id);
            }
        }
        $session->setData("is_credit_code", 'no');
        $session->setVerify(true);
        $this->loadLayout();
        $this->renderLayout();
        $this->_redirect('*/*/share');
        $session->addSuccess('A verification code has been sent to <a href="mailto:' . $sender_email . '"><b>your email</b></a>. Now, please check your email and verify your credit sending!');
    }

    public function checkCreditAction()
    {
        if (!Mage::helper('customercredit/account')->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }
        $session = Mage::getModel('checkout/session');
        $unchecked = $this->getRequest()->getParam('check_credit');
        if (($unchecked) && ($unchecked == 'unchecked')) {
            $session->setBaseCustomerCreditAmount(0.0);
        }
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array) $modules;
        if (isset($modulesArray['Magestore_Onestepcheckout']) && Mage::getStoreConfig('onestepcheckout/general/active') == '1') {
            $result['isonestep'] = Mage::getUrl('onestepcheckout/index/save_shipping', array('_secure' => true));
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function verifySenderAction()
    {
        $session = Mage::getSingleton('core/session');
        if (!Mage::helper('customercredit/account')->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }
        if ($validate_config = Mage::helper('customercredit')->getGeneralConfig('validate', null) == 0) {
            $this->_redirect("customercredit/index/share");
        }
        $sender_id = Mage::getSingleton('customer/session')->getCustomerId();
        $customer = Mage::getModel('customer/customer');
        $sender_email = $customer->load($sender_id)->getEmail();
        $id = $this->getRequest()->getParam('id');
        $email = $this->getRequest()->getParam('customercredit_email_input');
        $value = $this->getRequest()->getParam('customercredit_value_input');
        $description = $this->getRequest()->getParam('customer-credit-share-message');
        $session->setEmail($email);
        $session->setValue($value);
        $session->setId($id);
        $session->setDescription($description);
        if (isset($id) && ($email) && isset($value)) {
            Mage::getSingleton('core/session')->setData("sentemail", 'yes');
            $ran_num = rand(1, 1000000);
            $keycode = md5(md5(md5($ran_num)));
            Mage::getSingleton('core/session')->setData("emailcode", $keycode);
            Mage::getModel('customercredit/customercredit')->sendVerifyEmail($email, $value, null, $keycode);
        }
//        Mage::getSingleton('core/session')->addSuccess('A verification code has been sent to <a href="mailto:' . $sender_email . '"><b>your email</b></a>. Now, please check your email and verify your credit sending!');
        $this->loadLayout();
        $this->renderLayout();
        Mage::getSingleton('core/session')->setVerify(true);
        $this->_redirect('*/*/share');
        Mage::getSingleton('core/session')->addSuccess('A verification code has been sent to <a href="mailto:' . $sender_email . '"><b>your email</b></a>. Now, please check your email and verify your credit sending!');
    }

    public function unVerifySenderAction()
    {
        $session = Mage::getSingleton('core/session');
        $session->setVerify(null);
        $session->setEmail(null);
        $session->setValue(null);
        $session->setId(null);
        $session->setDescription(null);
        $this->_redirect('*/*/share');
    }

}
