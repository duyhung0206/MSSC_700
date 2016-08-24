<?php

class Magestore_Membership_Model_Observer {
    /*
      Create member or add new member package after the order is completed
      This function is called when the event sales_order_save_after occur.
     */

    public function sales_order_save_after($observer) {
        //get the current order
        $order = $observer->getEvent()->getOrder();
        //get the customer id in the order
        $customerId = $order->getCustomerId();

        if (!$customerId)
            return;

        $helper = Mage::helper('membership');
        //check if the customer is enabled or not.
        if (!$helper->getMemberStatus($customerId))//disabled
            return;

        $orderStateActivePackage = Mage::getStoreConfig('membership/general/active_package_when_state_order');
        $orderStateUpdatePackage = Mage::getStoreConfig('membership/general/update_package_when_state_order');
   
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $productId = $orderItem->getProductId();
            $packageId = $helper->getPackageFromMembershipProduct($productId)->getId();
            $memberId = $helper->addMember($customerId);
            if ($packageId) {
                $helper->addPaymentHistory($memberId, $packageId, $order->getId());
            }
            //in this case, customer buy a new package. 
            if ($order->getStatus() == $orderStateActivePackage) {
                if ($packageId) {
                    $helper->addPackageToMember($memberId, $packageId, $order->getId());
                }
            }

            //in this case, customer buy a normal product of a membership package.
            if ($order->getStatus() == $orderStateUpdatePackage) {
                $memberPackages = $helper->isProductDiscount($customerId, $productId);
                if (count($memberPackages) > 0) {

                    $memberPackages = $helper->getCurrentPackagesFromProductId($customerId, $productId);
                    //find a package with min final price
                    $minPrice = -1;
                    foreach ($memberPackages as $item) {
                        $package = Mage::getModel('membership/package')->load($item->getPackageId());
                        $membershipPrice = $helper->getMembershipPrice($productId, $package);
                        if ($minPrice == -1) {
                            $minPrice = $membershipPrice;
                            $memberPackage = $item;
                        } else if ($membershipPrice <= $minPrice) {
                            $minPrice = $membershipPrice;
                            $memberPackage = $item;
                        }
                    }//end foreach($memberPackages as $item)	
                    $saved = Mage::getModel('catalog/product')->load($productId)->getPrice() - $minPrice;
                    $memberPackage->setBoughtItemTotal($memberPackage->getBoughtItemTotal() + $orderItem->getQtyOrdered())
                            ->setSavedTotal($memberPackage->getSavedTotal() + $saved*$orderItem->getQtyOrdered())
                            ->setStatus(1);
                    try {
                        $memberPackage->save();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
    }

    //sales_order_save_after($observer)

    public function catalog_product_get_final_price($observer) {
        $finalPrices = array();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId)
            return;

        $product = $observer['product'];

        if (!Mage::helper('membership')->getMemberStatus($customerId))//disabled
            return;

        $memberPackages = Mage::helper('membership')->isProductDiscount($customerId, $product->getId());
        if (count($memberPackages) == 0)
            return;

        foreach ($memberPackages as $memberPackage) {
            $package = Mage::getModel('membership/package')->load($memberPackage->getPackageId());

            $finalPrices[] = Mage::helper('membership')->getMembershipPrice($product->getId(), $package);
        }

        sort($finalPrices, SORT_NUMERIC);
        $product->setData('final_price', $finalPrices[0]);
    }

    public function catalog_product_collection_load_after($observer) {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId)
            return;

        if (!Mage::helper('membership')->getMemberStatus($customerId))//disabled
            return;

        $collection = $observer['collection'];
        foreach ($collection as $product) {
            $finalPrices = array();
            $memberPackages = Mage::helper('membership')->isProductDiscount($customerId, $product->getId());
            if (count($memberPackages) == 0)
                continue;

            foreach ($memberPackages as $memberPackage) {
                $package = Mage::getModel('membership/package')->load($memberPackage->getPackageId());
                $finalPrices[] = Mage::helper('membership')->getMembershipPrice($product->getId(), $package);
            }

            sort($finalPrices, SORT_NUMERIC);
            $product->setData('final_price', $finalPrices[0]);
        }
    }

    public function customer_save_after($observer) {
        $customer = $observer['customer'];
        $member = Mage::getModel('membership/member')->getCollection()
                ->addFieldToFilter('customer_id', $customer->getId())
                ->getFirstItem();
        if (!$member->getId())
            return;
        $member->setMemberName($customer->getName())
                ->setMemberEmail($customer->getEmail());
        try {
            $member->save();
        } catch (Exception $e) {
            
        }
    }

    public function autopayment() {
        
    }

    /*
      this function runs on cron job.
     */

    public function noticeStatusPackage() {

        //update member package status
        $collection = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('end_time', array('datetime' => true, 'to' => now()));
        if (count($collection)) {
            foreach ($collection as $memberpackage) {
                $memberpackage->updatePackageStatus();
                if ($memberpackage->getStatus() == Magestore_Membership_Model_Memberpackage::STATUS_WARNING) {
                    Mage::helper('membership/email')->sendEmailNotifyRenewPackage($memberpackage);
                }
            }
        }
    }

    public function sales_order_creditmemo_save_after($observer) {
        $creditmemo = $observer['creditmemo'];
        if (!$customerId = $creditmemo->getCustomerId())
            return;

        $helper = Mage::helper('membership');
        if (!$memberId = $helper->getMemberId($customerId))
            return;

        foreach ($creditmemo->getAllItems() as $item) {
            $package = $helper->getPackageFromPackageProductId($item->getProductId());
            if ($packageId = $package->getId()) {
                $memberPackage = $helper->getMemberpackage($memberId, $packageId);
                if ($memberPackage->getBoughtItemTotal()) {
                    Mage::getSingleton('core/session')->addError($helper->__('Cannot refund a membership product if there is any other relevant purchase made with a discounted price under the same membership level!'));
                    throw new Exception($helper->__('Cannot refund a membership product if there is any other relevant purchase made with a discounted price under the same membership level!'));
                } else {
                    $endTime = date('Y-m-d H:i:s', strtotime($memberPackage->getEndTime() . '-' . $package->getDuration() . ' ' . $package->getUnitOfTime() . 's'));
                    $memberPackage->setEndTime($endTime);
                    $memberPackage->updatePackageStatus();
                    $memberPackage->save();
                }
            }
        }
    }

}
