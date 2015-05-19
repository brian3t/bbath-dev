<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Adminhtml/Stores.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ RwCyThWTNMeCOakV('cb994567d59b93e398a5aa27a5be18e9'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Adminhtml_Stores extends Mage_Adminhtml_Block_Template
{
    protected $_storeIds;
    protected $_hasDefaultOption = true;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitpermissions/stores.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName($this->__('All Store Views'));
    }

    public function getWebsiteCollection()
    {
        $collection = Mage::getModel('core/website')->getResourceCollection();

        $websiteIds = $this->getWebsiteIds();
        if (!is_null($websiteIds)) 
        {
            $collection->addIdFilter($this->getWebsiteIds());
        }

        return $collection->load();
    }

    public function getWebsites()
    {
        $websites = Mage::app()->getWebsites();
        if ($websiteIds = $this->getWebsiteIds()) 
        {
            foreach ($websites as $websiteId => $website) 
            {
                if (!in_array($websiteId, $websiteIds)) 
                {
                    unset($websites[$websiteId]);
                }
            }
        }
        return $websites;
    }

    public function getGroupCollection($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) 
        {
            $website = Mage::getModel('core/website')->load($website);
        }
        return $website->getGroupCollection();
    }

    public function getStoreGroups($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) 
        {
            $website = Mage::app()->getWebsite($website);
        }
        return $website->getGroups();
    }

    public function getStoreCollection($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) 
        {
            $group = Mage::getModel('core/store_group')->load($group);
        }
        $stores = $group->getStoreCollection();
        $_storeIds = $this->getStoreIds();
        if (!empty($_storeIds)) 
        {
            $stores->addIdFilter($_storeIds);
        }
        return $stores;
    }

    public function getStores($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) 
        {
            $group = Mage::app()->getGroup($group);
        }
        $stores = $group->getStores();
        if ($storeIds = $this->getStoreIds()) {
            foreach ($stores as $storeId => $store) {
                if (!in_array($storeId, $storeIds)) {
                    unset($stores[$storeId]);
                }
            }
        }
        return $stores;
    }

    public function getStoreId()
    {
        return $this->getRequest()->getParam('store');
    }

    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    public function getStoreIds()
    {
        return $this->_storeIds;
    }

    public function isShow()
    {
        return !Mage::app()->isSingleStoreMode();
    }

    protected function _toHtml()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            return parent::_toHtml();
        }
        return '';
    }

    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }
} } 