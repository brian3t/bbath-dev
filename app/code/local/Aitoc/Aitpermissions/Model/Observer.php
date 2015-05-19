<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Observer.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ CiiDhwahIayirBjk('d76c96f3fb3071fdff85ef4c9fa96180'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitpermissions_Model_Observer
{
    protected $_helper;
    protected $_helperAccess;

    public function __construct()
    {
        $this->_helper = Mage::helper('aitpermissions');
        $this->_helperAccess = Mage::helper('aitpermissions/access');
    }

    public function saveAdvancedRole($observer)
    {
        $role    = $observer->getObject();
        $request = Mage::app()->getRequest();
        $roleId  = $role->getId();

        if ($roleId && !is_null($request->getPost('access_scope')))
        {
            // Cleaning
            $this->deleteAdvancedRole($roleId);

            $canUpdateGlobalAttr = $request->getPost('allowupdateglobalattrs') ? 1 : 0;
            $canEditOwnProductsOnly = $request->getPost('caneditownproductsonly') ? 1 : 0;
            
            switch ($request->getPost('access_scope'))
            {
                /**
                * Scope set to "Limit Access to Store Views/Categories"
                */
                case 'store':
                    $SelectedStoreIds = $request->getPost('store_switcher');
                    $StoreCategoryIds = $request->getPost('store_category_ids');                    
    
                    foreach ($SelectedStoreIds as $store_id => $storeview)
                    {
                        $StoreViewIds = implode(',', $storeview);
                        
                        $CategoryIds = '';
                        if (isset($StoreCategoryIds[$store_id]))
                        {
                            $CategoryIds = implode(',', array_diff(array_unique(explode(',', $StoreCategoryIds[$store_id])), array('')));
                        }
    
                        $advancedrole = Mage::getModel('aitpermissions/advancedrole');
    
                        $advancedrole->setData('role_id', $roleId);
                        $advancedrole->setData('store_id', $store_id);
                        $advancedrole->setData('storeview_ids', $StoreViewIds);
                        $advancedrole->setData('category_ids', $CategoryIds);
                        $advancedrole->setData('website_id', 0);
                        $advancedrole->setData('can_edit_global_attr', $canUpdateGlobalAttr);
                        $advancedrole->setData('can_edit_own_products_only', $canEditOwnProductsOnly);
                        
                        $advancedrole->save();
                    }
                    break;
                
                /**
                * Scope set to "Limit Access to Websites"
                */
                case 'website': 
                    foreach ($request->getPost('website_switcher') as $website_id)
                    {
                        $advancedrole = Mage::getModel('aitpermissions/advancedrole');
                        
                        $advancedrole->setData('role_id', $roleId);
                        $advancedrole->setData('website_id', $website_id);
                        $advancedrole->setData('store_id', '');
                        $advancedrole->setData('category_ids', '');
                        $advancedrole->setData('can_edit_global_attr', $canUpdateGlobalAttr);
                        $advancedrole->setData('can_edit_own_products_only', $canEditOwnProductsOnly);
                        
                        $advancedrole->save();
                    }
                    break;
            }
        }
    }
    
    public function validateAdvancedRole(Varien_Event_Observer $observer)
    {
        $role    = $observer->getObject();
        $request = Mage::app()->getRequest();
        $roleId  = $role->getId();
        /**
         * Scope set to "Limit Access to Store Views/Categories"
         */
        if ('store' == $request->getPost('access_scope'))
        {
            $storeIds      = $request->getPost('store_switcher');
            $categoryIds   = $request->getPost('store_category_ids');
            $errorStoreIds = array();

            foreach ($storeIds as $storeId => $storeviewIds)
            {
                if (empty($categoryIds[$storeId]))
                {
                    $errorStoreIds[] = $storeId;
                }
            }

            if ($errorStoreIds)
            {
                $storesCollection = Mage::getModel('core/store_group')->getCollection()
                    ->addFieldToFilter('group_id', array('in' => $errorStoreIds));

                $storeNames = array();
                foreach ($storesCollection as $store)
                {
                    $storeNames[] = $store->getName();
                }

                Mage::throwException($this->_helper->__('Please, select allowed categories for the following stores: %s', join(', ', $storeNames)));
            }
        }
    }

    public function onAdminRolesDeleteAfter($observer)
    {
        $role = $observer->getObject();

        if ($role)
        {
            $this->deleteAdvancedRole($role->getId());
        }
    }
    
    public function onCatalogEditAction($observer)
    {
        if ($this->_helper->isPermissionsEnabled()) 
        {
            $product = $observer->getProduct();
            /* $var $product Mage_Catalog_Model_Product */
            
            if (!Mage::getStoreConfig('admin/general/showallproducts')) 
            {
                $bAllow = false;

                $allowedWebsites = $this->_helper->getAllowedWebsites();
                if (array_intersect($allowedWebsites, $product->getWebsiteIds()))
                {
                    if ($this->_helper->isScopeWebsite())
                    {
                        $bAllow = true;
                    }
                    else 
                    {
                        $allowedCategories = $this->_helper->getAllowedCategories();
                        if (array_intersect($allowedCategories, $product->getCategoryIds()))
                        {
                            $bAllow = true;
                        }
                    }
                }
                
                if ($this->_helperAccess->isAllowManageEntity('product') && $bAllow = true)
                {
                    if (!$this->_helperAccess->canManageProduct($product))
                    {
                        $bAllow = false;
                    }
                }

                if (!$bAllow)
                {
                    /* @var $session Mage_Adminhtml_Model_Session */
                    $session = Mage::getSingleton('adminhtml/session');
                    $session->addError($this->_helper->__('Sorry, you have no permissions to edit this product. For more details please contact site administrator.'));

                    $controller = Mage::app()->getFrontController();
                    $controller->getResponse()
                        ->setRedirect(Mage::getModel('adminhtml/url')->getUrl('*/*/', array('store' => $controller->getRequest()->getParam('store', 0))))
                        ->sendResponse();
                    exit;
                }
            }

            if (($this->_helper->isScopeStore() && !Mage::getStoreConfig('admin/general/allowdelete')) 
             || ($this->_helper->isScopeWebsite() && !Mage::getStoreConfig('admin/general/allowdelete_perwebsite')))
            {
                $product->setIsDeleteable(false);
            }
        }
    }
    
    public function onCatalogProductPrepareSave($observer)
    {
        // should check if the product is a new one
        $product = $observer->getProduct();
        $request = $observer->getRequest();

        $aProductPostData = $request->getPost('product');

        if (!$product->getId())
        {
            // new product
            Mage::getSingleton('catalog/session')->setIsNewProduct(true);
            Mage::getSingleton('catalog/session')->setSelectedVisibility($aProductPostData['visibility']);
            $product->setData('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
        }
    }
    
    /*
     * Apply selected visibility to allowed storeviews only, set "Not visible" in others
     */
    public function onCatalogProductSaveAfter($observer)
    {
        $controller = Mage::app()->getRequest()->getControllerName();
        $action     = Mage::app()->getRequest()->getActionName();
        
        
        if ( !(Mage::getSingleton('catalog/session')->getIsNewProduct(true)) && !('catalog_product' === $controller && 'quickCreate' === $action)  )
        {
            return;
        }
        
        $product = $observer->getProduct();
        
        $attributeId = Mage::getModel('eav/entity_attribute')->load('created_by', 'attribute_code')->getId();
        $adminId = Mage::getSingleton('admin/session')->getUser()->getUserId();
        
        $collection =           Mage::getResourceModel('catalog/product_collection');
        $attributeTable =       $collection->getTable('catalog_product_entity_int');
        $entityTypeId =         $product->getEntityTypeId();

        $this->insertAttributeValue($attributeTable, $entityTypeId, $attributeId, 0, $product->getId(), $adminId);
        
        if (!($this->_helper->isPermissionsEnabled()))
        {
            return;
        }
        
        /* setting selected visibility for allowed store views */
        
        $allowedStoreviews =    $this->_helper->getAllowedStoreviews();
        $attributeId =          Mage::getModel('eav/entity_attribute')->load('visibility', 'attribute_code')->getId();
        $visibilitySelected =   Mage::getSingleton('catalog/session')->getSelectedVisibility(true);

        foreach ($allowedStoreviews as $StoreviewId)
        {
            $this->insertAttributeValue($attributeTable, $entityTypeId, $attributeId, $StoreviewId, $product->getId(), $visibilitySelected);
        }

        /* setting visibility: "Nowhere" for all other store views */

        $visibilityNotVisible = Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
        $storeCollection =      Mage::getModel('core/store')->getCollection();
        foreach($storeCollection as $store) 
        {
            if (0 != $store->getId() && (!in_array($store->getId(), $allowedStoreviews)))
            {
                $this->insertAttributeValue($attributeTable, $entityTypeId, $attributeId, $store->getId(), $product->getId(), $visibilityNotVisible);
            }
        }
    }
    
    public function onCatalogProductCollectionLoadBefore($observer)
    {
    
        if (!(false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'adminhtml')
         || false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'bundle'))) {
            return $this;
        }
        
        if (!($this->_helper->isPermissionsEnabled() && !Mage::getStoreConfig('admin/general/showallproducts')))  {
            return $this;
        }
        
        $collection = $observer->getCollection();
        $controller = Mage::app()->getRequest()->getControllerName();
        $action     = Mage::app()->getRequest()->getActionName();
        
        $adminId = Mage::getSingleton('admin/session')->getUser()->getUserId();
        if ($this->_helperAccess->isAllowManageEntity('product') && $adminId) 
        {
            $aCond = array();
            $aCond[] = array( 'attribute' => 'created_by', 'eq' => $adminId );
            $join = 'inner';

            if(in_array($controller, array('sales_order_edit', 'sales_order_create', 'catalog_product')))
            {
                if($order = $this->_getCurrentOrder())
                {
                    $join = 'left';
                    $collection->getSelect()->reset(Zend_Db_Select::WHERE);                
                    $items = $order->getItemsCollection();
                    if ($items->getSize())
                    {
                        foreach($items as $item)
                        {
                            $aProductIds[] .= $item->getProductId();
                        }
                        $aCond[] = array( 'attribute' => 'entity_id', 'in' => $aProductIds );
                    }
                }
                $collection->addAttributeToFilter($aCond, null, $join);
            }
        }        
        
        if ($this->_helper->isScopeStore() && !in_array($controller, array('sales_order_edit', 'sales_order_create')) )
        {
            $categories = $this->_helper->getCategoryIds();
            if (!empty($categories))
            {
                $where = 
                    ' (e.entity_id IN ( '.
                    ' SELECT product_id '.
                    ' FROM '.$collection->getTable('catalog_category_product').' '.
                    ' WHERE category_id IN ('.join(',', $categories).') '.
                    ' ) OR product_cat.product_id IS NULL ) ';
                
                $collection->getSelect()->joinLeft(
                    array('product_cat' => $collection->getTable('catalog_category_product') ),
                    'product_cat.product_id = e.entity_id AND product_cat.category_id IS NULL',
                    array()
                );

                $collection->getSelect()->where($where);
            }
        }
        if ($this->_helper->isScopeWebsite())
        {
            $websiteIds = $this->_helper->getAllowedWebsites();
            $scopeStoreId = Mage::app()->getFrontController()->getRequest()->getParam('store');

            if ($scopeStoreId)
            {
                $scopeWebsiteId = Mage::getModel('core/store')->load($scopeStoreId)->getWebsiteId();

                if (in_array($scopeWebsiteId, $websiteIds))
                {
                    $websiteIds = array($scopeWebsiteId);
                }
            }
            $collection->addWebsiteFilter($websiteIds);
        }
           
    }
    
    protected function _getCurrentOrder()
    {
        $orderId = Mage::app()->getRequest()->has('order_id');
        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            return $order; 
            }
        
        $order = Mage::getSingleton('adminhtml/session_quote')->getOrder();
        if ($order->getId()) {
            return $order;
        }
        return null;
    }
    
    /**
    * Add limit to collection (order, invoice, shipment...)
    * 
    * @param mixed $collection
    */
    protected function _limitCollectionByStore($collection)
    {
        if (!$collection->getFlag('permissions_processed'))
        {
            if (false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'adminhtml'))
            {
                if ($this->_helper->isPermissionsEnabled()) 
                {
                    $AllowedStoreviews = $this->_helper->getAllowedStoreviews();
                    if(version_compare(Mage::getVersion(),'1.4.1.0','>'))
                    {
                    $collection->addAttributeToFilter('main_table.store_id', array('in' => $AllowedStoreviews));
                    } else {
                        $collection->addAttributeToFilter('store_id', array('in' => $AllowedStoreviews));
                    }
                }
            }
            $collection->setFlag('permissions_processed', true);
        }
    }
    
    public function onEavCollectionAbstractLoadBefore($observer)
    {
        $collection = $observer->getCollection();

        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Collection
            || $collection instanceof Mage_Sales_Model_Resource_Order_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
        
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Invoice_Collection
            || $collection instanceof Mage_Sales_Model_Resource_Order_Invoice_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
        
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Shipment_Collection
            || $collection instanceof Mage_Sales_Model_Resource_Order_Shipment_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
        
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Creditmemo_Collection
            || $collection instanceof Mage_Sales_Model_Resource_Order_Creditmemo_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
        
        if ($collection instanceof Mage_Customer_Model_Entity_Customer_Collection
            || $collection instanceof Mage_Customer_Model_Resource_Customer_Collection)
        {
            if (!Mage::getStoreConfig('admin/general/showallcustomers') && $this->_helper->isPermissionsEnabled())
            {
                $AllowedWebsites = $this->_helper->getAllowedWebsites();
                $collection->addAttributeToFilter('website_id', array('in' => $AllowedWebsites));
            }
        }
    }
    
    public function onCoreCollectionAbstractLoadBefore($observer)
    {
        $collection = $observer->getCollection();
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Grid_Collection
            || $collection instanceof Mage_Sales_Model_Resource_Order_Grid_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Invoice_Grid_Collection
            || $collection instanceof Mage_Sales_Model_Resource_Order_Invoice_Grid_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }        
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Shipment_Grid_Collection
            || $collection instanceof Mage_Sales_Model_Resource_Order_Shipment_Grid_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Creditmemo_Grid_Collection
            || $collection instanceof Mage_Sales_Model_Resource_Order_Creditmemo_Grid_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
        if ($collection instanceof Mage_Cms_Model_Mysql4_Block_Collection)
        {
            if ($this->_helper->isPermissionsEnabled())
            {
                $table = Mage::getSingleton('core/resource')->getTableName('cms/block_store');

                $collection
                    ->getSelect()
                    ->distinct()
                    ->join($table, $table.'.block_id = main_table.block_id', array());

                if (Mage::app()->getRequest()->getParam('store'))
                {
                    $storeId = Mage::getModel('core/store')
                        ->load(Mage::app()->getRequest()->getParam('store'))
                        ->getId();

                    $collection
                        ->getSelect()
                        ->where($table.'.store_id in (0, ' . $storeId . ')');
                }
                else
                {
                    $allowedStoreviews = $this->_helper->getAllowedStoreviews();
                    $collection
                        ->getSelect()
                        ->where($table.'.store_id in (0,' . implode(',', $allowedStoreviews) . ')');
                }
            }
        }
    }
    
    public function onSalesOrderLoadAfter($observer)
    {
        $order = $observer->getOrder();
        
        if (false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'adminhtml'))
        {
            if ($this->_helper->isPermissionsEnabled()) 
            {
                $AllowedStoreviews = $this->_helper->getAllowedStoreviews();
                if (!in_array($order->getStoreId(), $AllowedStoreviews))
                {
                    Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/sales_order'));
                }
            }
        }
    }
    
    public function onCustomerLoadAfter($observer)
    {
        $customer = $observer->getCustomer();
        
        if (false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'adminhtml') && $customer->getData())
        {
            if ($this->_helper->isPermissionsEnabled() && !Mage::getStoreConfig('admin/general/showallcustomers'))
            {
                $AllowedWebsites = $this->_helper->getAllowedWebsites();

                if (!in_array($customer->getWebsiteId(), $AllowedWebsites))
                {
                    Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/*'));
                }
            }
        }
    }
    
    public function onCmsPageLoadAfter($observer)
    {
        $model = $observer->getObject();
        if ($model instanceof Mage_Cms_Model_Page)
        {
            if (!$this->_helper->isPermissionsEnabled())
            {
                return true;
            }

            if (!$model->getData('store_id'))
            {
                return true;
            }

            if (is_array($model->getData('store_id')) && in_array(0, $model->getData('store_id')))
            {
                // allow, if admin store (all store views) selected
                return true;
            }

            if (is_array($model->getData('store_id')) && array_intersect($model->getData('store_id'), $this->_helper->getAllowedStoreviews()))
            {
                return true;
            }

            // if no permissions - redirect
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/*'));
        }
    }
    
    public function onAdminhtmlCmsPageEditTabMainPrepareForm($observer)
    {
        if ($this->_helper->isPermissionsEnabled())
        {
            $page = Mage::registry('cms_page');
            $pageStoreviews = (array)$page->getStoreId();

            $allowedStoreviews = $this->_helper->getAllowedStoreviews();

            /* if page assigned to some storeview admin don't have access to - forbid enabled/disabled setting changes */
            if (array_diff($pageStoreviews, $allowedStoreviews))
            {
                $fieldset = $observer->getForm()->getElement('base_fieldset');
                $fieldset->removeField('is_active');
            }
        }
    }
    
    public function onCmsPagePrepareSave($observer)
    {
        $page = $observer->getPage();
        if ($page->getId() && $this->_helper->isPermissionsEnabled())
        {
            // should keep in mind we may have store views from another websites (not visible on edit form) assigned
            $this->_helperAccess->setCmsObjectStores($page);
        }
    }
    
    public function onModelSaveBefore($observer)
    { 
        if ($this->_helper->isPermissionsEnabled())
        {
            $model = $observer->getObject();

            if ($model instanceof Mage_Cms_Model_Block)
            {
                $this->_helperAccess->setCmsObjectStores($model);
            }
            if ($model instanceof Mage_Widget_Model_Widget_Instance)
            {
                $this->_helperAccess->setCmsObjectStores($model);
            }
            if ($model instanceof Mage_Poll_Model_Poll)
            {
                $this->_helperAccess->setCmsObjectStores($model);
            }
            if ($model instanceof Mage_Catalog_Model_Product)
            {
                $this->_helperAccess->setProductWebsites($model);
            }
        }
    }
    
    public function onReviewDeleteBefore($observer)
    {
        if ($this->_helper->isPermissionsEnabled()) 
        {
            $ReviewId = $observer->getObject()->getId();
            $ReviewStoreId = Mage::getModel('review/review')->load($ReviewId)->getData('store_id');
            $AllowedStoreviews = $this->_helper->getAllowedStoreviews();
            
            if (!in_array($ReviewStoreId, $AllowedStoreviews)) 
            {
                Mage::throwException($this->_helper->__('Review could not be deleted due to insufficent permissions.'));
            }
        }
    }
    
    public function onAdminhtmlCatalogProductReviewMassDeletePredispatch($observer)
    {
        if ($this->_helper->isPermissionsEnabled()) 
        {
            $ReviewIds = $observer->getData('controller_action')->getRequest()->getParam('reviews');
            $AllowedStoreviews = $this->_helper->getAllowedStoreviews();
            
            $NotAllowedReviewIds = array();
            
            foreach ($ReviewIds as $id => $ReviewId) 
            {
                $ReviewStoreId = Mage::getModel('review/review')->load($ReviewId)->getData('store_id');
                if (!in_array($ReviewStoreId, $AllowedStoreviews)) 
                {
                    unset($ReviewIds[$id]);
                    $NotAllowedReviewIds[] = $ReviewId;
                }
            }
            if (!empty($NotAllowedReviewIds)) 
            {
                Mage::getSingleton('adminhtml/session')->addError($this->_helper->__('Some review(s) could not be deleted due to insufficent permissions.'));
                $observer->getData('controller_action')->getRequest()->setParam('reviews', $ReviewIds);
            }
        }
    }
    
    public function deleteAdvancedRole($roleId)
    {
        $advancedroleCollection = Mage::getModel('aitpermissions/advancedrole')->getCollection();
        $advancedroleCollection->addFieldToFilter('role_id', $roleId)->load();
        foreach ($advancedroleCollection as $advancedrole) 
        {
            $advancedrole->delete();
        }
    }
    
    public function insertAttributeValue($table, $entityTypeId, $attributeId, $toreId, $entityId, $value)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $data = array
        (
            'entity_type_id'   => $entityTypeId,
            'attribute_id'  => $attributeId,
            'store_id'     => $toreId,
            'entity_id'     => $entityId,
            'value'     => $value,
        );

        $connection->insert($table, $data);
    }
} } 