<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogProductHelperFormGallery.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ RwCyThWTNMeCOakV('40c7f1e5f095b6c0841257b852bf3764'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogProductHelperFormGallery extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery
{
	public function getElementHtml()
	{
		$html = parent::getElementHtml();
		if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
		{
			if ((Mage::helper('aitpermissions')->isScopeStore() && !Mage::getStoreConfig('admin/general/allowdelete')) 
             || (Mage::helper('aitpermissions')->isScopeWebsite() && !Mage::getStoreConfig('admin/general/allowdelete_perwebsite')))
            {
    		    $html = preg_replace('@cell-remove a-center last"><input([ ]+)type="checkbox"@', 'cell-remove a-center last"><input disabled="disabled" type="checkbox"', $html);
            }
		}
		return $html;
	}
} } 