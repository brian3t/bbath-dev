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
 * Shipping MatrixRates
 *
 * @category   Webshopapps
 * @package    Webshopapps_Premiumrate
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt
 * @author     Karen Baker <sales@webshopapps.com>
*/


class Webshopapps_Premiumrate_Model_Carrier_Premiumrate
    extends Webshopapps_Wsacommon_Model_Shipping_Carrier_Baseabstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'premiumrate';
    protected $_default_condition_name = 'package_standard';

    protected $_conditionNames = array();

    public function __construct()
    {
        parent::__construct();
        foreach ($this->getCode('condition_name') as $k=>$v) 
        {
            $this->_conditionNames[] = $k;
        }
    }

    /**
     * Sets the request with new values where required for shipping calculation 
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
    	$freeShippingOnly = 0;
    	$request->setPRConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);

    	$this->_rawRequest = $request;
    	
     	//Exclude virtual products price from package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) 
        {
            foreach ($request->getAllItems() as $item) 
            {       	
                if ($item->getParentItem()) 
                {  
                	continue;  
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) 
                {
                    foreach ($item->getChildren() as $child) 
                    {
                        if ($child->getProduct()->isVirtual()) 
                        {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual()) 
                {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }
        
     	if ($this->getConfigFlag('use_tax_incl')) 
     	{
        	$request->setPackageValue($this->getTotalPrice($request, $this->getConfigFlag('use_discount')));	
        } 
       	
        if ($this->getConfigFlag('use_discount') && !$this->getConfigFlag('use_tax_incl')) {
        	$request->setPackageValue($this->getDiscountPrice($request));
        }
        
        $this->_rawRequest->setIgnoreFreeItems(false);   
    }
    protected function _getQuotes() 
    {
    	if ($this->getConfigFlag('custom_sorting')) 
    	{
        	$result = Mage::getModel('premiumrate_shipping/rate_result');
        } else 
        {
			$result = Mage::getModel('shipping/rate_result');
        }
        
        $request = $this->_rawRequest;    

         $version = Mage::helper('wsacommon')->getVersion();
        // this is a fix for 1.4.1.1 and earlier versions where the free ship logic used for UPS doesnt work
        if (($version == 1.6 || $version == 1.7 || $version == 1.8) ) {
	
        	if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes() ) {
        	    $method = Mage::getModel('shipping/rate_result_method');
				$method->setCarrier('premiumrate');
				$method->setCarrierTitle($this->getConfigData('title'));
				$method->setMethod(strtolower('premiumrate_'.$this->getConfigData('free_method_text')));
				$method->setPrice('0.00');
				$method->setMethodTitle($this->getConfigData('free_method_text'));
				$result->append($method);
				return $result;
        	}
			  	
		}
        
        $rateArray = $this->getRate($request); 
        
    	if (empty($rateArray)) 
    	{
     		if ($this->getConfigData('specificerrmsg')!='') 
     		{
	            $error = Mage::getModel('shipping/rate_result_error');
	            $error->setCarrier('premiumrate');
	            $error->setCarrierTitle($this->getConfigData('title'));
	            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
	            $result->append($error);
     		}
     		return $result;
    	}
    	
    	if($this->getConfigFlag('lowest_price_free'))
		{
	     	$minValue = 99999;
	     	$lowRate;
	     	
	     	foreach($rateArray as $key=>$rate)
	     	{
		     	if(!empty($rate)&& $rate['price'] >= 0)
		     	{
		     		if($rate['price'] < $minValue)
					{
						$minValue = $rate['price'];
						$lowRate = $key;
					}				
		     	}
	     	}		
     		$rateArray[$lowRate]['price'] = 0.00; 
     		if ($this->getConfigData('free_method_text') != "") 
     		{
     			$rateArray[$lowRate]['delivery_type'] = $this->getConfigData('free_method_text');
     		}  
		}
		
		foreach ($rateArray as $rate)
		{			
			if (!empty($rate) && $rate['price'] >= 0) 
			{											
			  	$method = Mage::getModel('shipping/rate_result_method');

				$method->setCarrier('premiumrate');
				$method->setCarrierTitle($this->getConfigData('title'));

				$modifiedName=preg_replace('/&|;| /',"_",$rate['method_name']);
				$method->setMethod($modifiedName);

				$method->setMethodTitle(Mage::helper('shipping')->__($rate['delivery_type']));

				$shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
				$method->setCost($rate['cost']);
				$method->setDeliveryType($rate['delivery_type']);
        		
				$method->setPrice($shippingPrice);

				$result->append($method);				
			}
		}
		return $result;
    }    

    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
    	return Mage::getResourceModel('premiumrate_shipping/carrier_premiumrate')->getNewRate($request);
    }   

    public function getCode($type, $code='')
    {
        $codes = array(

            'condition_name'=>array(
        		'package_standard'   	=> Mage::helper('shipping')->__('Standard'),
        		'package_volweight'   	=> Mage::helper('shipping')->__('Volume Weight'),
        ),

            'condition_name_short'=>array(
                'package_standard' 		=> Mage::helper('shipping')->__('Standard'),
        		'package_volweight'   	=> Mage::helper('shipping')->__('Volume Weight'),
        ),
        'postcode_filtering'=>array(
            	'uk'  					=> Mage::helper('shipping')->__('UK'),
        		'canada'  				=> Mage::helper('shipping')->__('Canada Ranges'),
        		'numeric'  				=> Mage::helper('shipping')->__('Numerical Ranges (US/AUS/FR/etc)'),
        		'uk_numeric'  			=> Mage::helper('shipping')->__('Both UK and Numeric'),
        		'pattern'  				=> Mage::helper('shipping')->__('Pattern Matching'),
        ),

        );

        if (!isset($codes[$type])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Premium Matrix Rate code type: %s', $type));
        }

        if (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Premium Matrix Rate code for type %s: %s', $type, $code));
        }

        return $codes[$type][$code];
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
       if (!$this->getConfigFlag('user_codes')) {
    		return $this->getSimpleAllowedMethods();
       }
    	
       $collection = Mage::getResourceModel('premiumrate_shipping/carrier_premiumrate_collection');
       $collection = $collection->setDistinctDeliveryCodeFilter();
       $collection->load();
       $allowedMethods=array();
       $deliveryTypes=array();
       foreach ($collection->getItems() as $item) {

	       if ($item['algorithm']!="") 
	       {
				$algorithm_array=explode("&",$item['algorithm']);  // Multi-formula extension
				reset($algorithm_array);
				$skipData=false;
				foreach ($algorithm_array as $algorithm_single) 
				{
					$algorithm=explode("=",$algorithm_single,2);
					if (!empty($algorithm) && count($algorithm)==2) 
					{
						if (strtolower($algorithm[0])=="m") 
						{
							$item['delivery_type']=$algorithm[1];
						}
					}
				}
	       }       
	       $newDelType=preg_replace('/&|;| /',"_",$item->getData('delivery_type'));
	       $deliveryTypes[]=$newDelType;	       	
	       $allowedMethods[$newDelType] = $item->getData('delivery_type');
       }
       return $allowedMethods;
    }
    
    
    public function getSimpleAllowedMethods() 
    {    
       $collection = Mage::getResourceModel('premiumrate_shipping/carrier_premiumrate_collection');
       $collection = $collection->setDistinctDeliveryTypeFilter();
       $collection->load();
       $allowedMethods=array();
       $deliveryTypes=array();
       
       foreach ($collection->getItems() as $item) 
       {
       	  $newDelType=preg_replace('/&|;| /',"_",$item->getData('delivery_type'));	
       	  $deliveryTypes[]=$newDelType;
	      $allowedMethods[$newDelType] = $item->getData('delivery_type');
       }
       return $allowedMethods;
    }    
    
    /**
     * NOTE: This code is 1.4/1.5 specific - do not overwrite with 1.3 code
     * @param unknown_type $request
     * @return number
     */
     private function getTotalPrice($request, $discounted)
     {   	
		$totalCart=0;
		$discount = $discounted; 
		
    	$items = $request->getAllItems();
        if ($items != array())
        {
         	foreach ($items as $item)
         	{
         		if(!$this->getConfigFlag('include_virtual_price') && $item->getProduct()->isVirtual())
         		{
         			continue;
         		}              	
	         	if ($discount) 
	         	{
	         		if( Mage::helper('wsacommon')->getVersion() >= 1.8 ) {
						$totalCart += $item->getRowTotalInclTax()-$item->getDiscountAmount();
	         		} else {
	         			$totalCart += $item->getRowTotal()+$item->getTaxAmount();
	         		}
	         	} else 
	         	{
	         		if( Mage::helper('wsacommon')->getVersion() >= 1.8 ) {
	         			$totalCart += $item->getRowTotalInclTax();
	         		} else {
	         			$totalCart += $item->getRowTotalInclTax();
	         		}
	         	}
        	}
        }
        if ($totalCart==0) 
        {
        	$totalCart = $request->getPackageValue();
        }
    	return $totalCart;   
    }
    
    private function getDiscountPrice($request)
    {
    	$totalCart=$request->getPackageValue();
		
    	$items = $request->getAllItems();
        if ($items != array())
        {
         	foreach ($items as $item)
         	{
         		if(!$this->getConfigFlag('include_virtual_price') && $item->getProduct()->isVirtual())
         		{
         			continue;
         		} 
         		$totalCart -= $item->getDiscountAmount();       	
        	}
        }        
    	return $totalCart;       	
    }
    
}
