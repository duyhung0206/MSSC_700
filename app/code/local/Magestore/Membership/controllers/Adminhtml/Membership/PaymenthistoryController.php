<?php

class Magestore_Membership_Adminhtml_Membership_PaymenthistoryController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('membership/paymenthistory')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Payment History'), Mage::helper('adminhtml')->__('Payment History'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function gridAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('membership/adminhtml_paymenthistory_grid')->toHtml());
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('membership/package')->load($id);

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

    public function exportCsvAction() {
        $fileName = 'paymenthistory.csv';
        $content = $this->getLayout()->createBlock('membership/adminhtml_paymenthistory_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'paymenthistory.xml';
        $content = $this->getLayout()->createBlock('membership/adminhtml_paymenthistory_grid')
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
    
    public function groupsAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('membership/adminhtml_paymenthistory_edit_tab_group')->toHtml());
    }
    
    public function productsAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('membership/adminhtml_paymenthistory_edit_tab_product')->toHtml());
    }
    
    public function membersAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('membership/adminhtml_paymenthistory_edit_tab_member')->toHtml());
    }
	
	protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('membership/paymenthistory');
    }

}
