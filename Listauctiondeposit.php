<?php

class Magestore_Auction_Block_Adminhtml_Productauction_Edit_Tab_Listauctiondeposit extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('listauctiondepositGrid');
        $this->setDefaultSort('auctiondeposit_id');
        $this->setDefaultDir('DESC');
		$this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('auction/deposit')->getCollection()
                ->addFieldToFilter('productauction_id', $this->getRequest()->getParam('id'));
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        
        $this->addColumn('auctiondeposit_id', array(
            'header' => Mage::helper('auction')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'auctiondeposit_id',
        ));
		
		$this->addColumn('product_name', array(
            'header' => Mage::helper('auction')->__('Product Name'),
            'align' => 'left',
            'index' => 'product_name',
            'renderer' => 'auction/adminhtml_productauction_renderer_product',
        ));

        $this->addColumn('customer_name', array(
            'header' => Mage::helper('auction')->__('Customer Name'),
            'align' => 'left',
            'index' => 'customer_name',
            'renderer' => 'auction/adminhtml_productauction_renderer_customer',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('auction')->__('Status'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
			 1 => 'Pending',
			 2 => 'Approved',
			 ),
        ));
		
		$this->addColumn('action', array(
            'header' => Mage::helper('auction')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('auction')->__('Change Status'),
                    'url' => array('base' => '*/*/changedepositStatus'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));
		
        // $this->addExportType('*/*/exportDepositCsv', Mage::helper('auction')->__('CSV'));
        // $this->addExportType('*/*/exportDepositXml', Mage::helper('auction')->__('XML'));

        return parent::_prepareColumns();
    }

    // protected function _prepareMassaction() {
        // $this->setMassactionIdField('auctiondeposit_id');
        // $this->getMassactionBlock()->setFormFieldName('auctiondeposit');

       // /*mass change status*/
		 // $statuses = array(
		 // 1 => Mage::helper('auction')->__('Pending'),
		 // 2 => Mage::helper('auction')->__('Approved')
		 // );
		 // array_unshift($statuses, array('label' => '', 'value' => ''));
		 // $this->getMassactionBlock()->addItem('status', array(
		 // 'label' => Mage::helper('auction')->__('Change status'),
		 // 'url' => $this->getUrl('*/*/massdepositStatus', array('_current' => true)),
		 // 'additional' => array(
		 // 'visibility' => array(
		 // 'name' => 'status',
		 // 'type' => 'select',
		 // 'class' => 'required-entry',
		 // 'label' => Mage::helper('auction')->__('Status'),
		 // 'values' => $statuses
		 // ))
		 // ));

        // return $this;
    // }
	// protected function getAdditionalJavascript() {
       // return 'window.listauctiondepositGrid_massactionJsObject = listauctiondepositGrid_massactionJsObject;';
	// //'window.{gridId}_massactionJsObject = {gridId}_massactionJsObject;';
    // }

    
    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/depositlist', array('_current' => true));
    }

    protected function _afterLoadCollection() {
        if ($this->_isExport) {
        }
    }

}
