<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogProductEditTabCategories.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ RwCyThWTNMeCOakV('3ba71c9dee18c577a10bb691c6efb4df'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogProductEditTabCategories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{
    protected $_allowedCategoriesIds = array();
    

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
                $this->_allowedCategoriesIds = Mage::helper('aitpermissions')->getAllowedCategories();
                $allCategoriesForStoreViews = Mage::helper('aitpermissions')->getAllCategoriesForStoreViews();
                if (!empty($this->_allowedCategoriesIds) && !Mage::helper('aitpermissions')->isScopeStore()) 
                {
                    $collection->addIdFilter($this->_allowedCategoriesIds);
                }
                elseif(!empty($allCategoriesForStoreViews) && Mage::helper('aitpermissions')->isScopeStore())
                {
                    $collection->addIdFilter($allCategoriesForStoreViews);
                }
            }

	        $this->setData('category_collection', $collection);
        }
        return $collection;

    }


    public function isReadonly($id=0)
    {
        if(!Mage::helper('aitpermissions')->isScopeStore())
            return $this->getProduct()->getCategoriesReadonly();

        $readOnlyFlag =  $this->getProduct()->getCategoriesReadonly();
        $allowedCatFlag = false;

        if(in_array($id, $this->_allowedCategoriesIds))
        {
            $allowedCatFlag = true;
        }

        if($allowedCatFlag && !$readOnlyFlag)
        {
            return false;
        }

        return true;
    }


protected function _getNodeJson($node, $level=1)
    {
        $item = parent::_getNodeJson($node, $level);

        $isParent = $this->_isParentSelectedCategory($node);

        if ($isParent) {
            $item['expanded'] = true;
        }

//        if ($node->getLevel() > 1 && !$isParent && isset($item['children'])) {
//            $item['children'] = array();
//        }


        if (in_array($node->getId(), $this->getCategoryIds())) {
            $item['checked'] = true;
        }

        if (!$this->isReadonly($node->getId())) {
            $item['disabled'] = false;
        }
        return $item;
    }


public function getRoot($parentNodeCategory=null, $recursionLevel=3)
    {
        if (!is_null($parentNodeCategory) && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = Mage::registry('root');
        if (is_null($root)) {
            $storeId = (int) $this->getRequest()->getParam('store');

            if ($storeId) {
                $store = Mage::app()->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            }
            else {
                $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
            }

            $ids = $this->getSelectedCategoriesPathIds($rootId);
            $tree = Mage::getResourceSingleton('catalog/category_tree')
                ->loadByIds($ids, false, false);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
                if ($this->isReadonly($rootId)) {
                    $root->setDisabled(true);
                }
            }
            elseif($root && $root->getId() == Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setName(Mage::helper('catalog')->__('Root'));
            }

            Mage::register('root', $root);
        }

        return $root;
    }
    
    protected function _getSelectedNodes()
    {
        if(version_compare(Mage::getVersion(),'1.4.0.0','>='))
            return parent::_getSelectedNodes();
        
        if ($this->_selectedNodes === null) {
            $this->_selectedNodes = array();
            $root = $this->getRoot();

            foreach ($this->getCategoryIds() as $categoryId) {
                if($root)
                {
                $this->_selectedNodes[] = $this->getRoot()->getTree()->getNodeById($categoryId);
                }
            }
        }

        return $this->_selectedNodes;
    }

} } 