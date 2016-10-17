<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Magestore_Membership_Model_Pdf extends Mage_Sales_Model_Order_Pdf_Total_Default
{

    public function getTotalsForDisplay()
    {
        $invoiceId = Mage::app()->getRequest()->getParam('invoice_id');
        $creditmemoId = Mage::app()->getRequest()->getParam('creditmemo_id');
        $fontSize = $this->getFontSize() ? $this->getFontSize() : $this->getDefaultFontSize();
        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            $discountexchange = $this->getOrder()->formatPriceTxt($invoice->getDiscountexchangeAmount());
            $refundcredit = $this->getOrder()->formatPriceTxt($invoice->getRefundcreditAmount());
            $fee = $this->getOrder()->formatPriceTxt($invoice->getFeeAmount());
        } else if ($creditmemoId) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
            $discountexchange = $this->getOrder()->formatPriceTxt($creditmemo->getDiscountexchangeAmount());
            $fee = $this->getOrder()->formatPriceTxt($creditmemo->getFeeAmount());
        } else {
            $discountexchange = $this->getOrder()->formatPriceTxt($this->getOrder()->getDiscountexchangeAmount());
            $refundcredit = $this->getOrder()->formatPriceTxt($this->getOrder()->getRefundcreditAmount());
            $fee = $this->getOrder()->formatPriceTxt($this->getOrder()->getFeeAmount());
        }

        if ($this->getAmountPrefix()) {
            $discountexchange = $this->getAmountPrefix() . $discountexchange;
            $refundcredit = $this->getAmountPrefix() . $refundcredit;
            $fee = $this->getAmountPrefix() . $fee;
        }
        $total = array(array(
                'label' => 'Discount exchange',
                'amount' => '-' . $this->getAmountPrefix() . $discountexchange,
                'font_size' => $fontSize,
        ),
            array(
                'label' => 'Refund credit',
                'amount' => $this->getAmountPrefix() . $refundcredit,
                'font_size' => $fontSize,
            ),
            array(
                'label' => 'Fee',
                'amount' => $this->getAmountPrefix() . $fee,
                'font_size' => $fontSize,
            ));

        return $total;
    }

    public function getDefaultFontSize(){
        return Mage::getStoreConfig('customercredit/style_management/default_font_size');
    }

}
