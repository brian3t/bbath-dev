<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/CoreWebsiteCollection.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ QicmRCORfgjcVryd('8a98e1e3aed691a6e0d86309a5a8192e'); ?><?php

class Aitoc_Aitpermissions_Model_Rewrite_CoreWebsiteCollection extends Mage_Core_Model_Mysql4_Website_Collection
{
    public function toOptionHash()
    {
        /* @var $helper Aitoc_Aitpermissions_Helper_Data */
        $helper = Mage::helper('aitpermissions');
        if ($helper->isPermissionsEnabled())
        {
            $this->addFieldToFilter('website_id', array('in' => $helper->getAllowedWebsites()));
        }

        return parent::toOptionHash();
    }
} } 