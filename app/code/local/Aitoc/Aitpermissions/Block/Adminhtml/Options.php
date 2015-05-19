<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Adminhtml/Options.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ cooZCirCUrmokeDE('71f36ae772f1bff97e663ed533effc69'); ?><?php
/**
* @copyright  Copyright (c) 2012 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Adminhtml_Options extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitpermissions/options.phtml');
    }
    
    public function checkOption($role, $option)
    {
        $method = Mage::helper('aitpermissions')->getFuncNameByEntity($option);
        
        $collection = Mage::getModel('aitpermissions/advancedrole')->getCollection()->loadByRoleId($role);
        if (0 < $collection->getSize())
        {
            return (bool) $collection->getFirstItem()->$method();
        }
        
        if (0 == $collection->getSize())
        {
            return true; // NOT LOADED
        }
        
        return false;
    }
} } 