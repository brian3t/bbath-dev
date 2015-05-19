<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Mysql4/Advancedrole/Collection.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ UqqrIpEIRygqPDaW('45951c226e389e2fd07ef6b626e153b4'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Model_Mysql4_Advancedrole_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('aitpermissions/advancedrole');
    }

    public function loadByRoleId($roleId)
    {
        $this->addFieldToFilter('role_id', $roleId);
        $this->load();
        return $this;
    }
    
    public function loadByRoleAndStore($roleId, $storeId)
    {
        $this->addFieldToFilter('role_id', $roleId);
        $this->addFieldToFilter('store_id', $storeId);
        $this->load();
        return $this;
    }
} } 