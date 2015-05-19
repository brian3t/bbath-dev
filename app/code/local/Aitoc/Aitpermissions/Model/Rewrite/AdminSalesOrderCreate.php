<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/AdminSalesOrderCreate.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ fpUgNIdNXeZUtyMl('c6e950eddad07ab46f71bdfb49bec8ac'); ?><?php

class Aitoc_Aitpermissions_Model_Rewrite_AdminSalesOrderCreate extends Mage_Adminhtml_Model_Sales_Order_Create
{
    public function initFromOrder(Mage_Sales_Model_Order $order)
    {
        try {
            parent::initFromOrder($order);
        } catch (Mage_Core_Exception $e) {
              //  Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                return Mage::app()->getFrontController()->getResponse()->setRedirect(getenv("HTTP_REFERER"));
            } catch (Exception $e) {
               // Mage::getSingleton('adminhtml/session')->addException($e, $e->getMessage());
                return Mage::app()->getFrontController()->getResponse()->setRedirect(getenv("HTTP_REFERER"));
            }
        return $this;
    }
} } 