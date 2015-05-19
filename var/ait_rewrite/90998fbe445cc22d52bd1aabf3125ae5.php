<?php
/* DO NOT MODIFY THIS FILE! THIS IS TEMPORARY FILE AND WILL BE RE-GENERATED AS SOON AS CACHE CLEARED. */

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Order create model
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Aitoc_Aitpermissions_Model_Rewrite_AdminSalesOrderCreate extends Mage_Adminhtml_Model_Sales_Order_Create
{
	
	public function importPostData($data)
    {
    	
    	$shipAmount = 0;
    	$shipDesc = '';
        $found = false;
    	       
    	if (isset($data['shipping_amount'])) {
            $shipAmount  = $data['shipping_amount'];
            $found = true;
    	}
        
    	if (isset($data['shipping_description'])) {
            $shipDesc = $data['shipping_description'];
            $found = true;
    	}
        
        if ($found) {
        	Mage::register('adminship_data', new Varien_Object(array(
	            'shipping_amount'  => $shipAmount,
	            'shipping_description' => $shipDesc,
        	)));
        	$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        	
        } else {
        	Mage::unregister('rule_data');
        }
        
    	return parent::importPostData($data);
    }
	
	
}


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

class Webshopapps_Adminshipping_Adminhtml_Model_Sales_Order_Create extends Aitoc_Aitpermissions_Model_Rewrite_AdminSalesOrderCreate
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

