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
 * Customercredit Observer Model
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @author      Magestore Developer
 */
class Magestore_Customercredit_Model_Observer
{

    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_Customercredit_Model_Observer
     */
    const XML_PATH_DISABLE_GUEST_CHECKOUT = 'catalog/downloadable/disable_guest_checkout';

    public function controllerActionPredispatch($observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        return $this;
    }

    public function customercreditPaymentMethod($observer)
    {
        $block = $observer['block'];
        if ($block instanceof Mage_Checkout_Block_Onepage_Payment_Methods || $block instanceof Magestore_Webpos_Block_Onepage_Payment_Methods) {
            $requestPath = $block->getRequest()->getRequestedRouteName()
                . '_' . $block->getRequest()->getRequestedControllerName()
                . '_' . $block->getRequest()->getRequestedActionName();
            $transport = $observer['transport'];
            $html_addcredit = $block->getLayout()->createBlock('customercredit/payment_form')->renderView();
            $html = $transport->getHtml();
            $html .= '<script type="text/javascript">checkOutLoadCustomerCredit(' . Mage::helper('core')->jsonEncode(array('html' => $html_addcredit)) . ');enableCheckbox();</script>';
            $transport->setHtml($html);
        }

        /* Show Credit Form in the Cart page */
        if (($block instanceof Magestore_RewardPoints_Block_Checkout_Cart_Rewrite_Coupon) || ($block instanceof Mage_Checkout_Block_Cart_Coupon )) {
            $requestPath = $block->getRequest()->getRequestedRouteName()
                . '_' . $block->getRequest()->getRequestedControllerName()
                . '_' . $block->getRequest()->getRequestedActionName();

            if ($requestPath == 'checkout_onepage_index' || strpos($requestPath, 'checkout_cart') === false) {
                return;
            }
            $transport = $observer['transport'];
            $html = $transport->getHtml();
            $html_addreditform = $block->getLayout()->createBlock('customercredit/cart_customercredit')->renderView();
            $html .= $html_addreditform;

            $transport->setHtml($html);
        }
    }
    
    //force creditmemo
    public function salesOrderLoadAfter($observer) {
        $order = $observer['order'];
        if ($order->getCustomercreditDiscount() < 0.0001 || Mage::app()->getStore()->roundPrice($order->getGrandTotal()) > 0 || $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED || $order->isCanceled() || $order->canUnhold()) {
            return $this;
        }
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (($child->getQtyInvoiced() - $child->getQtyRefunded() - $child->getQtyCanceled()) > 0) {
                        $order->setForcedCanCreditmemo(true);
                        return $this;
                    }
                }
            } elseif ($item->getProduct()) {
                if (($item->getQtyInvoiced() - $item->getQtyRefunded() - $item->getQtyCanceled()) > 0) {
                    $order->setForcedCanCreditmemo(true);
                    return $this;
                }
            }
        }
    }
    
    public function customerSaveAfter($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer->getId())
            return $this;
        $credit_value = Mage::app()->getRequest()->getPost('credit_value');
        if (strpos($credit_value, ',')) {
            $credit_value = str_replace(',', '.', $credit_value);
        }
        $description = Mage::app()->getRequest()->getPost('description');
        $group = Mage::app()->getRequest()->getPost('account');
        $customer_group = $group['group_id'];
        $sign = substr($credit_value, 0, 1);
        if (!$credit_value)
            return $this;
        $credithistory = Mage::getModel('customercredit/transaction')->setCustomerId($customer->getId());
        $customers = Mage::getModel('customer/customer')->load($customer->getId());
        if ($sign == "-") {
            $end_credit = $customers->getCreditValue() - substr($credit_value, 1, strlen($credit_value));
            if ($end_credit < 0) {
                $end_credit = 0;
                $credit_value = -$customers->getCreditValue();
            }
        } else {
            $credithistory->setData('received_credit', $credit_value);
            $end_credit = $customers->getCreditValue() + $credit_value;
        }
        $customers->setCreditValue($end_credit);

        $credithistory->setData('type_transaction_id', 1)
            ->setData('detail_transaction', $description)
            ->setData('amount_credit', $credit_value)
            ->setData('end_balance', $customers->getCreditValue())
            ->setData('transaction_time', now())
            ->setData('customer_group_ids', $customer_group);
        try {
            $customers->save();
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customercredit')->__($e->getMessage()));
        }
        try {
            $credithistory->save();
        } catch (Mage_Core_Exception $e) {

            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customercredit')->__($e->getMessage()));
        }
        $sendemail = Mage::app()->getRequest()->getPost('send_mail');
        if ($sendemail == 1) {
            $email = $customer->getEmail();
            $name = $customer->getLastname();
            $balance = $customers->getCreditValue();
            $message = Mage::app()->getRequest()->getPost('description');
            Mage::getModel('customercredit/customercredit')->sendNotifytoCustomer($email, $name, $credit_value, $balance, $message);
        }
        return $this;
    }

    public function orderPlaceAfter($observer)
    {
        $order = $observer['order'];
        $customer_id = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customer_id) {
            $customer_id = $order->getCustomer()->getId();
        }
        $session = Mage::getSingleton('checkout/session');
        $amount = $session->getBaseCustomerCreditAmount();
        if ($amount && !$session->getHasCustomerCreditItem()) {
            Mage::getModel('customercredit/transaction')->addTransactionHistory($customer_id, Magestore_Customercredit_Model_TransactionType::TYPE_CHECK_OUT_BY_CREDIT, Mage::helper('customercredit')->__('check out by credit for order #') . $order->getIncrementId(), $order->getId(), -$amount);
            Mage::getModel('customercredit/customercredit')->changeCustomerCredit(-$amount, $customer_id);
        }
        if ($session->getUseCustomerCredit()) {
            $session->setBaseCustomerCreditAmount(null)
                ->setUseCustomerCredit(false);
//                    ->setHasCustomerCreditItem(false);
        } else {
            $session->setBaseCustomerCreditAmount(null);
//                    ->setHasCustomerCreditItem(false);
        }
    }

    public function orderCancelAfter($observer)
    {
        $order = $observer->getOrder();
        $customer_id = $order->getCustomerId();
        $order_id = $order->getEntityId();
       $installer = Mage::getModel('core/resource_setup');
       $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        if ((float) (string) $order->getBaseCustomercreditDiscount() > 0) {
            $amount_credit = (float) (string) $order->getBaseCustomercreditDiscount();
           $query = 'SELECT SUM(  `customercredit_discount` ) as `total_customercredit_invoiced` , 
                       SUM(  `base_customercredit_discount` ) as `total_base_customercredit_invoiced`
                       FROM  `' . $installer->getTable('sales/invoice') . '` 
                       WHERE  `order_id` = ' . $order_id;
           $data = $read->fetchAll($query);
//            $total_customercredit_invoiced = $data['total_customercredit_invoiced'];
           $total_base_customercredit_invoiced = (float) $data[0]['total_base_customercredit_invoiced'];
           $amount_credit -= $total_base_customercredit_invoiced;
            $type_id = Magestore_Customercredit_Model_TransactionType::TYPE_CANCEL_ORDER;
            $transaction_detail = "Cancel order #" . $order->getIncrementId();
            Mage::getModel('customercredit/transaction')->addTransactionHistory($customer_id, $type_id, $transaction_detail, $order_id, $amount_credit);
            $customer = Mage::getModel('customer/customer')->load($customer_id);
            $creditbefore = $customer->getCreditValue() + $amount_credit;
            $customer->setCreditValue($creditbefore);
            $customer->save();
            return true;
        }
    }

    public function orderSaveAfter($observer)
    {
        
    }

    public function invoiceSaveAfter($observer)
    {
        //Declare variables - Marko
        $invoice = $observer->getEvent()->getInvoice();
        $orderId = $invoice->getOrderId();
        $order = Mage::getSingleton('sales/order')->load($orderId);
        $customer_id = $order->getCustomerId();
        $customer_name = $order->getCustomerName();
        $customer_email = $order->getCustomerEmail();
        $product_credit_value = 0;

        //check if invoice store credit product - Marko
        foreach ($invoice->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                $productId = $item->getProductId();
                $product = Mage::getModel('catalog/product')->load($productId);
                $type = $product->getTypeId();
                
                $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
				$options = $orderItem->getProductOptions();
				$buyRequest = $options['info_buyRequest'];

                if ($type == 'customercredit') {
                    if (isset($buyRequest['send_friend']) && isset($buyRequest['recipient_email']) && $buyRequest['send_friend'] && $customer_email != $buyRequest['recipient_email']) {
                        
                        $email = $buyRequest['recipient_email'];
                        $amount = $buyRequest['amount'];
                        $message = $buyRequest['message'];
                        $friend_account_id = Mage::getModel('customer/customer')
                                ->getCollection()
                                ->addFieldToFilter('email', $email)
                                ->getFirstItem()
                                ->getId();                  

                        Mage::getModel('customercredit/transaction')->addTransactionHistory($customer_id, Magestore_Customercredit_Model_TransactionType::TYPE_SHARE_CREDIT_TO_FRIENDS, $customer_email . " sent " . $amount . " credit to " . $email, "", 0);

                        if (isset($friend_account_id)) {
                            Mage::getModel('customercredit/transaction')
                                ->addTransactionHistory($friend_account_id, Magestore_Customercredit_Model_TransactionType::TYPE_RECEIVE_CREDIT_FROM_FRIENDS, $email . " received " . $amount . " credit from " . $customer_name, "", $amount);
                            Mage::getModel('customercredit/customercredit')->addCreditToFriend($amount, $friend_account_id);                            
                        } else {
                            Mage::getModel('customercredit/customercredit')->sendCreditToFriendByEmail($amount, $email, $message, $customer_id);
                        }
                        
                        Mage::getModel('customercredit/customercredit')->sendSuccessEmail($customer_email, $customer_name, $email, true);
                        
                    } else {
                    //total credit value invoice - Marko
                        $product_credit_value += ((float) $buyRequest['amount']) * ((float) $item->getQty());
                    }
                }
            }
        }

        //create transaction and add credit value to customer if invoice store credit product - Marko
        if ($product_credit_value > 0) {
            Mage::getModel('customercredit/transaction')
                ->addTransactionHistory($order->getCustomerId(), Magestore_Customercredit_Model_TransactionType::TYPE_BUY_CREDIT, "buy credit " . $product_credit_value . " from store ", $order->getId(), $product_credit_value);
            Mage::getModel('customercredit/customercredit')
                ->addCreditToFriend($product_credit_value, $customer_id);
        }
    }

    public function creditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        //declare variables
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $data = Mage::app()->getRequest()->getPost('creditmemo');
        $order_id = $creditmemo->getOrderId();
        $order = Mage::getSingleton('sales/order');
        $order->load($order_id);
        $grand_total = $creditmemo->getGrandTotal();
        $amount_credit = $creditmemo->getCustomercreditDiscount();
        $customer_id = $creditmemo->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customer_id);
        $maxcredit = $grand_total;
        $product_credit_value = 0;

        //check store credit is enough to refund or not - Marko
        if (isset($data['refund_creditbalance_return'])) {
            if (round((float) $data['refund_creditbalance_return'], 3) > round($maxcredit, 3)) {
                Mage::throwException(Mage::helper('customercredit')->__('Credit amount cannot exceed order amount.'));
            }
        }
        //prepare transaction - Marko
        $transaction_detail = "Refund order #" . $order->getIncrementId();
        $type_id = Magestore_Customercredit_Model_TransactionType::TYPE_REFUND_ORDER_INTO_CREDIT;
        //check if refund store credit product - Marko
        foreach ($creditmemo->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                $productId = $item->getProductId();
                $product = Mage::getModel('catalog/product')->load($productId);
                $credit_rate = $product->getCreditRate();
                $type = $product->getTypeId();
                $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
				$options = $orderItem->getProductOptions();
				$buyRequest = $options['info_buyRequest'];
                if ($type == 'customercredit') {
                    //total credit value refund - Marko
                    $product_credit_value += ((float) $buyRequest['amount']) * ((float) $item->getQty());
                }
            }
        }
        $creditBalance = $customer->getCreditValue();
        if ($product_credit_value > $creditBalance) {
            Mage::throwException(
                Mage::helper('customercredit')->__('Credit balance is not enough to refund.')
            );
        }
        //refund store credit product - Marko
        if ($product_credit_value > 0) {
            $type_id = Magestore_Customercredit_Model_TransactionType::TYPE_REFUND_CREDIT_PRODUCT;
            $amount_credit -= $product_credit_value;
        }
        //check if refund to store credit - Marko
        if (isset($data['refund_creditbalance_return'])) {
            if ($data['refund_creditbalance_return_enable'] && $data['refund_creditbalance_return'] > 0) {
                $transaction_detail = "Refund order #" . $order->getIncrementId() . "into customer credit";
                $amount_credit += $data['refund_creditbalance_return'];
            }
        }
        if ($amount_credit) {
            Mage::getModel('customercredit/transaction')->addTransactionHistory($customer_id, $type_id, $transaction_detail, $order_id, $amount_credit);

            //set credit value to customer - Marko
            $credit_value = $customer->getCreditValue() + $amount_credit;
            if ($credit_value < 0) {
                $credit_value = 0;
            }
            $customer->setCreditValue($credit_value);
            try {
                $customer->save();
            } catch (Exception $e) {
                echo Mage::helper('customercredit')->__($e->getMessage());
            }
        }
    }

    public function adminhtmlCatalogProductSaveAfter($observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        $back = $action->getRequest()->getParam('back');
        $session = Mage::getSingleton('customercredit/session');
        $creditproductsession = $session->getCreditProductCreate();

        if ($back || !$creditproductsession)
            return $this;
        $type = $action->getRequest()->getParam('type');
        if (!$type) {
            $id = $action->getRequest()->getParam('id');
            $type = Mage::getModel('catalog/product')->load($id)->getTypeId();
        }
        if (!$type)
            return $this;

        $reponse = Mage::app()->getResponse();
        $url = Mage::getModel('adminhtml/url')->getUrl("adminhtml/creditproduct/index");
        $reponse->setRedirect($url);
        $reponse->sendResponse();
        $session->unsetData('credit_product_create');
        return $this;
    }

    //event checkout_allow guest
    public function isAllowedGuestCheckout(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        $store = $observer->getEvent()->getStore();
        $result = $observer->getEvent()->getResult();
        $session = Mage::getSingleton('checkout/session');
        $isContain = false;

        foreach ($quote->getAllItems() as $item) {
            if (($product = $item->getProduct()) &&
                $product->getTypeId() == 'customercredit') {
                $isContain = true;
            }
        }
        $session->setHasCustomerCreditItem(true);

        if ($isContain && Mage::getStoreConfigFlag(self::XML_PATH_DISABLE_GUEST_CHECKOUT, $store)) {
            $result->setIsAllowed(false);
        }

        return $this;
    }

    public function paypal_prepare_line_items($observer)
    {
        $session = Mage::getSingleton('checkout/session');
        if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
            $paypalCart = $observer->getEvent()->getPaypalCart();
            if ($paypalCart) {
                $salesEntity = $paypalCart->getSalesEntity();
                $customercreditDiscount = $salesEntity->getCustomercreditDiscount();
                if (!$customercreditDiscount)
                    $customercreditDiscount = $session->getBaseCustomerCreditAmount();
                if ($customercreditDiscount)
                    $paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, abs((float) $customercreditDiscount), Mage::helper('customercredit')->__('Customer Credit'));
                }
        }else {
            $salesEntity = $observer->getSalesEntity();
            $additional = $observer->getAdditional();
            if ($salesEntity && $additional) {
                $items = $additional->getItems();
                $items[] = new Varien_Object(array(
                    'name' => Mage::helper('customercredit')->__('Customer Credit'),
                    'qty' => 1,
                    'amount' => -(abs((float) $salesEntity->getCustomercreditDiscount())),
                ));
                $additional->setItems($items);
            }
        }
    }

    /* TrungHa: lock attribute credit_value */

    public function lockAttributes($observer)
    {
        $event = $observer->getEvent();
        $product = $event->getProduct();
        $product->lockAttribute('credit_value');
    }

}
