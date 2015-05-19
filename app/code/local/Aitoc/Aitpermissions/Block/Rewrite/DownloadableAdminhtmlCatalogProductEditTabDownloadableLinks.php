<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/DownloadableAdminhtmlCatalogProductEditTabDownloadableLinks.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ CiiDhwahIayirBjk('a0d9867516db9e6a78d7fc9c3c242d32'); ?><?php
/**
* @copyright  Copyright (c) 2011 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_DownloadableAdminhtmlCatalogProductEditTabDownloadableLinks extends Mage_Downloadable_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_Links
{
     /**
     *
     * @return Aitoc_Aitpermissions_Helper_Access
     */
    public function getAccessHelper()
    {
        return Mage::helper('aitpermissions/access');
    }
    
    public function getPurchasedSeparatelySelect()
    {        
        $html = parent::getPurchasedSeparatelySelect();     
        if (!Mage::app()->isSingleStoreMode() && !$this->getAccessHelper()->isAllowManageEntity('attribute'))
        {
            $html = str_replace('<select', '<select disabled="disabled"', $html);         
        }
        return $html;
    }
} } 