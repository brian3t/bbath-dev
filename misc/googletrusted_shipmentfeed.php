<?php
/* Author: JTS
	Description: Magento Google "Shipment Feed"  for Trusted Stores
	Notes:  If you have more shippers you'll need to translate them in the "shiptrans" array.
			Right now it pulls the past day's worth of date.  You can adjust the range via the "from_date" and "to_date".
Google Feed Instructions:
	https://support.google.com/trustedstoresmerchant/bin/answer.py?hl=en&answer=2609890&ctx=go
 
*/
require_once '../app/Mage.php';
Mage::app();
 
//setup translations
	$shiptrans['usps']='USPS';
	$shiptrans['ups']='UPS';
	$shiptrans['fedex']='FedEx';
//setup variables
$SEP="\t";
 
 
$from_date_time = strtotime("-1 day"); // begin date
//$period_date_to = $this->getRequest()->getParam('report_to');
$to_date_time = strtotime("+1 day");
 
$orders=Mage::getModel('sales/order');
$to_date = date('Y-m-d' . ' 00:00:00', $to_date_time);
$from_date = date('Y-m-d' . ' 00:00:00', $from_date_time);
$orders = Mage::getModel('sales/order')->getCollection()
    ->addFieldToFilter('updated_at', array('from'=>$from_date, 'to'=>$to_date))
	->addAttributeToSort('updated_at', 'DSC')
    ;
 
$string='';
print"merchant order id\ttracking number\tcarrier code\tother carrier name\tship date\n";
foreach ($orders as $order) {
	$id=$order->getId() ;
	$objOrder	= Mage::getModel('sales/order')->load($id);
 
	$inc_id=$order->getIncrementId(); //7/10/12: Change to increment id
		//we need: order_id/tracking number/carrier code /ship date
		$shipments = $objOrder->getShipmentsCollection();
		if(count($shipments)):
			foreach($shipments as $shipment):
				$tracking = $shipment->getAllTracks();
				#var_dump($tracking);
				#return;
				$shipdate=cleanString($shipment->getCreatedAt());
				if($shipdate!=''){
					$shipdate_time=strtotime($shipdate);
					$shipdate=date('Y-m-d', $shipdate_time);
				}else{
					continue;
				}
				//7/10/12 make sure the shipment is greater than our from date
				if($shipdate_time<$from_date_time){
					continue;
				}
 
 
				if(count($tracking)):
					foreach($tracking as $track):
						$track_no=cleanString($track->getNumber());
						$other='';
						$carrier=cleanString($track->getCarrierCode());
						//echo $carrier;
						$cartrans=$shiptrans[$carrier];
						//if we've selected other..make it into Google's feed
						//if($cartrans==''){ $cartrans="Other"; $other='OTHER';}
						#print "<br> HERE ID:$id Carrier".$cartrans." DATE:".$date. " OTHER:".$other." TRACKING:".$track_no;

						//google's feed tab formated
						//order_id/tracking number/carrier code/other carrier/shipdate;
						print $inc_id.$SEP;
						print $track_no.$SEP;
						print $cartrans.$SEP;
						print $other.$SEP;
						print $shipdate;
						print "\n";
 
						#$sTrackingBlock .= '<tr><td>';
						#$sTrackingBlock .= $track->getTitle() . ' - ';
						#$sTrackingBlock .= $track->getNumber();
						#$sTrackingBlock .= '</td></tr>';
					endforeach;
				endif;
			endforeach;
		endif;
 
}
function cleanString($string){
	$string = str_replace("\\t",'',$string);
	$string = str_replace("\\n",'',$string);
	$string = str_replace("\\r",'',$string);
	return trim($string);
}
 
?>