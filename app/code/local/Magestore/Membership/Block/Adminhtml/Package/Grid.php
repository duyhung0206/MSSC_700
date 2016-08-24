<?php

class Magestore_Membership_Block_Adminhtml_Package_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('packageGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $storeId = $this->getRequest()->getParam('store');
        $collection = Mage::getModel('membership/package')
                ->getCollection()
                ->setStoreId($storeId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('package_id', array(
            'header' => Mage::helper('membership')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'package_id',
        ));

        $this->addColumn('package_name', array(
            'header' => Mage::helper('membership')->__('Name'),
            'align' => 'left',
            'index' => 'package_name'
        ));

        $this->addColumn('package_price', array(
            'header' => Mage::helper('membership')->__('Package Price'),
            'align' => 'left',
            'index' => 'package_price',
            'type' => 'price',
            'width' => '100px',
            'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
        ));
        $this->addColumn('discount_type', array(
            'header' => Mage::helper('membership')->__('Discount Type'),
            'align' => 'left',
            'index' => 'discount_type',
            'type' => 'options',
            'width' => '150px',
            'options' => Magestore_Membership_Model_Package_Discounttype::getOptions()
        ));
        $this->addColumn('package_product_price', array(
            'header' => Mage::helper('membership')->__('Discount Value'),
            'align' => 'left',
            'index' => 'package_product_price',
           // 'renderer' => 'membership/adminhtml_package_renderer_price',
            'width' => '50px',
        ));
        
        $this->addColumn('custom_option_discount', array(
            'header' => Mage::helper('membership')->__('Discount for Product Custom Option'),
            'align' => 'left',
            'width' => '230',
            'index' => 'custom_option_discount',
        ));

        $this->addColumn('duration', array(
            'header' => Mage::helper('membership')->__('Duration'),
            'align' => 'left',
            'index' => 'duration',
            'width' => '50px',
        ));
        
        $this->addColumn('unit_of_time', array(
            'header' => Mage::helper('membership')->__('Unit of Time'),
            'align' => 'left',
            'index' => 'unit_of_time',
            'type' => 'options',
            'width' => '100px',
            'options' => Magestore_Membership_Model_Package_Unitoftime::getOptions()
        ));

        $this->addColumn('sort_order', array(
            'header' => Mage::helper('membership')->__('Sort Order'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'sort_order',
        ));

        $this->addColumn('package_status', array(
            'header' => Mage::helper('membership')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'package_status',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('membership')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('membership')->__('Edit'),
                    'url' => array('base' => '*/*/edit/store/' . $this->getRequest()->getParam('store')),
                    'field' => 'id',
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'package',
            'is_system' => true,
        ));

        //$this->addExportType('*/*/exportCsv', Mage::helper('membership')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('membership')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('package_id');
        $this->getMassactionBlock()->setFormFieldName('package');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('membership')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('membership')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('membership/status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('package_status', array(
            'label' => Mage::helper('membership')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'package_status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('membership')->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId(), 'store' => $this->getRequest()->getParam('store')));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('store' => $this->getRequest()->getParam('store')));
    }

}
