<?php

require_once '../app/Mage.php';

Varien_Profiler::enable();

Mage::setIsDeveloperMode(true);

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

Mage::app();


if(isset($_FILES['uploadedfile']['name'])) {

	$target_path = 'upload/orders.csv';
	
	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
		
		unset($errors);
	    csv_file_to_mysql_table('upload/orders.csv', 'bulk_import');
	    $db = Mage::getSingleton('core/resource')->getConnection('core_read');
		$orders = $db->query('SELECT `PO Number`, `Ship-To Location`, `Delivery Date Requested` from bulk_import group by `Ship-To Location`');
		//$pos = $db->fetchAssoc('SELECT po_number from sales_flat_order_payment where po_number is not null');
		//unset($pos['po_number']);
		//$poFilter = array_keys($pos);

	    foreach($orders as $order) {
	    	//$errors .= createOrder($order['PO Number'], $order['Ship-To Address 1']);
	    	//if(in_array($order['PO Number'], $poFilter)) {
		    	//$errors .= 'PO ' . $order['PO Number'] . ' has already been used before - Order not created.<br>';
	    	//} else {
		    	//if(strtotime($order['Delivery Date Requested']) < strtotime('+1 Day')){
					$errors .= createOrder($order['PO Number'], $order['Ship-To Location']);
				//} else {
					//$errors .= 'Ship date is too far in advance for PO ' . $order['PO Number'] . ' - Order not created.<br>';
				//}
	    	//}
		}

		echo $errors;
	} else{
	    echo 'There was an error uploading the file, please try again!';
	}
}

function createOrder($poNumber,$storeId) {

	$quote = Mage::getModel('sales/quote')->setStoreId(2);
	
	if ('do customer orders') {
		// for customer orders:
		$customer = Mage::getModel('customer/customer')->setWebsiteId(2)->setStoreId(2)->loadByEmail('supportbbb@bloomingbath.com');
		$quote->assignCustomer($customer);
	} else {
		// for guesr orders only:
		$quote->setCustomerEmail('supportbbb@bloomingbath.com');
	}
	foreach ($customer->getAddresses() as $address) {
		if(substr($address->company, strpos($address->company, "#")+1, 4) == $storeId) {
			$addressData = array(
				'firstname' => $address->firstname,
				'lastname' => $address->lastname,
				'street' => $address->street,
				'city' => $address->city,
				'postcode' => $address->postcode,
				'telephone' => $address->telephone,
				'country_id' => $address->country_id,
				'region_id' => $address->region_id,
				'company' => $address->company
			);
		}
	}
	if(isset($addressData)) {
		
		$db1 = Mage::getSingleton('core/resource')->getConnection('core_read');
		$items = $db1->query("SELECT `Buyer Item Nbr`, `Quantity` from bulk_import where `Ship-To Location` = '" . $storeId . "'");
		
		$date = time();
		$dotw = $dotw = date('w', $date);
		$end = ($dotw == 5 /* Friday */) ? $date : strtotime('next Friday', $date);
		$weekStart = $end - (7 * 24*60*60);
		$weekStart = date('Y-m-d', $weekStart);
		
		/*
$orders = Mage::getModel('sales/order')->getCollection()
		    	->addAttributeToFilter('created_at', array('from'  => $weekStart))
				->addAttributeToFilter('customer_id', array('eq'  => 1115));
				
		foreach($orders as $order) {
			if($order->getShippingAddress()->getCompany() == $addressData['company']) {
				$order_error = 'WARNING - ' . $order->getShippingAddress()->getCompany() . ' already ordered this week.  Order was still created.<br>';
			}
	        $order_data[$i] = array(
	            $order->getCreatedAt(),
	            $order->getIncrementId(),
	            $order->getShippingAddress()->getCompany()
	        );
	        $i++;
	        //print_r($order_data);
		}
*/

		
		foreach($items as $item) {
	    	$orderItem = Mage::getModel('catalog/product')->loadByAttribute('upc', $item['Buyer Item Nbr']);	
			// add product(s)
			$product = Mage::getModel('catalog/product')->load($orderItem->getId());
			$buyInfo = array(
				'qty' => $item['Quantity'],
				// custom option id => value id
				// or
				// configurable attribute id => value id
			);
			$quote->addProduct($product, new Varien_Object($buyInfo));
		}
		
		$billingAddress = $quote->getBillingAddress()->addData($addressData);
		$shippingAddress = $quote->getShippingAddress()->addData($addressData);
		
		$shippingAddress->setFreeShipping(true)
		        ->setCollectShippingRates(true)->collectShippingRates()
		        ->setShippingMethod('freeshipping_freeshipping')
		        ->setPaymentMethod('purchaseorder');
		
		$quote->getPayment()->importData(array('method' => 'purchaseorder', 'po_number' => $poNumber));
		
		$quote->collectTotals()->save();
		
		$service = Mage::getModel('sales/service_quote', $quote);
		$service->submitAll();
		$order = $service->getOrder();
		$order_id = $order->getIncrementId();
		
		$orderInvoice = Mage::getModel('sales/order')->loadByIncrementId($order_id);
		
		if(!$orderInvoice->canInvoice()) {
			Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
		}
		$invoice = Mage::getModel('sales/service_order', $orderInvoice)->prepareInvoice();
		if (!$invoice->getTotalQty()) {
			Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
		}
		$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
		$invoice->register();
		$transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
		$transactionSave->save();
		
		$orderInvoice->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
		
		echo 'Created order ' . $order_id .  ' for PO ' . $poNumber . '.<br>';
		unset($addressData);
	} else {
		$order_error = 'Store address not found for PO ' . $poNumber . '  - Order not created.<br>';
	}
	return $order_error;

}

function csv_file_to_mysql_table($source_file, $target_table, $max_line_length=10000) {
  $db = Mage::getSingleton('core/resource')->getConnection('core_read');
  $db->query("TRUNCATE TABLE $target_table");
  if (($handle = fopen("$source_file", "r")) !== FALSE) {
      $columns = fgetcsv($handle, $max_line_length, ",");
      foreach ($columns as &$column) {
          $column = "`" . str_replace(".","",$column) . "`";
      }
      $insert_query_prefix = "INSERT INTO $target_table (".join(",",$columns).")\nVALUES";
      while (($data = fgetcsv($handle, $max_line_length, ",")) !== FALSE) {
          while (count($data)<count($columns))
              array_push($data, NULL);
          $query = "$insert_query_prefix (".join(",",quote_all_array($data)).");";
          //echo $query;
          $db->query($query);
      }
      fclose($handle);
      unlink($source_file);
  }
}

function quote_all_array($values) {
  foreach ($values as $key=>$value)
      if (is_array($value))
          $values[$key] = quote_all_array($value);
      else
          $values[$key] = quote_all($value);
  return $values;
}

function quote_all($value) {
  if (is_null($value))
      return "NULL";

  $value = "'" . $value . "'";
  return $value;
}

?>
<!DOCTYPE html>
<html lang="en-us">
<head>
  <meta charset="utf-8" />
  <title>Order Import</title>
  <style>
  body {
    background-color: #EBF1F7;
    text-align: center;
    padding-top: 20px;
    font-family: Helvetica, "Helvetica Neue", Arial, sans-serif;
    text-shadow: 0px 1px 0px rgba(255,255,255, .7);
    color: rgba(0,0,0, .7);
  }
  body:before {
    content: "";
    position: fixed;
    top: -10px;
    left: 0;
    width: 100%;
    height: 10px;
    -webkit-box-shadow: 0px 0px 10px rgba(0,0,0,.8);
    -moz-box-shadow: 0px 0px 10px rgba(0,0,0,.8);
    box-shadow: 0px 0px 10px rgba(0,0,0,.8);
    z-index: 100;
  }
  </style>
</head>
<body>
  <div style="width:600px;margin: auto;">
  <img style="clear:both" src="logo.png" alt="Egghead Ventures" />
  <form enctype="multipart/form-data" action="bulk-import.php" method="POST" style="width: 600px;float: left;text-align: left;">
    <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
    Choose the BuyBuyBaby file you downloaded from Covalentworks: <input name="uploadedfile" type="file" /><br />
    <input type="submit" value="Upload File" />
  </form>
  </div>
</body>
</html>

