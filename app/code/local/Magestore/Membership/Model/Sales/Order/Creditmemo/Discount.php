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
class Magestore_Membership_Model_Sales_Order_Creditmemo_Discount extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{

    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $discount = 0;
        $basediscount = 0;
        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy())
                continue;
            die($discount);
            $discount += (float)$orderItem->getDiscountexchangeAmount();
            $basediscount += (float)$orderItem->getBaseDiscountexchangeAmount();
        }
        $discount = $creditmemo->getOrder()->getDiscountexchangeAmount();
        $basediscount = $creditmemo->getOrder()->getBaseDiscountexchangeAmount();

        $creditmemo->setDiscountexchangeAmount($discount);
        $creditmemo->setBaseDiscountexchangeAmount($basediscount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $discount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $basediscount);

        return $this;
    }


}

?>