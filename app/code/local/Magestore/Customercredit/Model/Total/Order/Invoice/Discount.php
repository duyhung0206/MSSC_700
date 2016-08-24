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
class Magestore_Customercredit_Model_Total_Order_Invoice_Discount extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if ($order->getCustomercreditDiscount() < 0.0001) {
            return;
        }

        $invoice->setBaseCustomercreditDiscount(0);
        $invoice->setCustomercreditDiscount(0);

        $totalDiscountInvoiced = 0;
        $totalBaseDiscountInvoiced = 0;

        $totalDiscountAmount = 0;
        $totalBaseDiscountAmount = 0;

        $totalHiddenTax = 0;
        $totalBaseHiddenTax = 0;

        $hiddenTaxInvoiced = 0;
        $baseHiddenTaxInvoiced = 0;
        $checkAddShipping = true;

        foreach ($order->getInvoiceCollection() as $previousInvoice) {
            if ($previousInvoice->getCustomercreditDiscount()) {
                $checkAddShipping = false;
                $totalBaseDiscountInvoiced += $previousInvoice->getBaseCustomercreditDiscount();
                $totalDiscountInvoiced += $previousInvoice->getCustomercreditDiscount();

                $hiddenTaxInvoiced += $previousInvoice->getCustomercreditHiddenTax();
                $baseHiddenTaxInvoiced += $previousInvoice->getBaseCustomercreditHiddenTax();
            }
        }

        if ($checkAddShipping) {
            $totalBaseDiscountAmount += $order->getBaseCustomercreditDiscountForShipping();
            $totalDiscountAmount += $order->getCustomercreditDiscountForShipping();

            $totalBaseHiddenTax += $order->getBaseCustomercreditShippingHiddenTax();
            $totalHiddenTax += $order->getCustomercreditShippingHiddenTax();
        }

        if ($invoice->isLast()) {
            $totalBaseDiscountAmount = $order->getBaseCustomercreditDiscount() - $totalBaseDiscountInvoiced;
            $totalDiscountAmount = $order->getCustomercreditDiscount() - $totalDiscountInvoiced;

            $totalHiddenTax = $order->getCustomercreditHiddenTax() - $hiddenTaxInvoiced;
            $totalBaseHiddenTax = $order->getBaseCustomercreditHiddenTax() - $baseHiddenTaxInvoiced;
        } else {
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $baseOrderItemCustomercreditDiscount = (float) $orderItem->getBaseCustomercreditDiscount();
                $orderItemCustomercreditDiscount = (float) $orderItem->getCustomercreditDiscount();

                $baseOrderItemHiddenTax = (float) $orderItem->getBaseCustomercreditHiddenTax();
                $orderItemHiddenTax = (float) $orderItem->getCustomercreditHiddenTax();

                $orderItemQty = $orderItem->getQtyOrdered();
                $invoiceItemQty = $item->getQty();

                if ($baseOrderItemCustomercreditDiscount && $orderItemQty) {
                    if (version_compare(Mage::getVersion(), '1.7.0.0', '>=')) {
                        $totalBaseDiscountAmount += $invoice->roundPrice($baseOrderItemCustomercreditDiscount / $orderItemQty * $invoiceItemQty, 'base', true);
                        $totalDiscountAmount += $invoice->roundPrice($orderItemCustomercreditDiscount / $orderItemQty * $invoiceItemQty, 'regular', true);

                        $totalHiddenTax += $invoice->roundPrice($orderItemHiddenTax / $orderItemQty * $invoiceItemQty, 'regular', true);
                        $totalBaseHiddenTax += $invoice->roundPrice($baseOrderItemHiddenTax / $orderItemQty * $invoiceItemQty, 'base', true);
                    } else {
                        $totalBaseDiscountAmount += $baseOrderItemCustomercreditDiscount / $orderItemQty * $invoiceItemQty;
                        $totalDiscountAmount += $orderItemCustomercreditDiscount / $orderItemQty * $invoiceItemQty;

                        $totalHiddenTax += $orderItemHiddenTax / $orderItemQty * $invoiceItemQty;
                        $totalBaseHiddenTax += $baseOrderItemHiddenTax / $orderItemQty * $invoiceItemQty;
                    }
                }
            }
        }
        $invoice->setBaseCustomercreditDiscount($totalBaseDiscountAmount);
        $invoice->setCustomercreditDiscount($totalDiscountAmount);

        $invoice->setBaseCustomercreditHiddenTax($totalBaseHiddenTax);
        $invoice->setCustomercreditHiddenTax($totalHiddenTax);

        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $totalBaseDiscountAmount + $totalBaseHiddenTax);
        $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount + $totalHiddenTax);
    }

}
