<?php
class Magestore_Membership_PlanController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }
	
	public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }
	
	public function packageAction()
    {
		$this->loadLayout(); 
		$this->renderLayout();
    }
	
	/*
		add product to cart
	     @param: 
		 - id: package_id
	*/
	public function renewAction()
    {	
		$id = $this->getRequest()->getParam('id');		
		$package = Mage::getModel('membership/package')->load($id);		
		// $this->_redirect('checkout/cart/add', array('product'=>$package->getProductId()));		
		$block = Mage::getBlockSingleton('catalog/product_list');
		$this->_redirectUrl($block->getAddToCartUrl(Mage::getModel('catalog/product')->load($package->getProductId())));
    }//end renewAction
}