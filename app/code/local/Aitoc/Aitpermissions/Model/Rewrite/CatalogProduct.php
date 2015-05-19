<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/CatalogProduct.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ QicmRCORfgjcVryd('fd4023b0bf566412c303952fc13fce21'); ?><?php
class Aitoc_Aitpermissions_Model_Rewrite_CatalogProduct extends Mage_Catalog_Model_Product
{
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if(Mage::helper('aitpermissions')->isPermissionsEnabled() 
            && Mage::getStoreConfig('admin/su/enable')
            && !$this->getCreatedAt())
        {
            $this->setStatus(Aitoc_Aitpermissions_Model_Rewrite_CatalogProductStatus::STATUS_AWAITING);
//            echo "<pre>"; print_r($this->toArray()); exit;
            Mage::getModel('aitpermissions/notification')->send($this);
        }
        if ($this->getId() && $this->getStatus())
        {
        Mage::getModel('aitpermissions/approve')->approve($this->getId(),$this->getStatus());
        }
        
        if (Mage::app()->getRequest()->getPost('simple_product') 
        &&  Mage::app()->getRequest()->getQuery('isAjax')
        &&  Mage::helper('aitpermissions')->isScopeStore())
        {
            $configurableProduct = Mage::getModel('catalog/product')
                ->setStoreId(0)
                ->load(Mage::app()->getRequest()->getParam('product'));
               
            if (!$configurableProduct->isConfigurable()) {
                return $this;
            }
           
            if (!$this->getData('category_ids'))
            {
                $categoryIds = $configurableProduct->getData('category_ids');
                if ( !empty($categoryIds) && is_array($categoryIds) )
                {
                    $this->setData('category_ids', $categoryIds);
                }
            }
        }
        return $this;
    }
    protected function _afterSave()
    {
    
        parent::_afterSave();
        if($this->getData('entity_id') && Mage::getStoreConfig('admin/su/enable') && $this->getStatus())
        {
            Mage::getModel('aitpermissions/approve')->approve($this->getData('entity_id'),$this->getStatus());
        }
    }
    protected function _beforeDelete()
    {
        parent::_beforeDelete();
        
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $bAllow = false;
            $product = Mage::getModel('catalog/product')->load(Mage::app()->getRequest()->getParam('id'));
    
            if (Mage::helper('aitpermissions')->isScopeWebsite() && Mage::getStoreConfig('admin/general/allowdelete_perwebsite')) 
            {
                $WebsiteIds = Mage::helper('aitpermissions')->getWebsiteIds();
                $productWebsiteIds = $product->getWebsiteIds();
                if (!empty($productWebsiteIds)) 
                {
                	foreach ($WebsiteIds as $WebsiteId)
                	{
                	    if (in_array($WebsiteId, $productWebsiteIds)) 
                	    {
                	    	$bAllow = true;
                	    	break;
                	    }
                	}
                }
            }
            
            if (Mage::helper('aitpermissions')->isScopeStore() && Mage::getStoreConfig('admin/general/allowdelete')) 
            {
            	$CategoryIds = Mage::helper('aitpermissions')->getCategoryIds();
                $productCategoryIds = $product->getCategoryIds();
                if (!empty($CategoryIds) && !empty($productCategoryIds)) 
                {
                	foreach ($CategoryIds as $CategoryId)
                	{
                	    if (in_array($CategoryId, $productCategoryIds)) 
                	    {
                	    	$bAllow = true;
                	    	break;
                	    }
                	}
                }
            }
            
            if (Mage::helper('aitpermissions/access')->isAllowManageEntity('product') && $bAllow = true)
            {
                if (!Mage::helper('aitpermissions/access')->canManageProduct($product))
                {
                    $bAllow = false;
                }
            }
            
            if ($bAllow == false) 
            {
                Mage::throwException(Mage::helper('aitpermissions')->__('Sorry, you have no permissions to delete this product. For more details please contact site administrator.'));
            }
        }
        return $this;
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $controller = Mage::app()->getRequest()->getControllerName();
        if(Mage::helper('aitpermissions')->isPermissionsEnabled() && Mage::helper('aitpermissions/access')->isAllowManageEntity('product') && Mage::app()->getStore()->isAdmin() && ($this->getCreatedBy() !== Mage::getSingleton('admin/session')->getUser()->getUserId()) && (!in_array($controller, array('sales_order_edit', 'sales_order_create'))) )
        {
            $this->unsetData();
        }
        
        return $this;
    }
} } 