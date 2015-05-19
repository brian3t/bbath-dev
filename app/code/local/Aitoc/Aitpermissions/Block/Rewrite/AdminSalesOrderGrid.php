<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminSalesOrderGrid.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ cooZCirCUrmokeDE('4a1e2dfeb0d57b0a72eaee3b169127a2'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminSalesOrderGrid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
	protected function _prepareColumns()
	{
		parent::_prepareColumns();
		
		if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
		{
			$AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
    		if (count($AllowedStoreviews) <=1 && isset($this->_columns['store_id']))
    		{
    		    unset($this->_columns['store_id']);
    		}
		}
		return $this;
	}
} } 