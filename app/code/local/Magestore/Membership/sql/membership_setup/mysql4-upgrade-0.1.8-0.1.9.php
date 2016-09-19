<?php
$installer = $this;
$installer->startSetup();
$installer->run("
  DROP TABLE IF EXISTS {$this->getTable('membership_group_price')};
CREATE TABLE {$this->getTable('membership_group_price')} (
 `group_price_id` int(10) unsigned NOT NULL auto_increment,
 `group_from` int(10) unsigned NOT NULL,
 `group_to` int(10) unsigned NOT NULL,  
 `price` float NOT NULL default '0.0000',
 
 FOREIGN KEY (`group_from`) REFERENCES {$this->getTable('membership_group')} (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 FOREIGN KEY (`group_to`) REFERENCES {$this->getTable('membership_group')} (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 PRIMARY KEY (`group_price_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");


$groups = Mage::getModel('membership/group')->getCollection();

if (count($groups)) {
	foreach ($groups as $group) {
		
		$model = Mage::getModel('membership/groupprice');             
		$model->setData('group_from', $group->getGroupId());
		$model->setData('group_to', $group->getGroupId());
		$model->setData('price', 0);
		$model->save();
		
	}
	$firstAsc = Mage::getModel('membership/group')->getCollection()
								->setOrder('group_id','ASC')
								->getFirstItem();
	$firstAscId = $firstAsc->getGroupId();
	$collections = Mage::getModel('membership/group')->getCollection()
			->addFieldToFilter('group_id',array('nin'=>$firstAscId));
	foreach($collections as $collection){
		$model = Mage::getModel('membership/groupprice');
		$model->setData('group_from',$firstAscId);
		$model->setData('group_to',$collection->getGroupId());
		$model->setData('price', 0);
		$model->save();
	}
	foreach($collections as $collection){
		$model = Mage::getModel('membership/groupprice');
		$model->setData('group_to',$firstAscId);
		$model->setData('group_from',$collection->getGroupId());
		$model->setData('price', 0);
		$model->save();
	}
	$firstDesc = Mage::getModel('membership/group')->getCollection()
								->setOrder('group_id','DESC')
								->getFirstItem();
	$firstDescId = $firstDesc->getGroupId();
	$colls = Mage::getModel('membership/group')->getCollection()
			->addFieldToFilter('group_id',array('nin'=>array($firstAscId , $firstDescId)));
	foreach($colls as $coll){
		$model = Mage::getModel('membership/groupprice');
		$model->setData('group_from',$firstDescId);
		$model->setData('group_to',$coll->getGroupId());
		$model->setData('price', 0);
		$model->save();
	}	
	foreach($colls as $coll){
		$model = Mage::getModel('membership/groupprice');
		$model->setData('group_to',$firstDescId);
		$model->setData('group_from',$coll->getGroupId());
		$model->setData('price', 0);
		$model->save();
	}
	
}
$installer->endSetup();
?>