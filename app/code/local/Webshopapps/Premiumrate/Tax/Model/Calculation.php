<?php
class Webshopapps_Premiumrate_Tax_Model_Calculation extends Mage_Tax_Model_Calculation
{
	
    protected function _getRequestCacheKey($request)
    {
        //$key = $request->getStore() ? $request->getStore()->getId() . '|' : '';
        $key = $request->getStore() ? $request->getStoreId() . '|' : '';
        $key.= $request->getProductClassId() . '|' . $request->getCustomerClassId() . '|'
            . $request->getCountryId() . '|'. $request->getRegionId() . '|' . $request->getPostcode();
        return $key;
    }

}