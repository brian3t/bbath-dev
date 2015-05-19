<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogCategoryEditForm.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ RwCyThWTNMeCOakV('aa4122dde503aa4933db387251e610d9'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogCategoryEditForm extends Mage_Adminhtml_Block_Catalog_Category_Edit_Form
{
    public function _prepareLayout()
    {
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
        	if ((Mage::helper('aitpermissions')->isScopeStore() && !Mage::getStoreConfig('admin/general/allowdelete')) 
             || (Mage::helper('aitpermissions')->isScopeWebsite() && !Mage::getStoreConfig('admin/general/allowdelete_perwebsite')))
            {
                $category = $this->getCategory()->setIsDeleteable(false);
                Mage::unregister('category');
                Mage::register('category', $category);
            }
        }
        return parent::_prepareLayout();
    }
} } 