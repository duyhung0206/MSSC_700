<?php

class Magestore_Membership_Block_Adminhtml_Memberpackage_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('MemberpackageGrid');
      $this->setDefaultSort('sort_order');
      $this->setDefaultDir('ASC');
	  $this->setUseAjax(true);
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getResourceModel('membership/memberpackage_collection')->getJoinedCollection();
	  $collection->setOrder('end_time','DESC');
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('member_id', array(
          'header'    => Mage::helper('membership')->__('Member ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'member_id',
      ));
	  
	 

      $this->addColumn('package_name', array(
          'header'    => Mage::helper('membership')->__('Name'),
          'align'     =>'left',
          'index'     => 'package_name',
      ));
	  
	  $this->addColumn('package_price', array(
          'header'    => Mage::helper('membership')->__('Price'),
          'align'     =>'left',
          'index'     => 'package_price',
		  'type'  => 'price',
		  'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),			  
      ));
	  
	 	 
	  $this->addColumn('end_time', array(
          'header'    => Mage::helper('membership')->__('End Time'),
          'align'     =>'left',
          'index'     => 'end_time',
		  'type'  => 'datetime',
      ));
	  
	 	 
	   $this->addColumn('bought_item_total', array(
          'header'    => Mage::helper('membership')->__('Purchased Items'),
          'align'     =>'left',
          'index'     => 'bought_item_total',
      ));
	  $this->addColumn('saved_total', array(
          'header'    => Mage::helper('membership')->__('Saved Total'),
          'align'     =>'left',
          'index'     => 'saved_total',
		  'type'  => 'price',
		  'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
      ));
	  
	  
	  
	  
	  
      $this->addColumn('status', array(
          'header'    => Mage::helper('membership')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
			  3 => 'Expired',
			  4 => 'Waiting'
          ),
      ));
	  
      $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('membership')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getMemberId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('membership')->__('View Member'),
                        'url'       => array('base'=> '*/adminhtml_member/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
	  
        
		//$this->addExportType('*/*/exportCsv', Mage::helper('membership')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('membership')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    

	public function getRowUrl($row)
	{
		return null;
	}

	public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }
}