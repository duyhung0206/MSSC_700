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
//            $discount += (float)$orderItem->getDiscountexchangeAmount();
//            $basediscount += (float)$orderItem->getBaseDiscountexchangeAmount();


//            $quoteItem = Mage::getModel('sales/quote_item')->load($orderItem->getQuoteItemId());
            Zend_debug::dump($item->getData());
            Zend_debug::dump($orderItem->getData());
        }
//        $discount = $invoice->getOrder()->getDiscountexchangeAmount();
//        $basediscount = $invoice->getOrder()->getBaseDiscountexchangeAmount();

        $invoice->setDiscountexchangeAmount($discount);
        $invoice->setBaseDiscountexchangeAmount($basediscount);

        $invoice->setGrandTotal($invoice->getGrandTotal() - $discount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $basediscount);

        return $this;
    }

}
