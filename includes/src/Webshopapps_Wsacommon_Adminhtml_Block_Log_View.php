<?php
class Webshopapps_Wsacommon_Adminhtml_Block_Log_View extends Mage_Catalog_Block_Product_Abstract {
	 
    public function __construct() {
        parent::__construct();
        $this->setTemplate('webshopapps_wsacommon/view.phtml');
        $this->setNotificationId($this->getRequest()->getParam('notification_id', false));
    }


    public function getMessageData() {
        if( $this->getNotificationId()) {
        	Mage::log(Mage::getModel('wsacommon/log')
	        			->load($this->getNotificationId()));
	        return Mage::getModel('wsacommon/log')
	        			->load($this->getNotificationId());
        } else {
        	throw new Exception("No Notification Id given");
        }
    }

    public function getBackUrl() {
        return Mage::helper('adminhtml')->getUrl('*/adminhtml_log');
    }
    
}

