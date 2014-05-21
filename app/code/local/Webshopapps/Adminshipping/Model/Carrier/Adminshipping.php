<?php
/**
 * Magento Webshopapps Shipping Module
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
 * Conditional Free Shipping Module - where if attribute exclude_free_shipping is set
 * will result in free shipping being disabled for checkout
 *
 * @category   Webshopapps
 * @package    Webshopapps_Freighrate
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt
 * @author     Karen Baker <sales@webshopapps.com>
 * @version    1.5
*/
/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Adminshipping_Model_Carrier_Adminshipping
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'adminshipping';
    
    protected $_modName = 'Webshopapps_Adminshipping';
	
    /**
     * FreeShipping Rates Collector
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
    	
    	if ($shipData = Mage::registry('adminship_data')) {
    		$result = Mage::getModel('shipping/rate_result');
	    	$method = Mage::getModel('shipping/rate_result_method');
	    	$method->setCarrier('adminshipping');
			$method->setPrice($shipData['shipping_amount']);
	    	$method->setCarrierTitle($this->getConfigData('title'));
	    	$method->setMethod('adminshipping');
	    	$method->setMethodTitle($shipData['shipping_description']);
	    	$result->append($method);
	    	
    		return $result;
    	} else {
        	return Mage::getModel('shipping/rate_result');
    	}
    }

    public function getAllowedMethods()
    {
        return array('adminshipping'=>'adminshipping');
    }

}
