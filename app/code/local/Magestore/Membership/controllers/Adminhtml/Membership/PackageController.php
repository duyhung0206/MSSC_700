<?php

class Magestore_Membership_Adminhtml_Membership_PackageController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('membership/package')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Packages Manager'), Mage::helper('adminhtml')->__('Package Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function gridAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('membership/adminhtml_package_grid')->toHtml());
    }

    public function groupsAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('package.edit.tab.group')
                ->setGroups($this->getRequest()->getPost('ogroup', null))
        ;
        $this->renderLayout();
    }

    public function groupsGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('package.edit.tab.group')
                ->setGroups($this->getRequest()->getPost('ogroup', null))

        ;
        $this->renderLayout();
    }

    public function productsAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('package.edit.tab.product')
                ->setProducts($this->getRequest()->getPost('oproduct', null))
        ;
        $this->renderLayout();
    }

    public function productsGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('package.edit.tab.product')
                ->setProducts($this->getRequest()->getPost('oproduct', null))
        ;
        $this->renderLayout();
    }

    public function membersAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('package.edit.tab.member')
                ->setMembers($this->getRequest()->getPost('omember', null))
        ;
        $this->renderLayout();
    }

    public function membersGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('package.edit.tab.member')
                ->setMembers($this->getRequest()->getPost('omember', null))
        ;
        $this->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store');
        $model = Mage::getModel('membership/package')->setStoreId($storeId)->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('package_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('membership/package');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Package Manager'), Mage::helper('adminhtml')->__('Package Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Package News'), Mage::helper('adminhtml')->__('Package News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('membership/adminhtml_package_edit'))
                    ->_addLeft($this->getLayout()->createBlock('membership/adminhtml_package_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('membership')->__('Package does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->editAction();
    }

    public function saveAction() {
        $packageId = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store');
        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['package_member'])) {
                $members = array();
                parse_str($data['package_member'], $members);
                $members = array_keys($members);
            } else {
                $members = null;
            }

            if (isset($data['package_group'])) {
                $groupIds = array();
                parse_str($data['package_group'], $groupIds);
                $groupIds = array_keys($groupIds);
            } else {
                $groupIds = array(0);
            }

            //get productIds
            $productIds = array();
            if (isset($data['product_select_all'])) {
                if ($data['product_select_all'] == '1') {
                    $productCollection = Mage::getModel('catalog/product')
                            ->getCollection()
                            ->addFieldToFilter('status', 1)
                            ->addAttributeToSelect('*');
                    $productIds = $productCollection->getAllIds();
                } elseif ($data['product_select_all'] == '0') {
                    $productIds = array();
                } else {
                    if (isset($data['package_product'])) {
                        parse_str($data['package_product'], $productIds);
                        $productIds = array_keys($productIds);
                    } else {
                        $productIds = array(0);
                    }
                }
            } else {
                if (isset($data['package_product'])) {
                    parse_str($data['package_product'], $productIds);
                    $productIds = array_keys($productIds);
                } else {
                    $productIds = array(0);
                }
            }
            
            $packageModel = Mage::getModel('membership/package');
            $packageModel->setData($data)
                    ->setStoreId($storeId)
                    ->setId($packageId);
            if ($packageId) {
                $productId = Mage::getModel('membership/package')->load($packageId)->getProductId();
            }
            if ($data['package_name_default']) {
                if ($data['package_name_default'] == 1) {
                    $data['package_name'] = Mage::getModel('membership/package')->load($packageId)->getPackageName();
                } else {
                    $data['package_name'] = $data['package_name_default'];
                }
            }
            if ($data['description_default']) {
                if ($data['description_default'] == 1) {
                    $data['description'] = Mage::getModel('membership/package')->load($packageId)->getDescription();
                } else {
                    $data['description'] = $data['description_default'];
                }
            }

            //create membership product
            $productId = Mage::helper('membership')->createMembershipProduct($data['package_name'], $data['description'], $data['package_price'], $data['package_status'], $productId, $storeId);

            if ($productId)
                $packageModel->setProductId($productId);
            else {
                $this->_redirect('*/*/');
                return;
            }

            try {
                $packageModel->save();
                //print_r($packageModel);die();
                if ($members && isset($members)) {
                    Mage::helper('membership')->setMemberByAdmin($members, $packageModel);
                }
                Mage::helper('membership')->assignGroupIds($packageModel, $groupIds);
                Mage::helper('membership')->assignProductIdsToPackage($packageModel, $productIds);
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('membership')->__('Package was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $packageModel->getId(), 'store' => $storeId));
                    return;
                }
                $this->_redirect('*/*/', array('store' => $storeId));
                return;
            } catch (Exception $e) {
                if(!$packageId){
                    if($productId){
                        $product = Mage::getModel('catalog/product')
                            ->load($productId);
                        $product->delete();
                    }
                }
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('membership')->__('Unable to find package to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('membership/package')
                    ->load($this->getRequest()->getParam('id'));
                $product = Mage::getModel('catalog/product')
                    ->load($model->getProductId());
                if($model->getUrlKey()){
                    $url_key = Mage::getModel('core/url_rewrite')->getCollection()
                        ->addFieldToFilter('store_id', 0)
                        ->addFieldToFilter('id_path', $model->getUrlKey())
                        ->addFieldToFilter('request_path', $model->getUrlKey(). '.html')
                        ->getFirstItem();
                }
                $model->delete();
                $product->delete();
                if(isset($url_key)&&$url_key->getId()){
                    $url_key->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Package was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $packageIds = $this->getRequest()->getParam('package');
        if (!is_array($packageIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Package(s)'));
        } else {
            try {
                foreach ($packageIds as $packageId) {
                    $package = Mage::getModel('membership/package')->load($packageId);
                    $product = Mage::getModel('catalog/product')
                        ->load($package->getProductId());
                    if($package->getUrlKey()){
                        $url_key = Mage::getModel('core/url_rewrite')->getCollection()
                            ->addFieldToFilter('store_id', 0)
                            ->addFieldToFilter('id_path', $package->getUrlKey())
                            ->addFieldToFilter('request_path', $package->getUrlKey(). '.html')
                            ->getFirstItem();
                    }
                    $package->delete();
                    $product->delete();
                    if(isset($url_key)&&$url_key->getId()){
                        $url_key->delete();
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($packageIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $storeId = $this->getRequest()->getParam('store');
        $packageIds = $this->getRequest()->getParam('package');
        if (!is_array($packageIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select package(s)'));
        } else {
            try {
                foreach ($packageIds as $packageId) {
                    $package = Mage::getSingleton('membership/package')
                            ->load($packageId)
                            ->setPackageStatus($this->getRequest()->getParam('package_status'))
                            ->setStoreId($storeId)
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($packageIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index', array('store' => $storeId));
    }

    public function exportCsvAction() {
        $fileName = 'package.csv';
        $content = $this->getLayout()->createBlock('membership/adminhtml_package_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'package.xml';
        $content = $this->getLayout()->createBlock('membership/adminhtml_package_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
	
	protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('membership/package');
    }

}
