<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCmsPageGrid.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ UqqrIpEIRygqPDaW('6e7e268236d38bb55a5eab75d086f3aa'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminCmsPageGrid extends Mage_Adminhtml_Block_Cms_Page_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cms/page')->getCollection();
        /* @var $collection Mage_Cms_Model_Mysql4_Page_Collection */
        $collection->setFirstStoreFlag(true);

        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            
            $collection->getSelect()->join(
                array('store_table_permissions' => $collection->getTable('cms/page_store')),
                'main_table.page_id = store_table_permissions.page_id',
                array()
            )
            ->where('store_table_permissions.store_id in (?)', $AllowedStoreviews)
            ->group('main_table.page_id');            
        }

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
} } 