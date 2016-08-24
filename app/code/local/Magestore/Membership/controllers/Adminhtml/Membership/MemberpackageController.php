<?php

class Magestore_Membership_Adminhtml_Membership_MemberpackageController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('membership/memberpackage')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Member Package'), Mage::helper('adminhtml')->__('Member Package'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function gridAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('membership/adminhtml_memberpackage_grid')->toHtml());
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
	
	protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('membership/memberpackage');
    }

}
