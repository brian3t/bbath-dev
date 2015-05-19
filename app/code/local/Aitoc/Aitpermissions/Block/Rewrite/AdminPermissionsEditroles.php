<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminPermissionsEditroles.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ ThhkUqPUQmBhWZrO('c2c94ed552b03c3e692b831395907ee7'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminPermissionsEditroles extends Mage_Adminhtml_Block_Permissions_Editroles
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        $id = $this->getRequest()->getParam('rid');
		$storeCategories = Mage::getResourceModel('aitpermissions/advancedrole_collection')->loadByRoleId($id);
		Mage::register('store_categories', $storeCategories);
        
        $this->addTab('advanced', array(
            'label'     => Mage::helper('aitpermissions')->__('Advanced Permissions'),
            'content' => $this->getLayout()->createBlock('aitpermissions/adminhtml_permissions_tab_advanced')->toHtml()           
        ));

        return $this;
    }
} } 