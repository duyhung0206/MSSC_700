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
class Magestore_Customercredit_Model_Total_Order_Creditmemo_Discount extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{

    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getCustomercreditDiscount() < 0.0001) {
            return;
        }
//        echo $creditmemo->getBaseShippingAmount();
        $creditmemo->setBaseCustomercreditDiscount(0);
        $creditmemo->setCustomercreditDiscount(0);

        $totalDiscountAmount = 0;
        $baseTotalDiscountAmount = 0;

        $hiddenTaxDiscount = 0;
        $baseHiddenTaxDiscount = 0;

        $totalDiscountRefunded = 0;
        $baseTotalDiscountRefunded = 0;

        $hiddenTaxRefunded = 0;
        $baseHiddenTaxRefunded = 0;

        foreach ($order->getCreditmemosCollection() as $existedCreditmemo) {
            if ($existedCreditmemo->getCustomercreditDiscount()) {
                $baseTotalDiscountRefunded += $existedCreditmemo->getBaseCustomercreditDiscount();
                $totalDiscountRefunded += $existedCreditmemo->getCustomercreditDiscount();

                $baseHiddenTaxRefunded += $existedCreditmemo->getBaseCustomercreditHiddenTax();
                $hiddenTaxRefunded += $existedCreditmemo->getCustomercreditHiddenTax();
            }
        }

        $baseShippingAmount = $creditmemo->getBaseShippingAmount();
        if ($baseShippingAmount) {
            $baseTotalDiscountAmount += $baseShippingAmount * $order->getBaseCustomercreditDiscountForShipping() / $order->getBaseShippingAmount();
            $totalDiscountAmount += $order->getShippingAmount() * $baseTotalDiscountAmount / $order->getBaseShippingAmount();

            $baseHiddenTaxDiscount += $baseShippingAmount * $order->getBaseCustomercreditShippingHiddenTax() / $order->getBaseShippingAmount();
            $hiddenTaxDiscount += $order->getShippingAmount() * $baseHiddenTaxDiscount / $order->getBaseShippingAmount();
        }

        if ($this->isLast($creditmemo)) {
            $baseTotalDiscountAmount = $order->getBaseCustomercreditDiscount() - $baseTotalDiscountRefunded;
//            echo $baseTotalDiscountRefunded;
            $totalDiscountAmount = $order->getCustomercreditDiscount() - $totalDiscountRefunded;

            $baseHiddenTaxDiscount = $order->getBaseCustomercreditHiddenTax() - $baseHiddenTaxRefunded;
            $hiddenTaxDiscount = $order->getCustomercreditHiddenTax() - $hiddenTaxRefunded;
        } else {
            foreach ($creditmemo->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy())
                    continue;

                $orderItemBaseDiscount = (float) $orderItem->getBaseCustomercreditDiscount();
                $orderItemDiscount = (float) $orderItem->getCustomercreditDiscount();

                $orderItemBaseHiddenTax = (float) $orderItem->getBaseCustomercreditHiddenTax();
                $orderItemHiddenTax = (float) $orderItem->getCustomercreditHiddenTax();

                $orderItemQty = $orderItem->getQtyOrdered();
                $refundItemQty = $item->getQty();
                if ($orderItemDiscount && $orderItemQty) {
                    if (version_compare(Mage::getVersion(), '1.7.0.0', '>=')) {
                        $totalDiscountAmount += $creditmemo->roundPrice($refundItemQty * $orderItemBaseDiscount / $orderItemQty, 'regular', true);
                        $baseTotalDiscountAmount += $creditmemo->roundPrice($refundItemQty * $orderItemDiscount / $orderItemQty, 'base', true);

                        $hiddenTaxDiscount += $creditmemo->roundPrice($refundItemQty * $orderItemHiddenTax / $orderItemQty, 'regular', true);
                        $baseHiddenTaxDiscount += $creditmemo->roundPrice($refundItemQty * $orderItemBaseHiddenTax / $orderItemQty, 'base', true);
                    } else {
                        $totalDiscountAmount += $refundItemQty * $orderItemBaseDiscount / $orderItemQty;
                        $baseTotalDiscountAmount += $refundItemQty * $orderItemDiscount / $orderItemQty;

                        $hiddenTaxDiscount += $refundItemQty * $orderItemHiddenTax / $orderItemQty;
                        $baseHiddenTaxDiscount += $refundItemQty * $orderItemBaseHiddenTax / $orderItemQty;
                    }
                }
            }
        }
        $creditmemo->setBaseCustomercreditDiscount($baseTotalDiscountAmount);
        $creditmemo->setCustomercreditDiscount($totalDiscountAmount);

        $creditmemo->setBaseCustomercreditHiddenTax($baseHiddenTaxDiscount);
        $creditmemo->setCustomercreditHiddenTax($hiddenTaxDiscount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $hiddenTaxDiscount - $totalDiscountAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseHiddenTaxDiscount - $baseTotalDiscountAmount);

        $creditmemo->setAllowZeroGrandTotal(true);
    }

    /**
     * check credit memo is last or not
     * 
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return boolean
     */
    public function isLast($creditmemo)
    {
        foreach ($creditmemo->getAllItems() as $item) {
            if (!$item->isLast()) {
                return false;
            }
        }
        return true;
    }
}

?>