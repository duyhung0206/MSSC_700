<?php

class Magestore_Membership_Adminhtml_Membership_GroupController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('membership/group')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Groups Manager'), Mage::helper('adminhtml')->__('Group Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function gridAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('membership/adminhtml_group_grid')->toHtml());
    }

    public function productsAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('group.edit.tab.product')
                ->setProducts($this->getRequest()->getPost('oproduct', null))
        ;
        $this->renderLayout();
    }

    public function productsGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('group.edit.tab.product')
                ->setProducts($this->getRequest()->getPost('oproduct', null))
        ;

        $this->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $groupModel = Mage::getModel('membership/group')->load($id);

        if ($groupModel->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $groupModel->setData($data);
            }

            //print_r($data);die();
            Mage::register('group_data', $groupModel);

            $this->loadLayout();
            $this->_setActiveMenu('membership/group');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Group Manager'), Mage::helper('adminhtml')->__('Group Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Group News'), Mage::helper('adminhtml')->__('Group News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('membership/adminhtml_group_edit'))
                    ->_addLeft($this->getLayout()->createBlock('membership/adminhtml_group_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('membership')->__('Group does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['group_product'])) {
                $productIds = array();
                parse_str($data['group_product'], $productIds);
                $productIds = array_keys($productIds);
            } else {
                $productIds = array(0);
            }
            //print_r($data);die();
            $groupModel = Mage::getModel('membership/group');
            $groupModel->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {
                $groupModel->save();
                Mage::helper('membership')->assignProductIdsToGroup($groupModel, $productIds);

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('membership')->__('Group was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $groupModel->getId()));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('membership')->__('Unable to find group to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $groupModel = Mage::getModel('membership/group');

                $groupModel->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Group was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $groupIds = $this->getRequest()->getParam('group');
        if (!is_array($groupIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select group(s)'));
        } else {
            try {
                foreach ($groupIds as $groupId) {
                    $group = Mage::getModel('membership/group')->load($groupId);
                    $group->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($groupIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $groupIds = $this->getRequest()->getParam('group');
        if (!is_array($groupIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Group(s)'));
        } else {
            try {
                foreach ($groupIds as $groupId) {
                    $group = Mage::getSingleton('membership/group')
                            ->load($groupId)
                            ->setGroupStatus($this->getRequest()->getParam('group_status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($groupIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
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
        return Mage::getSingleton('admin/session')->isAllowed('membership/group');
    }

}
