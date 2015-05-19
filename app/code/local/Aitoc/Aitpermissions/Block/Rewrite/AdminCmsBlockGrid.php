<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCmsBlockGrid.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ IppacokcTkMpEjZP('4c5a6bca6863ef5e22b2f1ae1f268134'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminCmsBlockGrid extends Mage_Adminhtml_Block_Cms_Block_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cms/block')->getCollection();
        /* @var $collection Mage_Cms_Model_Mysql4_Block_Collection */

        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            $collection->addStoreFilter($AllowedStoreviews);
        }
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
} } 