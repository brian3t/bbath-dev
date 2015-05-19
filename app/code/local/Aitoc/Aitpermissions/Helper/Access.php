<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Helper/Access.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ fpUgNIdNXeZUtyMl('1d2f8779075e70019b6e2ebc32e40d31'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Helper_Access extends Mage_Core_Helper_Abstract
{
    private function _getAllowedActions($type)
    {
        $allowedActions = array(
            'attribute' => array(
            'adminhtml_catalog_product',
            )
        );
        
        return isset($allowedActions[$type]) ? $allowedActions[$type] : array();
    }
    
    private function _canProcessAction($type)
    {
        $action = Mage::app()->getFrontController()->getAction()->getFullActionName();
        
        if ($this->_getAllowedActions($type) && $action) {
            foreach ($this->_getAllowedActions($type) as $allowedAction) {
                if (($allowedAction == $action) || (false !== strpos($action, $allowedAction))) {
                    return true;
                }
            }
        return false;
    }
        return true;
    }
    
    /**
    * Sets store_id's for cms object, keeping in mind that unavailable stores are 
    * not visible in multiselect, but should not dissapear after save
    * 
    * @param object $objectToModify
    * @param object $objectCurrent
    */
    public function setCmsObjectStores($object)
    {
        $origData = $object->getOrigData();
        $saveData = $object->getData();
        
        $objectIsNew = empty($origData);
        
        $allowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
        
        switch (get_class($object))
        {
            case 'Mage_Cms_Model_Page':
            {
                $tosaveStoreIds = $saveData['stores'];
                
                if (!$objectIsNew)
                {
                    $originalStoreIds = $origData['store_id'];
                    $preserveStoreIds = array_diff($originalStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_intersect($tosaveStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_unique(array_merge($preserveStoreIds, $tosaveStoreIds));
                }
                    
                $object->setData('stores', $tosaveStoreIds);
                
                break;
            }
            case 'Mage_Cms_Model_Block':
            {
                $tosaveStoreIds = $saveData['stores'];

                if (!$objectIsNew)
                {
                    $originalStoreIds = $origData['store_id'];
                    $preserveStoreIds = array_diff($originalStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_intersect($tosaveStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_unique(array_merge($preserveStoreIds, $tosaveStoreIds));
                }
                
                $object->setData('stores', $tosaveStoreIds);
                
                break;
            }
            case 'Mage_Widget_Model_Widget_Instance':
            {
                $tosaveStoreIds = explode(',', $saveData['store_ids']);
                
                if (!$objectIsNew)
                {
                    $originalStoreIds = explode(',', $origData['store_ids']);
                    $preserveStoreIds = array_diff($originalStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_intersect($tosaveStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_unique(array_merge($preserveStoreIds, $tosaveStoreIds));
                }
                
                $object->setData('store_ids', implode(',', $tosaveStoreIds));
                
                break;
            }
            case 'Mage_Poll_Model_Poll':
            {
                $tosaveStoreIds = $saveData['store_ids'];
                
                if (!$objectIsNew)
                {
                    $originalStoreIds = Mage::getModel('poll/poll')->load($object->getPollId())->getStoreIds();
                    $preserveStoreIds = array_diff($originalStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_intersect($tosaveStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_unique(array_merge($preserveStoreIds, $tosaveStoreIds));
                }
                
                $object->setData('store_ids', $tosaveStoreIds);
                
                break;
            }
            default: 
            {
                break;
            }
        }
    }
    
    /*
     * If a product is assigned to website(s) not available for current role, we should preserve these assignments
     */
    public function setProductWebsites($product)
    {
        $originalProduct = Mage::getModel('catalog/product')->load($product->getId());

        $allowedWebsites = Mage::helper('aitpermissions')->getAllowedWebsites();
        $originalWebsiteIds = $originalProduct->getWebsiteIds();
        $toSaveWebsiteIds = $product->getWebsiteIds();
        
        $preserveWebsiteIds = array_diff($originalWebsiteIds, $allowedWebsites);
        $toSaveWebsiteIds = array_unique(array_merge($preserveWebsiteIds, $toSaveWebsiteIds));
        
        $product->setWebsiteIds($toSaveWebsiteIds);
    }
    
    /**
    * Checks if specified website id is allowed for access
    * 
    * @param integer $websiteId
    */
    public function isWebsiteAllowed($websiteId)
    {
        if (!$websiteId)
        {
            return true;
        }
        if (Mage::helper('aitpermissions')->isScopeStore())
        {
            return false;
        }
        if (!in_array($websiteId, Mage::helper('aitpermissions')->getAllowedWebsites()))
        {
            return false;
        }
        return true;
    }
    
    /**
    * Checks if specified group id is allowed for access
    * 
    * @param integer $groupId
    */
    public function isGroupAllowed($groupId)
    {
        if (!$groupId)
        {
            return true;
        }
        $AllowedStores = Mage::helper('aitpermissions')->getAllowedStores();
        if (!in_array($groupId, $AllowedStores)) 
        {
        	return false;
        }
        return true;
    }
    
    /**
    * Checks if specified store id(s) allowed
    * 
    * @param integer|array $storeId
    */
    public function isStoreIdAllowed($storeId)
    {
        if (!$storeId || (array_key_exists(0, $storeId) && !$storeId[0]))
        {
            return true;
        }
        if (!is_array($storeId))
        {
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            if (!in_array($storeId, $AllowedStoreviews)) 
            {
            	return false;
            }
        }
        return true;
    }
    
    /**
     * Returns default helper.
     * 
     * @return Aitoc_Aitpermissions_Helper_Data
     */
    public function getAitpermissionsHelper()
    {
        return Mage::helper('aitpermissions');
    }
    
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }
    
    public function isAllowManageEntity($type)
    {
        if (!$this->_canProcessAction($type))
        {
            return true;
        }
        if (!Mage::app()->getStore()->isAdmin())
        {
            return true;
        }
        $method = $this->getAitpermissionsHelper()->getFuncNameByEntity($type);
            $roles = $this->getAitpermissionsHelper()->getCurrentRoles();
            if ($roles->getSize())
            {
                foreach ($roles as $role)
                {                        
                        return (boolean) $role->$method();
                    }
                }
            return true;
        }
    
    public function canManageProduct($product)
    {
        $productOwnerId = $product->getCreatedBy();
        $adminId = Mage::getSingleton('admin/session')->getUser()->getUserId();

        if ($productOwnerId && $adminId)
        {
            if ($productOwnerId == $adminId)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
        return true;
    }
} } 