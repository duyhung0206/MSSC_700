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
class Magestore_Customercredit_Block_Order_Creditmemo_Totals extends Mage_Core_Block_Template
{

    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $creditmemo = $totalsBlock->getCreditmemo();
        if ($creditmemo->getCustomercreditDiscount() > 0.0001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code' => $this->getCode(),
                'label' => $this->__('Customer Credit'),
                'value' => -$creditmemo->getCustomercreditDiscount(),
                'base_value' => -$creditmemo->getBaseCustomercreditDiscount(),
                )), 'subtotal');
        }
    }

}
