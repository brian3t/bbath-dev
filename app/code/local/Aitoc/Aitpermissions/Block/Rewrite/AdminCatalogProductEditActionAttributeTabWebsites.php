<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogProductEditActionAttributeTabWebsites.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ RwCyThWTNMeCOakV('f9f2143370fc885d9acda9f0597bf495'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogProductEditActionAttributeTabWebsites extends Mage_Adminhtml_Block_Catalog_Product_Edit_Action_Attribute_Tab_Websites
{
    public function getWebsiteCollection()
    {
        $websites = parent::getWebsiteCollection();

        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
        	$AllowedWebsites = Mage::helper('aitpermissions')->getAllowedWebsites();
        	foreach ($websites as $i => $website)
            {
            	if (!in_array($website->getId(), $AllowedWebsites))
            	{
            		unset($websites[$i]);
            	}
            }
        }
        return $websites;
    }
} } 