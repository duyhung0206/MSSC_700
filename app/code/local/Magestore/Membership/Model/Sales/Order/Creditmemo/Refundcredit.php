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
class Magestore_Membership_Model_Sales_Order_Creditmemo_Refundcredit extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{

    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $refund = $creditmemo->getOrder()->getRefundcreditAmount();
        $baserefund = $creditmemo->getOrder()->getBaseRefundcreditAmount();

        $creditmemo->setRefundcreditAmount($refund);
        $creditmemo->setBaseRefundcreditAmount($baserefund);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal());

        return $this;
    }


}

?>