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
 * Customercredit Model
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @author      Magestore Developer
 */
class Magestore_Membership_Model_Sales_Order_Invoice_Refundcredit extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $refundcredit = 0;
        $baserefundcredit = 0;
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy())
                continue;

            $refundcredititem = Mage::getModel('sales/quote_item_option')->getCollection()
                ->addFieldToFilter('item_id', $orderItem->getQuoteItemId())
                ->addFieldToFilter('code', 'refund_credit')->getFirstItem()->getValue();
            $qty = Mage::getModel('sales/quote_item_option')->getCollection()
                ->addFieldToFilter('item_id', $orderItem->getQuoteItemId())
                ->addFieldToFilter('code', 'qty_exchange')->getFirstItem()->getValue();
            $refundcredit += $refundcredititem / $qty * $item->getQty();
        }
        $baserefundcredit = $refundcredit;
        $invoice->setRefundcreditAmount($refundcredit);
        $invoice->setBaseRefundcreditAmount($baserefundcredit);

        $invoice->setGrandTotal($invoice->getGrandTotal());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal());

        return $this;
    }

}
