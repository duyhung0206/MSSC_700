<?php

class Magestore_Membership_Block_Adminhtml_Package_Edit_Tab_Group extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('list_group_grid');
        $this->setDefaultSort('group_id');
        $this->setUseAjax(true);
        if ($this->getPackage() && $this->getPackage()->getId()) {
            $this->setDefaultFilter(array('in_groups' => 1));
        }
    }

    /* Ham nay co tac dung de search theo tung truong mot */

    protected function _addColumnFilterToCollection($column) {
        // Set custom filter for in group flag
        if ($column->getId() == 'in_groups') {
            $groupIds = $this->_getSelectedGroups();
            if (empty($groupIds)) {
                $groupIds = array(0);
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('group_id', array('in' => $groupIds));
            } else {
                if ($groupIds) {
                    $this->getCollection()->addFieldToFilter('group_id', array('nin' => $groupIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    //return category collection filtered by store
    protected function _prepareCollection() {
        $collection = Mage::getModel('membership/group')
                ->getCollection()
        ;

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('in_groups', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_groups',
            'align' => 'center',
            'index' => 'group_id',
            'values' => $this->_getSelectedGroups(),
        ));

        $this->addColumn('group_id', array(
            'header' => Mage::helper('membership')->__('ID'),
            'sortable' => true,
            'width' => '60px',
            'index' => 'group_id'
        ));
        $this->addColumn('group_name', array(
            'header' => Mage::helper('membership')->__('Name'),
            'index' => 'group_name'
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
        
        $this->addColumn('position', array(
            'header' => Mage::helper('membership')->__(''),
            'name' => 'position',
            'index' => 'position',
            'width' => 0,
            'editable' => true,
            'filter' => false,
        ));

        return parent::_prepareColumns();
    }

    protected function _getSelectedGroups() {
        $groups = $this->getGroups();
        if (!is_array($groups)) {
            $groups = array_keys($this->getSelectedGroups());
        }
        return $groups;
    }

    public function getSelectedGroups() {
        $groups = array();
        $groupIds = $this->getPackage()->getGroupIds();
        if (count($groupIds)) {
            foreach ($groupIds as $groupId) {
                $groups[$groupId] = array('position' => 0);
            }
        }
        return $groups;
    }

    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/groupsGrid', array('_current' => true, 'id' => $this->getRequest()->getParam('id')));
    }

    //return Magestore_Membership_Model_Package
    public function getPackage() {
        return Mage::getModel('membership/package')
                        ->load($this->getRequest()->getParam('id'));
    }

}
