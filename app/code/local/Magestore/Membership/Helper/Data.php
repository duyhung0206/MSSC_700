<?php

class Magestore_Membership_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getMembershipUrl() {
        return $this->_getUrl('membership/index/index');
    }

    public function getReorderPackageUrl($membershippackage) {
        return $this->_getUrl('membership/mymembership/reorder', array('id' => $membershippackage->getId()));
    }

    public function getMembershipPackageIds($productId) {
        $packageIds = array();
        $groupIds = array(0);

        $collection = Mage::getModel('membership/packageproduct')->getCollection()
                ->addFieldToFilter('product_id', $productId);

        if (count($collection)) {
            foreach ($collection as $item) {
                $packageIds[] = $item->getPackageId();
            }
        }

        $collection = Mage::getModel('membership/groupproduct')->getCollection()
                ->addFieldToFilter('product_id', $productId);

        if (count($collection)) {
            foreach ($collection as $item) {
                $groupIds[] = $item->getGroupId();
            }
        }
        $collection = Mage::getModel('membership/packagegroup')->getCollection()
                ->addFieldToFilter('group_id', array('in' => $groupIds));

        if (count($collection)) {
            foreach ($collection as $item) {
                $packageIds[] = $item->getPackageId();
            }
        }
        return $packageIds;
    }

    public function isProductInMembership($productId) {
        $packageIds = $this->getMembershipPackageIds($productId);
        if (count($packageIds))
            return true;
        else
            return false;
    }

    public function getMembershipProductIds() {
        $productIds = array();
        $attrSetName = 'Membership';
        $attributeSetId = Mage::getModel('eav/entity_attribute_set')
                ->load($attrSetName, 'attribute_set_name')
                ->getAttributeSetId();

        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addFieldToFilter('attribute_set_id', $attributeSetId);
        if (count($collection)) {
            foreach ($collection as $item) {
                $productIds[] = $item->getId();
            }
        }
        return $productIds;
    }

    public function setStatusMemberPackage($memberId) {
        $collection = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('member_id', $memberId);

        $days = Mage::getStoreConfig('membership/general/days_active_package_after_expired');
        if (count($collection)) {
            foreach ($collection as $item) {
                if ($item->getStatus() == 3) {
                    $time = strtotime(now()) - $days * 24 * 60 * 60;
                    if ($item->getEndTime() <= date('Y-m-d H:i:s', $time)) {
                        try {
                            $item->setStatus(2)->save();
                        } catch (Exception $e) {
                            
                        }
                    }
                } elseif ($item->getStatus() == 1) {
                    if ($item->getEndTime() <= now()) {
                        try {
                            if ($item->getIsRenew())
                                $item->setStatus(2)->save();
                            else
                                $item->setStatus(3)->save();
                        } catch (Exception $e) {
                            
                        }
                    }
                } elseif ($item->getStatus() == 4) {
                    if ($item->getStartTime() <= now()) {
                        try {
                            $item->setStatus(1)->save();
                        } catch (Exception $e) {
                            
                        }
                    }
                }
            }
        }
    }

    public function isProductDiscount($customerId, $productId) {
        $packages = array();
        $memberId = $this->getMemberId($customerId);
        if (!$memberId)
            return $packages;
        $this->setStatusMemberPackage($memberId);
        $collection = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('member_id', $memberId)
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('end_time', array('datetime' => true, 'from' => now()));
        if (!count($collection))
            return $packages;
        foreach ($collection as $item) {
            $package = Mage::getModel('membership/package')->load($item->getPackageId());
            $productIds = $package->getAllProductIds();
            if (in_array($productId, $productIds))
                $packages[] = $item;
        }
        return $packages;
    }

    /*
      get all active packages of a member
     */

    public function getCurrentPackagesFromProductId($customerId, $productId) {
        $packages = array();
        $memberId = $this->getMemberId($customerId);
        $collection = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('member_id', $memberId)
                ->addFieldToFilter('end_time', array('datetime' => true, 'from' => now()));

        foreach ($collection as $item) {
            $package = Mage::getModel('membership/package')->load($item->getPackageId());
            $productIds = $package->getAllProductIds();
            if (in_array($productId, $productIds))
                $packages[] = $item;
        }
        return $packages;
    }

    public function isSignedUpPackage($memberId, $packageId) {
        $memberpackage = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('package_id', $packageId)
                ->addFieldToFilter('member_id', $memberId)
                ->setOrder('end_time', 'DESC')
                ->getFirstItem();
        if ($memberpackage->getId())
            return $memberpackage;
        else
            return null;
    }

    public function getMemberId($customerId) {
        $memberId = Mage::getModel('membership/member')
                ->load($customerId, 'customer_id')
                ->getId();

        return $memberId;
    }

    public function getMemberStatus($customerId) {
        $member = Mage::getModel('membership/member')->getCollection()
                ->addFieldToFilter('customer_id', $customerId);

        if (count($member)) {
            $member = $member->getFirstItem();
            return $member->IsEnable();
        }
        return true;
    }

    public function getGroupIdsFromProduct($productId) {
        $collection = Mage::getModel('membership/groupproduct')
                ->getCollection()
                ->addFieldToFilter('product_id', $productId);

        foreach ($collection as $item) {
            $groupIds[] = $item->getGroupId();
        }
        return $groupIds;
    }

    public function getGroupIdsFromPackage($packageId) {
        $collection = Mage::getModel('membership/packagegroup')
                ->getCollection()
                ->addFieldToFilter('package_id', $packageId);

        foreach ($collection as $item) {
            $groupIds[] = $item->getGroupId();
        }
        return $groupIds;
    }

    public function getPackageIds($productId) {

        $collection = Mage::getModel('membership/packageproduct')
                ->getCollection()
                ->addFieldToFilter('product_id', $productId);
        foreach ($collection as $item) {
            $packageIds[] = $item->getPackageId();
        }
        return $packageIds;
    }

    public function getMembershipPrice($productId, $package) {
        $packageId = $package->getId();
        $result_price = 0;
        $base_price = Mage::getModel('catalog/product')->load($productId)->getPrice();
        $productPrice = $package->getPackageProductPrice();
        $packageModel = Mage::getModel('membership/package')->getCollection()
                ->addFieldToSelect('package_id', 'package_id')
                ->addFieldToSelect('package_product_price', 'package_product_price')
                ->addFieldToSelect('package_status', 'package_status')
                ->addFieldToSelect('discount_type', 'discount_type');
        $packageProduct = clone $packageModel->getSelect();
        $packageProduct->joinLeft(
                array('package_product' => Mage::getSingleton("core/resource")->getTableName('membership_package_product')), 'main_table.package_id=package_product.package_id 
            WHERE main_table.package_id = ' . $packageId . ' 
            AND package_product.product_id= ' . $productId, 'package_product.product_id as product_id'
        );
        $groupProduct = clone $packageModel->getSelect();
        $groupProduct->joinLeft(
                array('package_group' => Mage::getSingleton("core/resource")->getTableName('membership_package_group')), 'main_table.package_id=package_group.package_id ', ''
        )->joinLeft(
                array('group_product' => Mage::getSingleton("core/resource")->getTableName('membership_group_product')), 'package_group.group_id=group_product.group_id WHERE main_table.package_id = ' . $packageId . ' AND group_product.product_id = ' . $productId, 'group_product.product_id as product_id'
        );
        $packageModel->getSelect()->reset();
        $packageModel->getSelect()->union(array(
            new Zend_Db_Expr($packageProduct->__toString()),
            new Zend_Db_Expr($groupProduct->__toString())
        ));
        if (count($packageModel)) {
            $discountType = $package->getDiscountType();
            if ($discountType == Magestore_Membership_Model_Package_Discounttype::TYPE_FIXED)
                $result_price = $base_price - $productPrice;
            else if ($discountType == Magestore_Membership_Model_Package_Discounttype::TYPE_PERCENT)
                $result_price = $base_price - $base_price * floatval($productPrice) / 100;
            else
                $result_price = $productPrice;
        }
        return $result_price;
    }

    public function getProductDiscountPercent($productId, $package) {

        if ($package->getCustomOptionDiscount() == 'no') {
            return 1;
        }
        $groupIdsFromProduct = $this->getGroupIdsFromProduct($productId);
        $groupIdsFromPackage = $this->getGroupIdsFromPackage($package->getId());

        $groupIds = array_intersect($groupIdsFromPackage, $groupIdsFromProduct);

        if (count($groupIds)) {
            $discountType = $package->getDiscountType();
            if ($discountType == Magestore_Membership_Model_Package_Discounttype::TYPE_PERCENT)
                return floatval($package->getPackageProductPrice()) / 100;
            else
                return 1;
        }
    }

    public function getPackageFromMembershipProduct($productId) {
        $package = Mage::getModel('membership/package')->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->getFirstItem();
        return $package;
    }

    //public function get
    //if joined return memberId
    public function isJoinMembership($customerId) {
        $memberId = Mage::getModel('membership/member')->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->getFirstItem()
                ->getId();
        if ($memberId)
            return $memberId;
        else
            return false;
    }

    public function addMember($customerId) {
        $memberId = $this->isJoinMemberShip($customerId);
        if (!$memberId) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $member = Mage::getModel('membership/member')
                    ->setCustomerId($customerId)
                    ->setMemberName($customer->getName())
                    ->setMemberEmail($customer->getEmail())
                    ->setJoinedTime(now());
            try {
                $member->save();

                //Mage::helper('membership/email')->sendEmailToNewMember($member);
                return $member->getId();
            } catch (Exception $e) {
                return;
            }
        } else {
            return $memberId;
        }
    }

    /*
      add a package to member
      @params:
      - memberId: int, ID of member in the member table
      - packageId: int, ID of the package that need to add
      - orderId: int, ID of associated order
     */

    public function addPackageToMember($memberId, $packageId, $orderId) {
        try {
            //get the package from its ID
            $package = Mage::getModel('membership/package')->load($packageId);

            //add the package to member package table.
            //there are two cases:
            //			- customer is already bought this package: we just update the end time for current record
            //                        - this is the first time the customer bought this package: we insert new record.

            $memberPackage_collection = Mage::getModel('membership/memberpackage')->getCollection()
                    ->addFieldToFilter('member_id', $memberId)
                    ->addFieldToFilter('package_id', $packageId);

            if (count($memberPackage_collection)) { //the package is already bought
                $memberPackage = $memberPackage_collection->getFirstItem();
                $order_ids = $memberPackage->getData('order_ids');
                if ($order_ids) {
                    $order_ids_check = explode(',', $order_ids);
                }
                if (in_array($orderId, $order_ids_check)) {
                    return;
                }
                $order_ids = $order_ids . ',' . $orderId;
                if ($memberPackage->getEndTime() <= now()) { // the package is already expired
                    $memberPackage->setEndTime(now());
                }
                //update the end time
                $endTime = date('Y-m-d H:i:s', strtotime($memberPackage->getEndTime() . '+' . $package->getDuration() . ' ' . $package->getUnitOfTime() . 's'));
                $memberPackage->setEndTime($endTime);
                $memberPackage->setData('order_ids', $order_ids);
                $memberPackage->updatePackageStatus();
                $memberPackage->save();
            } else { //insert a new record
                $endTime = date('Y-m-d H:i:s', strtotime(now() . '+' . $package->getDuration() . ' ' . $package->getUnitOfTime() . 's'));
                $memberPackage = Mage::getModel('membership/memberpackage');
                $memberPackage->setPackageId($packageId);
                $memberPackage->setMemberId($memberId);
                $memberPackage->setEndTime($endTime);
                $memberPackage->setData('order_ids', $orderId);
                $memberPackage->updatePackageStatus();
                $memberPackage->save();
            }

            Mage::helper('membership/email')->sendEmailNewPackage($memberPackage);
        } catch (Exception $e) {
            echo($e->getMessage());
        }

        return;
    }

//end addPackageToMember($memberId, $packageId, $orderId)

    public function addPaymentHistory($memberId, $packageId, $orderId) {
        //get the package from its ID
        $package = Mage::getModel('membership/package')->load($packageId);

        //add the package to payment history table.
        $startTime = now();
        $endTime = date('Y-m-d H:i:s', strtotime($startTime . '+' . $package->getDuration() . ' ' . $package->getUnitOfTime() . 's'));
        $paymentHistory = Mage::getModel('membership/paymenthistory')->getCollection()
                ->addFieldToFilter('member_id', $memberId)
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('package_name', $package->getPackageName())
                ->getFirstItem();
        if ($paymentHistory->getId())
            return;

        $paymentHistory = Mage::getModel('membership/paymenthistory')
                ->setMemberId($memberId)
                ->setPackageName($package->getPackageName())
                ->setOrderId($orderId)
                ->setPrice($package->getPackagePrice())
                ->setDuration($package->getDuration())
                ->setUnitOfTime($package->getUnitOfTime())
                ->setStartTime($startTime)
                ->setEndTime($endTime)
                ->save();
    }

    public function assignProductIdsToGroup($group, $productIds) {
        $added_products = 0;
        $groupproductModel = Mage::getModel('membership/groupproduct');
        if ($productIds == array(0)) {
            return $this;
        }
        if (!count($productIds)) {
            $productIds = array(0);
        }

        foreach ($productIds as $key => $productId) {
            $productId = (int) $productId;
            if ($productId) {
                $groupproductModel->loadGroupProduct($group->getId(), $productId);
                if (!$groupproductModel->getId())
                    $added_products++;
                $groupproductModel->setGroupId($group->getId());
                $groupproductModel->setProductId($productId);
                $groupproductModel->save();
                $groupproductModel->setId(null);
            } else {
                unset($productIds[$key]);
            }
        }

        if (!count($productIds)) {
            $productIds = array(0);
        }

        $collection = Mage::getResourceModel('membership/groupproduct_collection')
                ->addFieldToFilter('product_id', array('nin' => $productIds))
                ->addFieldToFilter('group_id', $group->getId())
        ;

        if (count($collection)) {
            foreach ($collection as $item) {
                $item->delete();
                $added_referrals--;
            }
        }

        //$program->updateField('num_products',$added_products);

        return $this;
    }

    public function assignProductIdsToPackage($package, $productIds) {
        $added_products = 0;
        $packageproductModel = Mage::getModel('membership/packageproduct');
        if ($productIds == array(0)) {
            return $this;
        }
        if (!count($productIds)) {
            $productIds = array(0);
        }

        foreach ($productIds as $key => $productId) {
            $productId = (int) $productId;
            if ($productId) {
                $packageproductModel->loadPackageProduct($package->getId(), $productId);
                if (!$packageproductModel->getId())
                    $added_products++;
                $packageproductModel->setPackageId($package->getId());
                $packageproductModel->setProductId($productId);
                $packageproductModel->save();
                $packageproductModel->setId(null);
            } else {
                unset($productIds[$key]);
            }
        }

        if (!count($productIds)) {
            $productIds = array(0);
        }

        $collection = Mage::getResourceModel('membership/packageproduct_collection')
                ->addFieldToFilter('product_id', array('nin' => $productIds))
                ->addFieldToFilter('package_id', $package->getId())
        ;

        if (count($collection)) {
            foreach ($collection as $item) {
                $item->delete();
                $added_referrals--;
            }
        }

        //$program->updateField('num_products',$added_products);

        return $this;
    }

    public function assignGroupIds($package, $groupIds) {
        $added_groups = 0;
        $packagegroupModel = Mage::getModel('membership/packagegroup');
        if ($groupIds == array(0)) {
            return $this;
        }
        if (!count($groupIds)) {
            $groupIds = array(0);
        }

        foreach ($groupIds as $key => $groupId) {
            $groupId = (int) $groupId;
            if ($groupId) {
                $packagegroupModel->loadPackageGroup($package->getId(), $groupId);
                if (!$packagegroupModel->getId())
                    $added_groups++;
                $packagegroupModel->setPackageId($package->getId());
                $packagegroupModel->setGroupId($groupId);
                $packagegroupModel->save();
                $packagegroupModel->setId(null);
            } else {
                unset($groupIds[$key]);
            }
        }

        if (!count($groupIds)) {
            $groupIds = array(0);
        }

        $collection = Mage::getResourceModel('membership/packagegroup_collection')
                ->addFieldToFilter('group_id', array('nin' => $groupIds))
                ->addFieldToFilter('package_id', $package->getId())
        ;

        if (count($collection)) {
            foreach ($collection as $item) {
                $item->delete();
                $added_referrals--;
            }
        }

        //$program->updateField('num_groups',$added_groups);

        return $this;
    }

    public function createMembershipProduct($name, $description, $price, $status, $productId, $storeId) {
        if ($status == 1) {
            $status = 1;
        }
        if ($status == 2) {
            $status = 2;
        }
        if ($productId) {
            $product = Mage::getModel('catalog/product')->load($productId);
        } else {
            $product = Mage::getModel('catalog/product');
            $attributeSetName = 'Membership';
            $entityType = Mage::getSingleton('eav/entity_type')->loadByCode('catalog_product');
            $entityTypeId = $entityType->getId();
            $setId = Mage::getResourceModel('catalog/setup', 'core_setup')->getAttributeSetId($entityTypeId, $attributeSetName);
            $product->setAttributeSetId($setId);
            $product->setTypeId('virtual');
            $product->setSku('ms_' . $name);
            $product->setWebsiteIDs(array(1));

            //$product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            $product->setTaxClassId(0);
            $product->setStockData(array(
                'is_in_stock' => 1,
                'qty' => 99999
            ));
            $product->setCreatedAt(now());
        }
        $product->setStoreId($storeId);
        $product->setPrice($price);
        $product->setName($name);
        $product->setDescription($description);
        $product->setShortDescription($description);
        $product->setStatus($status);
        try {
            $product->save();
            return $product->getId();
        } catch (Exception $e) {
            //print_r($e);die();
            return;
        }
    }

    public function get_option_discount_percent($product) {
        if (!$product) {
            return -1;
        }
        $percents = array();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId)
            return -1;

        if (!Mage::helper('membership')->getMemberStatus($customerId))//disabled
            return -1;

        $memberPackages = Mage::helper('membership')->isProductDiscount($customerId, $product->getId());
        if (count($memberPackages)==0)
            return -1;

        foreach ($memberPackages as $memberPackage) {
            $package = Mage::getModel('membership/package')->load($memberPackage->getPackageId());
            $percents[] = $this->getProductDiscountPercent($product->getId(), $package);
        }

        sort($percents, SORT_NUMERIC);

        //die("percent=".$percents[0]);
        return $percents[0];
    }

    // fix by Hai.Ta

    public function getListMemberIds($packageId) {
        $memberIds = array();
        $collection = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('package_id', $packageId);
        foreach ($collection as $item) {
            $memberIds[] = $item->getMemberId();
        }
        return $memberIds;
    }

    public function setMemberByAdmin($memberIds, $package) {
        $packageId = $package->getId();
        $collection = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('package_id', $packageId);
        if (count($memberIds))
            $collection->addFieldToFilter('member_id', array('nin' => $memberIds));
        if (count($collection->getData())) {
            foreach ($collection as $item) {
                $item->delete();
            }
        }

        $listMemberIds = $this->getListMemberIds($packageId);
        if (count($memberIds)) {
            foreach ($memberIds as $memberId) {
                if (!in_array($memberId, $listMemberIds)) {
                    $duration = $package->getDuration();
                    $endTime = date('Y-m-d H:i:s', strtotime(now() . '+' . (int) $duration . ' ' . $package->getUnitOfTime() . 's'));
                    $model = Mage::getModel('membership/memberpackage');
                    $memberpackage = array(
                        'package_id' => $packageId,
                        'member_id' => $memberId,
                        'end_time' => $endTime,
                        'status' => '1'
                    );
                    $model->setData($memberpackage);
                    try {
                        $model->save();
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    }
                }
            }
        }
    }

    public function getCustomerByEmail($email) {
        return Mage::getResourceModel('customer/customer_collection')
                        ->addNameToSelect()
                        ->addAttributeToSelect('email')
                        ->addAttributeToFilter('email', array('in' => array($email)))
                        ->getFirstItem();
    }

    public function createCustomer($data) {
        $name = $this->editNameCustomer($data['member_name']);
        $customer = Mage::getModel('customer/customer')
                ->setFirstname($name['frist_name'])
                ->setLastname($name['last_name'])
                ->setEmail($data['member_email']);

        $newPassword = $customer->generatePassword();
        $customer->setPassword($newPassword);
        try {
            $customer->save();
        } catch (Exception $e) {
            
        }

        return $customer;
    }

    public function editNameCustomer($name) {
        $data = explode(" ", $name);
        $customerName = array();
        $customerName['frist_name'] = $data[0];
        $customerName['last_name'] = '';
        for ($i = 1; $i < count($data); $i++) {
            $customerName['last_name'] .= ' ' . $data[$i];
        }
        if ($customerName['last_name'] == '') {
            $customerName['last_name'] = $data[0];
        }
        return $customerName;
    }

    public function getJoinToPackage($id) {
        if ($id && isset($id)) {
            $collection = Mage::getResourceModel('membership/package_collection');
            $collection->getSelect()->joinLeft(array('c' => $collection->getTable('membership/memberpackage')), 'main_table.package_id = c.package_id and c.member_id = ' . $id, array('end_time', 'status', 'bought_item_total', 'saved_total'));
            return $collection;
        } else {
            $collection = Mage::getModel('membership/package')->getCollection();
            return $collection;
        }
    }

    public function getListPackageIds($memberId) {
        $packageIds = array();
        $collection = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('member_id', $memberId);
        foreach ($collection as $item) {
            $packageIds[] = $item->getPackageId();
        }
        return $packageIds;
    }

    public function addPackageToMemeberbyAdmin($packageIds, $memberId) {

        $collection = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('member_id', $memberId)
                ->addFieldToFilter('package_id', array(
            'nin' => $packageIds));
        // delete all package of member		
        if (count($collection->getData())) {
            foreach ($collection as $item) {
                $item->delete();
            }
        }
        $listPackageIds = $this->getListPackageIds($memberId);
        // then add package to member
        if (count($packageIds)) {
            foreach ($packageIds as $packageId) {
                if (!in_array($packageId, $listPackageIds)) {
                    $package = Mage::getModel('membership/package')->load($packageId);
                    $duration = $package->getDuration();
                    $endTime = date('Y-m-d H:i:s', strtotime(now() . '+' . $duration . ' ' . $package->getUnitOfTime() . 's'));
                    $model = Mage::getModel('membership/memberpackage');
                    $memberpackage = array(
                        'package_id' => $packageId,
                        'member_id' => $memberId,
                        'end_time' => $endTime,
                        'status' => '1'
                    );
                    $model->setData($memberpackage);
                    try {
                        $model->save();
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    }
                }
            }
        }
    }

    public function getPackageFromPackageProductId($productId){
        return Mage::getModel('membership/package')->load($productId, 'product_id');
    }
    
    public function getMemberpackage($memberId, $packageId){
        return Mage::getModel('membership/memberpackage')
                    ->getCollection()
                    ->addFieldToFilter('member_id', $memberId)
                    ->addFieldToFilter('package_id', $packageId)
                    ->getFirstItem();
    }
}
