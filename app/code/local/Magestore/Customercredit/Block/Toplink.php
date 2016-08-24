<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Customercredit Block
 * 
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @author      Magestore Developer
 */
class Magestore_Customercredit_Block_Toplink extends Mage_Core_Block_Template
{

    public function addTopLinkStores()
    {
        if ((Mage::helper('customercredit')->getGeneralConfig('enable') == 1)) {
            $toplinkBlock = $this->getParentBlock();
            if ($toplinkBlock) {
                $toplinkBlock->addLink(Mage::helper('customercredit')->__('%s Buy Store Credit', $this->getIconImage()), 'customercredit/index/list', 'Buy customercredit', true, array(), 10);
            }
        }
    }

    public function getIconImage()
    {
        if (Mage::getVersion() < '1.9.0.0') {
            return Mage::helper('customercredit')->getIconImage();
        } else {// Edit by Crystal
            return '<span style="display: inline-block;margin-top: 1px;margin-right: 22px;"><img style="position: absolute;margin-top: -15px" src="' . Mage::getDesign()->getSkinUrl('images/customercredit/point.png') . '" /></span>';
        }
    }

}
