<?php

class Magestore_Membership_Block_Adminhtml_Member_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('memberGrid');
        $this->setDefaultSort('member_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('membership/member')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('member_id', array(
            'header' => Mage::helper('membership')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'member_id',
        ));

        $this->addColumn('member_name', array(
            'header' => Mage::helper('membership')->__('Name'),
            'align' => 'left',
            'index' => 'member_name',
        ));

        $this->addColumn('member_email', array(
            'header' => Mage::helper('membership')->__('Email'),
            'align' => 'left',
            'index' => 'member_email',
        ));


        $this->addColumn('joined_time', array(
            'header' => Mage::helper('membership')->__('Joined Date'),
            'width' => '150px',
            'index' => 'joined_time',
            'type' => 'date',
        ));


        $this->addColumn('status', array(
            'header' => Mage::helper('membership')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
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
            'getter' => 'getCustomerId',
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

        //$this->addExportType('*/*/exportCsv', Mage::helper('membership')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('membership')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('member_id');
        $this->getMassactionBlock()->setFormFieldName('member');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('membership')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('membership')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('membership/status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('membership')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
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
        return $this->getUrl('*/*/edit', array('id' => $row->getId(), 'customer_id' => $row->getCustomerId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid');
    }

}
