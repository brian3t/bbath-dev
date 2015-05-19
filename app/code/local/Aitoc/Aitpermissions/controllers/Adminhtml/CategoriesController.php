<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/controllers/Adminhtml/CategoriesController.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ RwCyThWTNMeCOakV('b729e10589212859dbf45c1976cca4c2'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Adminhtml_CategoriesController extends Mage_Adminhtml_Controller_action
{
	protected function _init()
	{
        $id = $this->getRequest()->getParam('rid');
		$storeCategories = Mage::getResourceModel('aitpermissions/advancedrole_collection')->loadByRoleId($id);
		Mage::register('store_categories', $storeCategories);
	}

    public function categoriesJsonAction()
    {
        $this->_init();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('aitpermissions/adminhtml_store_switcher')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'), $this->getRequest()->getParam('store')));
    }

    public function categoriesAction()
    {
        $this->_init();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('aitpermissions/adminhtml_permissions_tab_advanced')->toHtml()
        );
    }
} } 