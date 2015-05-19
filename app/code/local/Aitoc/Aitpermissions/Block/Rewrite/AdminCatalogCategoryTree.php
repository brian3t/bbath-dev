<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogCategoryTree.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ hwwjqhZqcZkwager('958bf383109d53947083297fd991a808'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogCategoryTree extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    public function getCategoryCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
        
        if (is_null($collection)) 
        {
            $collection = Mage::getModel('catalog/category')->getCollection();

            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
                
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setProductStoreId($storeId)
                ->setLoadProductCount($this->_withProductCount)
                ->setStoreId($storeId);
                
            if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
            {
            	$AllowedCategories = Mage::helper('aitpermissions')->getAllowedCategories();
                if (!empty($AllowedCategories)) 
                {
                    $store = Mage::app()->getStore($storeId);
                    $AllowedCategories[] = $store->getRootCategoryId();
                    $collection->addIdFilter($AllowedCategories);
                }
            }
            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    public function getMoveUrl()
    {
        if ($this->getRequest()->getPost('store'))
        {
            return $this->getUrl('*/catalog_category/move', array('store' => $this->getRequest()->getPost('store')));
        }

        return $this->getUrl('*/catalog_category/move', array('store' => $this->getRequest()->getParam('store')));
    }

    public function getMoveUrlPattern()
    {
        return $this->getUrl('*/catalog_category/move', array('store' => ':store'));
    }
} } 