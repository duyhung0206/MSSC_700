<?php

class Magestore_Membership_Block_Adminhtml_Paymenthistory_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('paymenthistoryGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('membership/paymenthistory')->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('payment_history_id', array(
            'header' => Mage::helper('membership')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'payment_history_id',
        ));

        $this->addColumn('member_id', array(
            'header' => Mage::helper('membership')->__('Member ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'member_id',
        ));

        $this->addColumn('package_name', array(
            'header' => Mage::helper('membership')->__('Package Name'),
            'align' => 'left',
            'index' => 'package_name',
        ));

        $this->addColumn('price', array(
            'header' => Mage::helper('membership')->__('Price'),
            'align' => 'left',
            'index' => 'price',
            'type' => 'price',
            'width' => '100px',
            'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
        ));


        $this->addColumn('duration', array(
            'header' => Mage::helper('membership')->__('Duration'),
            'align' => 'center',
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

        $this->addColumn('start_time', array(
            'header' => Mage::helper('membership')->__('Start Time'),
            'align' => 'right',
            'width' => '150px',
            'index' => 'start_time',
        ));

        $this->addColumn('end_time', array(
            'header' => Mage::helper('membership')->__('End Time'),
            'align' => 'right',
            'width' => '150px',
            'index' => 'end_time',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('membership')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getMemberId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('membership')->__('View Member'),
                    'url' => array('base' => '*/membership_member/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));


        //$this->addExportType('*/*/exportCsv', Mage::helper('membership')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('membership')->__('XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid');
    }

}
