<?php

class Magestore_Membership_Block_Adminhtml_Member_Edit_Tab_Package extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('packageGrid');
        $this->setDefaultSort('package_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setDefaultFilter(array('in_packages' => 1));
        //$this->setSaveParametersInSession(true);	  
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_packages') {
            $packageIds = $this->_getSelectedPackages();
            if (empty($packageIds)) {
                $packageIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.package_id', array('in' => $packageIds));
            } elseif (!empty($packageIds)) {
                $this->getCollection()->addFieldToFilter('main_table.package_id', array('nin' => $packageIds));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection() {
        $memberId = $this->getRequest()->getParam('id');
        $collection = Mage::helper('membership')->getJoinToPackage($memberId);
        //Zend_debug::dump($collection->getData());die();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('in_packages', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'in_packages',
            'align' => 'center',
            'index' => 'package_id',
            'values' => $this->_getSelectedPackages(),
        ));

        $this->addColumn('package_id', array(
            'header' => Mage::helper('membership')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'package_id',
        ));

        $this->addColumn('package_name', array(
            'header' => Mage::helper('membership')->__('Name'),
            'align' => 'left',
            'index' => 'package_name',
        ));

        $this->addColumn('package_price', array(
            'header' => Mage::helper('membership')->__('Price'),
            'align' => 'left',
            'index' => 'package_price',
            'type' => 'price',
            'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
        ));


        $this->addColumn('package_end_time', array(
            'header' => Mage::helper('membership')->__('End Time'),
            'align' => 'left',
            'index' => 'end_time',
            'type' => 'datetime',
        ));


        $this->addColumn('bought_item_total', array(
            'header' => Mage::helper('membership')->__('Purchased Items'),
            'align' => 'left',
            'index' => 'bought_item_total',
        ));
        $this->addColumn('saved_total', array(
            'header' => Mage::helper('membership')->__('Saved Total'),
            'align' => 'left',
            'index' => 'saved_total',
            'type' => 'price',
            'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
        ));

        $this->addColumn('package_status', array(
            'header' => Mage::helper('membership')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
                3 => 'Expired',
                4 => 'Waiting'
            ),
        ));
        
        $this->addColumn('position', array(
            'header'            => Mage::helper('membership')->__('Position'),
            'name'              => 'position',		  
            'index'             => 'position',
            'width'             => 0,
            'editable'          => true,
            'filter'		=> false,
	 ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/adminhtml_package/edit', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/packageGrid', array('_current' => true, 'id' => $this->getRequest()->getParam('id')));
    }

    protected function _getSelectedPackages() {
        $packages = $this->getPackages();
        if (!is_array($packages)) {
            $packages = array_keys($this->getSelectedPackages());
        }
        return $packages;
    }

    public function getSelectedPackages() {
        $collection = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('member_id', $this->getRequest()->getParam('id'));
        foreach ($collection as $item) {
            $packageIds[$item->getPackageId()] = array('position' => 0);
        }
        return $packageIds;
    }

}
