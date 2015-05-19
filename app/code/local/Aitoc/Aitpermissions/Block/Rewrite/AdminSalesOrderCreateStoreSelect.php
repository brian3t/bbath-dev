<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminSalesOrderCreateStoreSelect.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ CiiDhwahIayirBjk('35911c49ac20870d6165f325614026c5'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminSalesOrderCreateStoreSelect extends Mage_Adminhtml_Block_Sales_Order_Create_Store_Select
{
    public function getStoreCollection($group)
    {
        $stores = parent::getStoreCollection($group);
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
        	$AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
        	$stores->addIdFilter($AllowedStoreviews);
        }
        return $stores;
    }
} } 