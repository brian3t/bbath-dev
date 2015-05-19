<?php

require_once '../app/Mage.php';

Varien_Profiler::enable();

Mage::setIsDeveloperMode(true);

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

Mage::app();


if(isset($_FILES['uploadedfile']['name'])) {

	$target_path = 'upload/orders.csv';
	define(TARGET_ID, 3994);
	define(BBB_ID, 1115);
	
	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
		
		unset($errors);
	    csv_file_to_mysql_table('upload/orders.csv', 'bulk_import');
	    
	    $customer = $_POST["CUSTOMER"];
	    if($customer == "BBB") {
		    $customer_id = BBB_ID;
	    } elseif($customer == "TARGET") {
		    $customer_id = TARGET_ID;
	    } else {
		    echo 'Customer Not Found';
		    exit;
	    }
	    $db = Mage::getSingleton('core/resource')->getConnection('core_read');
		$orders = $db->query('SELECT `PO Number`, `Ship-To Location`, `Ship-To Address 1`, `Delivery Date Requested` from bulk_import group by `PO Number`');
		$pos = $db->fetchAssoc('SELECT po_number, customer_id from sales_flat_order_payment LEFT JOIN sales_flat_order ON sales_flat_order_payment.parent_id = sales_flat_order.entity_id where po_number is not NULL AND customer_id = ' . $customer_id);	
		unset($pos['po_number']);
		$poFilter = array_keys($pos);
		//print_r($poFilter);

	    foreach($orders as $order) {
	    	if($customer_id == BBB_ID) {
		    	$storeId = preg_replace("/[^0-9]/","", $order['Ship-To Address 1']);
	    	} elseif($customer_id == TARGET_ID) {
		    	$storeId = trim($order['Ship-To Location']);
	    	} else {
		    	echo 'Fail';
		    	exit;
	    	}
	    	
	    	if(in_array($order['PO Number'], $poFilter)) {
		    	$errors .= 'PO ' . $order['PO Number'] . ' has already been used before - Order not created.<br>';
	    	} else {
		    	if(strtotime($order['Delivery Date Requested']) > strtotime('+1 Day')){
					$errors .= 'Ship date is future for PO ' . $order['PO Number'] . ' - Order Still Created And Place ON HOLD.<br>';
					$hold = true;
				} 
					$errors .= createOrder($order['PO Number'], $customer_id, $storeId, $hold);
					//echo $order['PO Number'] . ' - ' . $storeId . '<br>';
	    	}
		}

		echo $errors;
	} else{
	    echo 'There was an error uploading the file, please try again!';
	}
}

function createOrder($poNumber, $customer_id, $storeId, $hold = false) {

	$quote = Mage::getModel('sales/quote')->setStoreId(2);

	$customer = Mage::getModel('customer/customer')->setWebsiteId(2)->setStoreId(2)->load($customer_id);
	$quote->assignCustomer($customer);

	$date = time();
	$dotw = $dotw = date('w', $date);
	$end = ($dotw == 5 /* Friday */) ? $date : strtotime('next Friday', $date);
	$weekStart = $end - (7 * 24*60*60);
	$weekStart = date('Y-m-d', $weekStart);
	
	$orders = Mage::getModel('sales/order')->getCollection()
	    	->addAttributeToFilter('created_at', array('from'  => $weekStart))
			->addAttributeToFilter('customer_id', array('eq'  => $customer_id));
			
	foreach($orders as $order) {
		$orderStoreId = preg_replace("/[^0-9]/","", $order->getShippingAddress()->getCompany());
		if($storeId == $orderStoreId) {
			$order_error = 'WARNING - ' . $order->getShippingAddress()->getCompany() . ' already ordered this week.  Order was still created so put ON HOLD.<br>';
			$hold = true;
		}
/*
        $order_data[$i] = array(
            $order->getCreatedAt(),
            $order->getIncrementId(),
            $order->getShippingAddress()->getCompany()
        );
        $i++;
*/
        //print_r($order_data);
	}
	
	$db1 = Mage::getSingleton('core/resource')->getConnection('core_read');
	
	$addresses = $db1->fetchAll("SELECT * from bulk_import where `PO Number` = '" . $poNumber . "' LIMIT 1");
	
	if($customer_id == BBB_ID) {
		foreach ($addresses as $address) {
			$regionModel = Mage::getModel('directory/region')->loadByCode($address['Ship-To State'], 'US');
			$regionId = $regionModel->getId();
			$addressData = array(
				'firstname' => 'Receiving',
				'lastname' => 'Department #' . $storeId,
				'street' => $address['Ship-To Address 2'],
				'city' => $address['Ship-To City'],
				'postcode' => $address['Ship-To Postal Code'],
				'telephone' => '(000) 000-0000',
				'country_id' => 'US',
				'region_id' => $regionId,
				'company' => $address['Ship-To Address 1']
			);
			$shipMethod = $address['hdr_user_defined_field20'];
		}
	} elseif($customer_id == TARGET_ID) {
		foreach ($addresses as $address) {
			$regionModel = Mage::getModel('directory/region')->loadByCode($address['Ship-To State'], 'US');
			$regionId = $regionModel->getId();
			$addressData = array(
				'firstname' => 'Receiving',
				'lastname' => 'Department #' . $storeId,
				'street' => $address['Ship-To Address 1'],
				'city' => $address['Ship-To City'],
				'postcode' => $address['Ship-To Postal Code'],
				'telephone' => '(000) 000-0000',
				'country_id' => 'US',
				'region_id' => $regionId,
				'company' => $address['Ship-To Name']
			);
			$shipMethod = $address['hdr_user_defined_field20'];
		}
	}

	$items = $db1->query("SELECT `Buyer Item Nbr`, `Manufacturer Item Nbr`, `Quantity` from bulk_import where `PO Number` = '" . $poNumber . "'");
	
	foreach($items as $item) {
		if($customer_id == BBB_ID) {
    		$orderItem = Mage::getModel('catalog/product')->loadByAttribute('upc', $item['Buyer Item Nbr']);
    	} elseif($customer_id == TARGET_ID) {
	    	$orderItem = Mage::getModel('catalog/product')->loadByAttribute('upc', $item['Manufacturer Item Nbr']);
    	}
		// add product(s)
		$product = Mage::getModel('catalog/product')->load($orderItem->getId());
		$buyInfo = array(
			'qty' => $item['Quantity'],
		);
		$quote->addProduct($product, new Varien_Object($buyInfo));
	}
	
	$billingAddress = $quote->getBillingAddress()->addData($addressData);
	$shippingAddress = $quote->getShippingAddress()->addData($addressData);
	
	if($shipMethod == '') {
		Mage::register('adminship_data', new Varien_Object(array(
	        'shipping_amount'  => '0',
	        'shipping_description' => 'Prepaid',
		)));
	} else {
		Mage::register('adminship_data', new Varien_Object(array(
	        'shipping_amount'  => '0',
	        'shipping_description' => $shipMethod,
		)));
	}
	
	$shippingAddress->setCollectShippingRates(true)->collectShippingRates()
	        ->setShippingMethod('adminshipping_adminshipping')
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
	if($hold == true) {
		$orderInvoice->hold()->save();
	}
	
	echo 'Created order ' . $order_id .  ' for PO ' . $poNumber . '.<br>';
	unset($addressData);
	unset($hold);
	Mage::unregister('adminship_data');

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
  select {
	  -webkit-appearance: none;
	  font-size: 20px;
	  padding: 10px;
  }
  input[type="submit"] {
	  -webkit-appearance: none;
	font-size: 20px;
	border-radius: 5px;
	background-color: rgb(50, 205, 235);
	border: 1px solid #ccc;
	padding: 10px;
  }
  </style>
</head>
<body>
  <div style="width:600px;margin: auto;">
  <img style="clear:both" src="logo.png" alt="Egghead Ventures" />
  <form enctype="multipart/form-data" action="bulk-import.php" method="POST" style="width: 600px;float: left;text-align: left;">
    <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
    <p>Choose the file you downloaded from Covalentworks:</p>
    <p>
    <select name="CUSTOMER">
    	<option value="">--Select Customer--</option>
    	<option value="BBB">buybuyBaby</option>
    	<option value="TARGET">Target Stores</option>
    </select>
    </p>
    <p>
    	<input name="uploadedfile" type="file" />
    </p>
    <p>
    	<input type="submit" value="Upload File" />
    </p>
  </form>
  </div>
</body>
</html>

