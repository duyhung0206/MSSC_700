<?php

class Magestore_Membership_ExchangeproductController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function exchangeAction()
    {
        $data = $this->getRequest()->getPost();
        $productBoughtId = $data['group-radio-product-boughts'];
        $productExchangeId = $data['group-radio-product-exchange'];
        $maxQtyBought = $data['number-product-bought-' . $productBoughtId];
        $qtyExchange = $data['number-exchange-' . $productExchangeId];
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (isset($productBoughtId) && isset($productExchangeId)) {
            if (isset($maxQtyBought) && isset($qtyExchange)) {

                if ($maxQtyBought < $qtyExchange) {
                    Mage::getSingleton('core/session')->addError(Mage::helper('membership')->__('Error exchange product'));
                    $this->_redirect('*/*/index');
                    return;
                }
                //add to cart
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                if (!$quote->getId() || $quote->getId() <= 0) {
                    $quote = Mage::getModel('sales/quote')->assignCustomer(Mage::getModel('customer/customer')->load($customerId));
                    $quote->setStoreId(Mage::app()->getStore()->getStoreId());
                } else {
                    $items = $quote->getAllItems();
                    foreach ($items as $item) {
                        $proExch = $item->getOptionByCode('product_exchange_id');
                        $proBou = $item->getOptionByCode('product_bought_id');
                        if ($proExch != null && $proExch->getValue() > 0 || $proBou != null && $proBou->getValue() > 0) {
                            if ($proExch->getValue() == $productExchangeId || $proBou->getValue() == $productBoughtId) {
                                Mage::getSingleton('core/session')->addError(Mage::helper('membership')->__('Exchange product exist'));
                                $this->_redirect('checkout/cart');
                                return;
                            }
                        }
                    }
                }

                try {
                    $productExchange = Mage::getModel('catalog/product')->load($productExchangeId);
                    $productBought = Mage::getModel('catalog/product')->load($productBoughtId);

                    $priceExchange = Mage::helper('membership')->getDiscountPrice($customerId, $productExchangeId);
                    $priceBought = Mage::helper('membership')->getDiscountPrice($customerId, $productBoughtId);

                    $customer = Mage::getSingleton('customer/session')->getCustomer();
                    $storeId = $customer->getStoreId();
                    $quoteItem = Mage::getModel('sales/quote_item')->setProduct($productExchange);
                    Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

                    $quoteItem->setStoreId($storeId);
                    $quoteItem->setCustomPrice($qtyExchange * $priceExchange);
                    $quoteItem->setOriginalCustomPrice($priceExchange);
                    $quoteItem->addOption(array(
                        'label' => 'Product exchange',
                        'code' => 'product_exchange_id',
                        'value' => $productExchangeId,
                    ));
                    $quoteItem->addOption(array(
                        'label' => 'Product bought',
                        'code' => 'product_bought_id',
                        'value' => $productBoughtId,
                    ));
                    $quoteItem->addOption(array(
                        'label' => 'Qty',
                        'code' => 'qty_exchange',
                        'value' => $qtyExchange,
                    ));
                    $fee = 69;
                    $discount = $priceBought;
                    if ($discount > $priceExchange) {

                        $storecredit = ($discount - $priceExchange) * $qtyExchange;
                        $discount = $priceExchange;
                        $quoteItem->addOption(array(
                            'label' => 'Discount Exchange',
                            'code' => 'discount',
                            'value' => $discount * $qtyExchange,
                        ));
                        $quoteItem->addOption(array(
                            'label' => 'Refund credit',
                            'code' => 'refund_credit',
                            'value' => $storecredit,
                        ));
                    }
                    if ($discount <= $priceExchange) {
                        $quoteItem->addOption(array(
                            'label' => 'Discount Exchange',
                            'code' => 'discount',
                            'value' => $discount * $qtyExchange,
                        ));
                    }

                    $quoteItem->addOption(array(
                        'label' => 'Fee Exchange',
                        'code' => 'fee',
                        'value' => $fee,
                    ));

                    $quoteItem->setQty($qtyExchange);
                    $quoteItem->getProduct()->setIsSuperMode(true);
                    Mage::getSingleton('core/session')->setData('checkout_exchange', true);
                    $quote->addItem($quoteItem);

                    $quote->collectTotals();
                    $quote->save();
                    Mage::helper('membership')->updateFeeCart($quote);

                    Mage::getSingleton('core/session')->addSuccess(Mage::helper('membership')->__('Check out success !'));
                    $this->_redirect('checkout/cart');
                    return;
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->addError(Mage::helper('membership')->__('Error'));
                    $this->_redirect('*/*/index');
                    return;
                }


            }
            Mage::getSingleton('core/session')->addError(Mage::helper('membership')->__('Error exchange product'));
        } else {
            Mage::getSingleton('core/session')->addError(Mage::helper('membership')->__('Error exchange product'));
        }

        $this->_redirect('*/*/index');
    }

    public function testAction()
    {
        $customerId = 140;
        $productId = 409;
        $memberId = Mage::helper('membership')->getMemberId($customerId);
        if (!$memberId)
            echo "false";
        Mage::helper('membership')->setStatusMemberPackage($memberId);


        if ($productId == null) {
            $collection = Mage::getModel('membership/groupproduct')->getCollection();
        } else {
            $collection = Mage::getModel('membership/groupproduct')->getCollection()
                ->addFieldToFilter('product_id', $productId);
        }
        $collection->getSelect()->join(
            'membership_group',
            'main_table.group_id = membership_group.group_id && membership_group.group_status = 1',
            array('group_name')
        );
        $collection->getSelect()->join(
            'membership_package_group',
            'main_table.group_id = membership_package_group.group_id',
            array('package_id')
        );
        $collection->getSelect()->join(
            'membership_member_package',
            'membership_package_group.package_id = membership_member_package.package_id
            && membership_member_package.status = 1
            && membership_member_package.member_id = ' . $memberId . '
            && membership_member_package.end_time > cast((now()) as date)',
            array('end_time')
        );
        if (!count($collection))
            echo "false";
        return $collection;
    }

    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

}