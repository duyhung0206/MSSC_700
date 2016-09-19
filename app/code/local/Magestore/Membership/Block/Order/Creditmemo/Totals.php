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
 * Customercredit Block
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @author      Magestore Developer
 */
class Magestore_Membership_Block_Order_Creditmemo_Totals extends Mage_Core_Block_Template
{

    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $creditmemo = $totalsBlock->getCreditmemo();
        if ($creditmemo->getDiscountexchangeAmount() > 0.0001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code' => 'discount_amount',
                'label' => $this->__('Discount amount'),
                'value' => -$creditmemo->getDiscountexchangeAmount(),
                'base_value' => -$creditmemo->getDiscountexchangeAmount(),
            )), 'subtotal');
        }

        if ($creditmemo->getFeeAmount() > 0.0001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code' => 'fee',
                'label' => $this->__('Fee'),
                'value' => $creditmemo->getFeeAmount(),
                'base_value' => $creditmemo->getBaseFeeAmount(),
            )), 'subtotal');
        }

        if ($creditmemo->getRefundcreditAmount() > 0.0001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code' => 'refund_credit',
                'label' => $this->__('Refundcredit'),
                'value' => $creditmemo->getRefundcreditAmount(),
                'base_value' => $creditmemo->getBaseRefundcreditAmount(),
            )), 'subtotal');
        }

    }

}
