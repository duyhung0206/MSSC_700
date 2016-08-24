<?php

class Magestore_Membership_Block_Adminhtml_Member_Edit_Tab_Customergrid extends Mage_Adminhtml_Block_Widget_Grid {
    
    protected $_customerId;

    public function __construct() {
        parent::__construct();
        $this->setId('customerGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id');
        if($this->_customerId = Mage::app()->getRequest()->getParam('customer_id'))
            $collection->getSelect()->order('(e.entity_id = '.$this->_customerId.') DESC');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('customer_id', array(
            'header' => Mage::helper('membership')->__('ID'),
            'align' => 'right',
            'width' => '40px',
            'index' => 'entity_id',
            'type'  =>  'radio',
            'value' => Mage::app()->getRequest()->getParam('customer_id'),
            'html_name'  => 'customer_id'
        ));
        
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('membership')->__('ID'),
            'align' => 'right',
            'width' => '40px',
            'index' => 'entity_id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('membership')->__('Customer Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('email', array(
            'header' => Mage::helper('membership')->__('Email'),
            'align' => 'left',
            'index' => 'email',
        ));

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header'    =>  Mage::helper('customer')->__('Group'),
            'width'     =>  '100',
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));
        
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }

        $this->addColumn('action', array(
            'header' => Mage::helper('membership')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getEntityId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('membership')->__('View customer'),
                    'url' => array('base' => 'adminhtml/customer/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));
        
        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/customerGrid', array('customer_id' => $this->_customerId));
    }

}
