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
 * @package     Magestore_Membership
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Membership Model
 *
 * @category    Magestore
 * @package     Magestore_Membership
 * @author      Magestore Developer
 */
class Magestore_Membership_Model_Transaction extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('membership/transaction');
    }

    protected function _beforeSave()
    {
        $this->setTransactionTime(now());
        
        return parent::_beforeSave();
    }

    public function addTransaction($customer_id, $transaction_detail, $product_id_old, $order_id_old, $product_id_new, $order_id_new)
    {
        $transaction = Mage::getModel('membership/transaction');
        $transaction->setCustomerId($customer_id)
            ->setDetailTransaction($transaction_detail)
            ->setProductIdOld($product_id_old)
            ->setOrderIdOld($order_id_old)
			->setProductIdNew($product_id_new)
            ->setOrderIdNew($order_id_new);
        try {
            $transaction->save();
        } catch (Exception $ex) {
            $ex->getMessage();
        }
    }

    // public function getTransactionByOrderId($order_id)
    // {
        // $transactions = Mage::getModel('customercredit/transaction')->getCollection()
            // ->addFieldToFilter('order_increment_id', $order_id);
        // return $transactions;
    // }

    // public function getTransactionCreditMemo($order_id, $type_id)
    // {
        // $transactions = Mage::getModel('customercredit/transaction')->getCollection()
            // ->addFieldToFilter('order_increment_id', $order_id)
            // ->addFieldToFilter('type_transaction_id', $type_id)
            // ->addFieldToFilter('amount_credit', array('gt' => 0));
        // return $transactions->getSize();
    // }

}
