<?php
class Magebuzz_Dealerlocator_IndexController extends Mage_Core_Controller_Front_Action
{
	public function distance($lat1, $lon1, $lat2, $lon2, $unit) { 
	  $theta = $lon1 - $lon2; 
	  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	  $dist = acos($dist); 
	  $dist = rad2deg($dist); 
	  $miles = $dist * 60 * 1.1515;
	  if ($unit == 1) {
		return ($miles * 1.609344); 
	  } else {
		return $miles;
	  }
	}
	
    public function indexAction()
    {
		$this->loadLayout();  
		$this->getLayout()->getBlock('head')->setTitle('Dealer Locator');
		$this->renderLayout();
    }
	
	public function searchAction()
	{
		$collection = Mage::getModel('dealerlocator/dealerlocator')->getCollection()->addFieldToFilter('status',1);
		$locations = array();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$units = Mage::getStoreConfig('magebuzz/google_map_options/distance_units');
			//$radius = Mage::getStoreConfig('magebuzz/google_map_options/default_search_radius');
			$radius = $data['distance']; //**EGGHEAD ADDED

			Mage::getSingleton('core/session')->setDistance($radius);

			if($data['address']) {
				Mage::getSingleton('core/session')->setSearchedAddress($data['address']);
				if($data['longitude']) {
					$centerLatitude = $data['latitude'];
					$centerLongitude = $data['longitude'];
				} else {
					$address = urlencode($data['address']);
					$json = file_get_contents(Mage::getStoreConfig('magebuzz/google_map_options/google_geo_api_url')."?address=$address&sensor=false");
					$json = json_decode($json);
					$centerLatitude = strval($json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'});
					$centerLongitude = strval($json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'});
				}
				foreach($collection as $item) {
					$distance = $this->distance($item['latitude'], $item['longitude'], $centerLatitude, $centerLongitude, $units);
					if( $distance <= $radius && !in_array($item->getData(), $locations)) {
						$item->distance = number_format($distance, 2, '.', '');
						array_push($locations, $item->getData());
					}
				}
				$cmp = function($a,$b) {return $a['distance'] - $b['distance'];};
				usort($locations, $cmp);
				Mage::getSingleton('core/session')->setIsAddressSearch(true);
				Mage::getSingleton('core/session')->setSearchLatitude($centerLatitude);
				Mage::getSingleton('core/session')->setSearchLongitude($centerLongitude);
			}
			
			Mage::getSingleton('core/session')->setSearchLocations($locations);
			Mage::getSingleton('core/session')->setIsSearch(true);
			$this->_redirectUrl(Mage::getBaseUrl().'dealer-locator');
		}
	}
}