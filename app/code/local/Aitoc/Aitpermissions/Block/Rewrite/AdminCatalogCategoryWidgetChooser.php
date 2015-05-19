<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogCategoryWidgetChooser.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ NoIMQcVQABDIdkmt('ac2353b35112b75e50ed6c78de5cd39a'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogCategoryWidgetChooser extends Mage_Adminhtml_Block_Catalog_Category_Widget_Chooser
{
    public function getCategoryCollection()
    {
        $collection = parent::getCategoryCollection()->addAttributeToSelect('url_key')->addAttributeToSelect('is_anchor');
        
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $AllowedCategories = Mage::helper('aitpermissions')->getAllowedCategories();
            $collection->addIdFilter($AllowedCategories);
        }
        return $collection;
    }
} } 