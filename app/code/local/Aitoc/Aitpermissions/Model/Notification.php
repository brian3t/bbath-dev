<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Notification.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ QicmRCORfgjcVryd('7a30fa331967582df0a4100506f4b878'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Model_Notification extends Mage_Core_Model_Abstract
{
    const XML_PATH_EMAIL_SENDER     = 'contacts/email/sender_email_identity';
    
    public function send($product)
    {
        $vars = array();
        $suEmail = Mage::getStoreConfig('admin/su/email');
        if ($suEmail == "")
        {
            return false;
        }
        $vars = $this->_prepareVars($product);
        
      //  echo "<pre>"; print_r($product->toArray()); exit;
       // echo "<pre>"; print_r($vars); exit;
        //$email = Mage::getConfig('admin/su/template');
        $name = 'Advanced Permissions Notification';
        $storeId = $product->getStoreId();
        
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
        
        $mailTemplate = Mage::getModel("core/email_template");
        $mailTemplate->setDesignConfig(array("area"=>"frontend", "store"=>$storeId));
        $mailTemplate->setTemplateSubject($name);
        $emailId = Mage::getStoreConfig('admin/su/template',$storeId);
        $mailTemplate->sendTransactional(
            $emailId,
            Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $storeId),
            $suEmail,
            $name,
            $vars
        );
        $translate->setTranslateInline(true);
        if ($mailTemplate->getSentSuccess())
        {
            return true;
            
        }
        return false;
    }
    
    protected function _prepareVars($product)
    {
        return array('product_name' => $product->getName(),
                     'product_sku'  => $product->getSku(),
                     'product_price'=> $product->getPrice(),
                     'admin_name'   => Mage::getSingleton('admin/session')->getUser()->getName(),
                     'website'      => Mage::getBaseUrl(),
                    );
    }   
} } 