<?php

class Magestore_Membership_Adminhtml_Membership_MemberController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('membership/member')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Members Manager'), Mage::helper('adminhtml')->__('Member Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function gridAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('membership/adminhtml_member_grid')->toHtml());
    }
    
    public function customerGridAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('membership/adminhtml_member_edit_tab_customergrid')->toHtml());
    }

    public function packageAction() {
        $memberId = $this->getRequest()->getParam('id');
        Mage::helper('membership')->setStatusMemberPackage($memberId);
        $this->loadLayout();
        $this->getLayout()->getBlock('member.edit.tab.package')
                ->setPackages($this->getRequest()->getPost('opackage', null));
        $this->renderLayout();
    }

    public function packageGridAction() {
        $memberId = $this->getRequest()->getParam('id');
        Mage::helper('membership')->setStatusMemberPackage($memberId);
        $this->loadLayout();
        $this->getLayout()->getBlock('member.edit.tab.package')
                ->setPackages($this->getRequest()->getPost('opackage', null));
        $this->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('membership/member')->load($id);

        if ($model->getId() || $id == 0) {
            if(!$this->getRequest()->getParam('customer_id')){
                $this->getRequest()->setParam('customer_id', $model->getCustomerId());
            }
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('member_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('membership/member');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Member Manager'), Mage::helper('adminhtml')->__('Member Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Member News'), Mage::helper('adminhtml')->__('Member News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('membership/adminhtml_member_edit'))
                    ->_addLeft($this->getLayout()->createBlock('membership/adminhtml_member_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('membership')->__('Member does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            if(!$data['customer_id']){
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                Mage::getSingleton('adminhtml/session')->addError('Please choose a customer!');
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
            $status = $data['status'];
            //$customer = Mage::helper('membership')->getCustomerByEmail($data['member_email']);
            $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
            if (!$customer || !$customer->getId()) {
                $customer = Mage::helper('membership')->createCustomer($data);
            }
            //list information of customer at Model 'customer'	
            $member = array(
                'member_name' => $customer->getName(),
                'member_email' => $customer->getEmail(),
                'customer_id' => $customer->getId(),
                'status' => $status
            );
            $model = Mage::getModel('membership/member');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->setData($member)
                        ->setId($id);
            } else {
                $member ['joined_time'] = date('Y-m-d H:i:s');
                $model->setData($member);
            }
            try {
                $model->save();
                $id = $model->getId();
                if (isset($data['member_package'])) {
                    $packages = array();
                    parse_str($data['member_package'], $packages);
                    $packages = array_keys($packages);
                } else {
                    $packages = null;
                }
                if ($packages && isset($packages)) {
                    Mage::helper('membership')->addPackageToMemeberbyAdmin($packages, $id);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('membership')->__('Member was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(),'customer_id' => $data['customer_id']));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('membership')->__('Unable to find member to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('membership/member');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Member was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $memberIds = $this->getRequest()->getParam('member');
        if (!is_array($memberIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select member(s)'));
        } else {
            try {
                foreach ($memberIds as $memberId) {
                    $member = Mage::getModel('membership/member')->load($memberId);
                    $member->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($memberIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $memberIds = $this->getRequest()->getParam('member');
        if (!is_array($memberIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select member(s)'));
        } else {
            try {
                foreach ($memberIds as $memberId) {
                    $member = Mage::getSingleton('membership/member')
                            ->load($memberId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($memberIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'member.csv';
        $content = $this->getLayout()->createBlock('membership/adminhtml_member_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'member.xml';
        $content = $this->getLayout()->createBlock('membership/adminhtml_member_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('membership/member');
    }

}
