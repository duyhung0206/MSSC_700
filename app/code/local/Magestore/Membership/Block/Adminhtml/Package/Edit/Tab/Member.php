<?php

class Magestore_Membership_Block_Adminhtml_Package_Edit_Tab_Member extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('memberGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setDefaultFilter(array('in_members' => 1));
        //$this->setSaveParametersInSession(true);
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_members') {
            $memberIds = $this->_getSelectedMembers();
            if (empty($memberIds)) {
                $memberIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('member_id', array('in' => $memberIds));
            } elseif (!empty($memberIds)) {
                $this->getCollection()->addFieldToFilter('member_id', array('nin' => $memberIds));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('membership/member')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('in_members', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'in_members',
            'align' => 'center',
            'index' => 'member_id',
            'values' => $this->_getSelectedMembers(),
        ));

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

        $this->addColumn('member_status', array(
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

    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/membersGrid', array('_current' => true, 'id' => $this->getRequest()->getParam('id')));
    }

    protected function _getSelectedMembers() {
        $members = $this->getMembers();
        if (!is_array($members)) {
            $members = array_keys($this->getSelectedMembers());
        }

        return $members;
    }

    public function getSelectedMembers() {
        $collection = Mage::getModel('membership/memberpackage')->getCollection()
                ->addFieldToFilter('package_id', $this->getRequest()->getParam('id'));
        foreach ($collection as $item) {
            $memberIds[$item->getMemberId()] = array('position' => 0);
        }
        return $memberIds;
    }

}
