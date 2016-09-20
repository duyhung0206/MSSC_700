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
class Magestore_Membership_Model_Sales_Order_Invoice_Discount extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $discount = 0;
        $basediscount = 0;
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy())
                continue;

            $discountitem = Mage::getModel('sales/quote_item_option')->getCollection()
                ->addFieldToFilter('item_id', $orderItem->getQuoteItemId())
                ->addFieldToFilter('code', 'discount')->getFirstItem()->getValue();
            $qty = Mage::getModel('sales/quote_item_option')->getCollection()
                ->addFieldToFilter('item_id', $orderItem->getQuoteItemId())
                ->addFieldToFilter('code', 'qty_exchange')->getFirstItem()->getValue();
            $discount += $discountitem / $qty * $item->getQty();
        }
        $basediscount = $discount;

        $invoice->setDiscountexchangeAmount($discount);
        $invoice->setBaseDiscountexchangeAmount($basediscount);

        $invoice->setGrandTotal($invoice->getGrandTotal() - $discount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $basediscount);

        return $this;
    }

}
