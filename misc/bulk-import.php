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
	define(TARGET_COM_ID, 1849);
	define(TOYSRUS, 850);
	
	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
		
		unset($errors);
	    $file_contents = file_get_contents('upload/orders.csv');
        $file_contents = str_replace('\\', '~' ,$file_contents);
        file_put_contents('upload/orders.csv', $file_contents);
        
	    csv_file_to_mysql_table('upload/orders.csv', 'bulk_po_import');
	    
	    $customer = $_POST["CUSTOMER"];
	    if($customer == "BBB") {
		    $customer_id = BBB_ID;
	    } elseif($customer == "TARGET") {
		    $customer_id = TARGET_ID;
		} elseif($customer == "TARGET_COM") {
		    $customer_id = TARGET_COM_ID;
		} elseif($customer == "TOYSRUS") {
		    $customer_id = TOYSRUS;
	    } else {
		    echo 'Customer Not Found';
		    exit;
	    }
	    $db = Mage::getSingleton('core/resource')->getConnection('core_read');
		$orders = $db->query('SELECT `PO Number`, `Ship To Code`, `Ship To Address 1`, `Ship Not Before Date`, `Requested Ship Date` from bulk_po_import group by `PO Number`');
		$pos = $db->fetchAssoc('SELECT po_number, customer_id from sales_flat_order_payment LEFT JOIN sales_flat_order ON sales_flat_order_payment.parent_id = sales_flat_order.entity_id where po_number is not NULL AND customer_id = ' . $customer_id);	
		unset($pos['po_number']);
		$poFilter = array_keys($pos);
		//print_r($poFilter);

	    foreach($orders as $order) {
	    	$storeId = trim($order['Ship To Code']);
	    	
	    	if(in_array($order['PO Number'], $poFilter)) {
		    	$errors .= 'PO ' . $order['PO Number'] . ' has already been used before - Order not created.<br>';
	    	} else {
		    	if($customer == 'BBB') {
					$ship_date = $order['Requested Ship Date'];
				} else {
					$ship_date = $order['Ship Not Before Date'];
				}
		    	if(strtotime($ship_date) > strtotime('+1 Day')){
					$errors .= 'Ship date is future for PO ' . $order['PO Number'] . ' - Order Still Created And Place ON HOLD.<br>';
					$hold = true;
				}
				$errors .= createOrder($order['PO Number'], $customer_id, $storeId, $hold, $ship_date);
				//echo $order['PO Number'] . ' - ' . $storeId . '<br>';
	    	}
		}
		
		if($customer != 'BBB') {
			sendSummary();
		}
/*
		$rows = mysql_query("SELECT `PO Date`,`Ship To Code Type`,`Ship To Code`,`Ship To Address 1`,`Ship To Address 2`,`Ship To City`,`Ship To State`,`Ship To ZipCode`,`Quantity` FROM bulk_import");
		$field = mysql_num_fields($rows);

	    // create line with field names
	    for($i = 0; $i < $field; $i++) {
	      $csv_export.= mysql_field_name($query,$i).';';
	    }

		while ($row = mysql_fetch_assoc($rows)) {
			fputcsv($output, $row);
		}
*/

		echo $errors;
	} else{
	    echo 'There was an error uploading the file, please try again!';
	}
}

function createOrder($poNumber, $customer_id, $storeId, $hold = false, $ship_date = null) {

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
	
	$addresses = $db1->fetchAll("SELECT * from bulk_po_import where `PO Number` = '" . $poNumber . "' LIMIT 1");
	
	if($customer_id == BBB_ID) {
		foreach ($addresses as $address) {
			$regionModel = Mage::getModel('directory/region')->loadByCode($address['Ship To State'], 'US');
			$regionId = $regionModel->getId();
			$addressData = array(
				'firstname' => 'Receiving',
				'lastname' => 'Department #' . $storeId,
				'street' => $address['Ship To Address 1'],
				'city' => $address['Ship To City'],
				'postcode' => $address['Ship To ZipCode'],
				'telephone' => '(000) 000-0000',
				'country_id' => 'US',
				'region_id' => $regionId,
				'company' => 'buybuyBaby #' . $storeId
			);
			//$shipMethod = $address['SCAC Code'];
		}
	} elseif($customer_id == TARGET_ID || $customer_id == TARGET_COM_ID || $customer_id == TOYSRUS) {
		foreach ($addresses as $address) {
			$regionModel = Mage::getModel('directory/region')->loadByCode($address['Ship To State'], 'US');
			$regionId = $regionModel->getId();
			$addressData = array(
				'firstname' => 'Receiving',
				'lastname' => 'Department #' . $storeId,
				'street' => $address['Ship To Address 1'],
				'city' => $address['Ship To City'],
				'postcode' => $address['Ship To ZipCode'],
				'telephone' => '(000) 000-0000',
				'country_id' => 'US',
				'region_id' => $regionId,
				'company' => $address['Ship To Name']
			);
			//$shipMethod = $address['SCAC Code'];
			//$covalent_path = $address['Covalent Path File Reference'];
		}
	}

	$items = $db1->query("SELECT `UPC/EAN Code`, `Ordered Quantity`, `Covalent Path File Reference` from bulk_po_import where `PO Number` = '" . $poNumber . "'");
	
	foreach($items as $item) {

		// add product(s)
		$covalent_path = $item['Covalent Path File Reference'];
		$orderItem = Mage::getModel('catalog/product')->loadByAttribute('upc', $item['UPC/EAN Code']);
		$product = Mage::getModel('catalog/product')->load($orderItem->getId());
		$buyInfo = array(
			'qty' => $item['Ordered Quantity'],
			'file' => $item['Covalent Path File Reference'],
		);
		$quote->addProduct($product, new Varien_Object($buyInfo));
		$total_qty = $total_qty + $item['Ordered Quantity'];
	}
	
	$billingAddress = $quote->getBillingAddress()->addData($addressData);
	$shippingAddress = $quote->getShippingAddress()->addData($addressData);
	
	if($customer_id == TARGET_ID || $customer_id == TARGET_COM_ID) {
		if($total_qty > 85) {
			Mage::register('adminship_data', new Varien_Object(array(
	        'shipping_amount'  => '0',
	        'shipping_description' => 'NLRT',
		)));
		} else {
			Mage::register('adminship_data', new Varien_Object(array(
	        'shipping_amount'  => '0',
	        'shipping_description' => 'FDEG',
		)));
		}
		
	} elseif($customer_id == BBB_ID) {
		if($total_qty > 120) {
			Mage::register('adminship_data', new Varien_Object(array(
	        'shipping_amount'  => '0',
	        'shipping_description' => 'UPGF',
		)));
		} else {
			Mage::register('adminship_data', new Varien_Object(array(
	        'shipping_amount'  => '0',
	        'shipping_description' => 'FDEG',
		)));
		}
	} elseif($customer_id == TOYSRUS) {
		if($total_qty > 80) {
			Mage::register('adminship_data', new Varien_Object(array(
	        'shipping_amount'  => '0',
	        'shipping_description' => 'UPGF',
		)));
		} else {
			Mage::register('adminship_data', new Varien_Object(array(
	        'shipping_amount'  => '0',
	        'shipping_description' => 'UPSN',
		)));
		}
	} else {
		Mage::register('adminship_data', new Varien_Object(array(
	        'shipping_amount'  => '0',
	        'shipping_description' => 'CHECK',
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
	$order->setGomageDeliverydateFormated($ship_date)->save();
	
	$orderInvoice = Mage::getModel('sales/order')->loadByIncrementId($order_id);
	
/*
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
*/
	
	if($hold == true) {
		$orderInvoice->hold()->save();
	}
	
	/*
if($customer_id == BBB_ID) {
		createAsn($order_id);	
	}
*/
	
	createAsn($order_id);
	if($customer_id != BBB_ID) {
		$update_order_id = "UPDATE bulk_po_import set `Order Number` = " . $order_id . " WHERE `PO Number` = '" . $poNumber . "'";
		$db1->query($update_order_id);
		createSummary();
	}
	
	echo 'Created order ' . $order_id .  ' for PO ' . $poNumber . '.<br>';
	unset($addressData);
	unset($hold);
	Mage::unregister('adminship_data');

	return $order_error;

}

function createAsn($orderId) {
    $db2 = Mage::getSingleton('core/resource')->getConnection('core_read');

	$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
	
	if($order->getStatus() == 'processing' || $order->getStatus() == 'holded' || $order->getStatus() == 'pending') {
		$orderItems = $order->getItemsCollection();
		
		$asn_id = $order->getRealOrderId() . date('mdy');
		
		if($orderItems) {
	        $itemInc = $order->getItemsCollection()->count();
        	
        	foreach ($orderItems as $item) {
				if(strpos($item->getName(),'Blooming Bath') !== false ) {
	            	$pack_quantity = 4;
	            	$pack_weight = 8.4;
            	} else {
	            	$pack_quantity = 12;
	            	$pack_weight = 2.3;
            	}
            	
            	$item_lading_qty = (int)$item->getQtyOrdered() / $pack_quantity;
            	
            	for($i = $item_lading_qty; $i > 0; $i--) {
					$get_ucc = 'SELECT ucc FROM bulk_uccs WHERE order_num is null order by id asc LIMIT 1';
					$carton_id = $db2->fetchOne($get_ucc);
					$use_ucc = 'UPDATE bulk_uccs set order_num = \'' . $order->getRealOrderId() . '\', weight = ' . $pack_weight . ', asn_id = \'' . $asn_id . '\' WHERE ucc = \'' . $carton_id . '\'';
					$db2->query($use_ucc);
				}
			}
		}
	}
}

function createSummary() {
	$db = Mage::getSingleton('core/resource')->getConnection('core_read');
	$header = array(
		'Order Number',
		'PO Number',
		'PO Date',
		'Requested Ship Date',
		'Ship Not Before Date',
		'Ship To Code',
		'Ship To Address',
		'Ship To City',
		'Ship To State',
		'Ship To ZipCode',
		'Ordered Quantity',
		'Unit of Measure',
		'Vendor Item',
		'Buyer Item',
		'Buyer Item Number',
		'Color Description'
	);
	
	$fp = fopen('summary.csv','w');
	fputcsv($fp,$header, ',', '"');
	
	$stmt = $db->query('SELECT `Order Number`, `PO Number`,`PO Date`,`Requested Ship Date`,`Ship Not Before Date`,`Ship To Code`,`Ship To Address 1`,`Ship To City`,`Ship To State`,`Ship To ZipCode`,`Ordered Quantity`,`Unit of Measurement`,`Vendor Item Number`,`Buyer Item Qualifier`,`Buyer Item Number`,`Color Description` FROM bulk_po_import');
	$rows = $stmt->fetchAll();
	//print_r($rows);
	
	$qty = 0;
	$cartons = 0;
	$pallets = 0;
	
	foreach($rows as $row) {
	    $qty = $qty + $row['Ordered Quantity'];
	    $string = array_values($row);
	    fputcsv($fp,$string, ',', '"');
	}
	
	$cartons = $qty/4;
	$pallets = ceil($cartons/24);
	fputcsv($fp, array(''), ',', '"');
	fputcsv($fp, array('','','','','','','',$qty,'',$cartons,$pallets), ',', '"');
	fputcsv($fp, array('','','','','','','','','','Cartons','Pallets'), ',', '"');
	
	fclose($fp);

}

function sendSummary() {
	$config = array('ssl' => 'tls', 'port' => 587, 'auth' => 'login', 'username' => 'support@bloomingbath.com', 'password' => '@BB11111');
	$smtpConnection = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $config);
	
	$mail = new Zend_Mail();
	$mail->setFrom("support@bloomingbath.com","Blooming Bath");
	$mail->addTo("support@bloomingbath.com","support@bloomingbath.com");
	$mail->setSubject("Order Summary " . date("Y-m-d H:i:s"));
	$mail->setBodyHtml(""); // here u also use setBodyText options.
	
	// this is for to set the file format
	$at = new Zend_Mime_Part(file_get_contents('summary.csv'));
	
	$at->type        = 'application/csv'; // if u have PDF then it would like -> 'application/pdf'
	$at->disposition = Zend_Mime::DISPOSITION_INLINE;
	$at->encoding    = Zend_Mime::ENCODING_8BIT;
	$at->filename    = 'summary.csv';
	$mail->addAttachment($at);
	$mail->send($smtpConnection);    
}

function csv_file_to_mysql_table($source_file, $target_table, $max_line_length=10000) {
  $db = Mage::getSingleton('core/resource')->getConnection('core_read');
  $db->query("TRUNCATE TABLE $target_table");
  $replace_chars = array(".","(",")");
  if (($handle = fopen("$source_file", "r")) !== FALSE) {
      $columns = fgetcsv($handle, $max_line_length, ",");
      foreach ($columns as &$column) {
          $column = "`" . trim(str_replace($replace_chars,"",$column)) . "`";
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
    	<option value="TARGET_COM">Target.com</option>
    	<option value="TOYSRUS">Babies R Us</option>
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

