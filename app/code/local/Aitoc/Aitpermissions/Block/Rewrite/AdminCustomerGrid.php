<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCustomerGrid.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ RwCyThWTNMeCOakV('586b9907c393cb5b1896cc1344f3aa60'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminCustomerGrid extends Mage_Adminhtml_Block_Customer_Grid
{
	protected function _prepareColumns()
	{
		parent::_prepareColumns();
		
		if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
		{
            if (!Mage::getStoreConfig('admin/general/showallcustomers') && isset($this->_columns['website_id']))
            {
                unset($this->_columns['website_id']);
                $AllowedWebsites = Mage::helper('aitpermissions')->getAllowedWebsites();
                
                if (count($AllowedWebsites) > 1)
                {
                    $WebsiteFilter = array();
                    foreach ($AllowedWebsites as $AllowedWebsite) 
                    {
                    	$Website = Mage::getModel('core/website')->load($AllowedWebsite);
                    	$WebsiteFilter[$AllowedWebsite] = $Website->getData('name');
                    }
                    
                	$this->addColumn('website_id', array(
                    'header'    => Mage::helper('customer')->__('Website'),
                    'align'     => 'center',
                    'width'     => '80px',
                    'type'      => 'options',
                    'options'   => $WebsiteFilter,
                    'index'     => 'website_id',
                    ));
                }
            }
		}
        return $this;
	}
} } 