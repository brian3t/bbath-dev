<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/CatalogCategory.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ RwCyThWTNMeCOakV('602866e2f5916d9af4f1403bb7033ff7'); ?><?php
class Aitoc_Aitpermissions_Model_Rewrite_CatalogCategory extends Mage_Catalog_Model_Category
{
    protected function _beforeSave()
    {
        if (!$this->getId() AND !Mage::registry('aitemails_category_is_new'))
        {
            Mage::register('aitemails_category_is_new', true);
        }
        return parent::_beforeSave();
    }
    
    protected function _afterSave()
    {
        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            if (Mage::helper('aitpermissions')->isScopeStore()) 
            {
                // adding this category to allowed if created by user with restricted permissions
            	$CurrentStoreviewId = Mage::app()->getRequest()->getParam('store');
            	$CurrentStoreId = Mage::getModel('core/store')->load($CurrentStoreviewId)->getGroupId();
                $RoleId = Mage::getSingleton('admin/session')->getUser()->getRole()->getId();
            
                $RoleCollection = Mage::getModel('aitpermissions/advancedrole')->getCollection()
                    ->addFieldToFilter('role_id', $RoleId)
                    ->addFieldToFilter('store_id', $CurrentStoreId)
                    ->load();
                foreach ($RoleCollection as $Role)
                {
                    $StoredCategories = explode(',', $Role->getData('category_ids'));
                    if (!in_array($this->getId(), $StoredCategories)) 
                    {
                    	$StoredCategories[] = $this->getId();
                    }
                    $Role->setData('category_ids', implode(',', $StoredCategories));
                    $Role->save();
                }
            }
            
            if (true === Mage::registry('aitemails_category_is_new'))
            {
                Mage::unregister('aitemails_category_is_new');
                $this->setStoreId(0);
                $this->setIsActive(false);
                $this->save();
            }
        }
        
        return parent::_afterSave();
    }
} } 