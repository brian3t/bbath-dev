<?

  $mageFilename = '../app/Mage.php';

  require_once $mageFilename;

  Varien_Profiler::enable();

  Mage::setIsDeveloperMode(true);

  error_reporting(E_ALL ^ E_NOTICE);
  ini_set('display_errors', 1);

  umask(0);
  Mage::app('default');
  Mage::register('isSecureArea', 1);

  if(isset($_FILES['uploadedfile']['name'])) {

    $target_path = 'upload/temp.csv';

    if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
        //echo "The file ".  basename( $_FILES['uploadedfile']['name']). ' has been uploaded';
        csv_file_to_mysql_table('upload/temp.csv', 'bulk_process');
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
		$result = $db->query('SELECT s.entity_id as entity_id, b.tracking_number as tracking_number FROM bulk_process b LEFT JOIN sales_flat_order s on b.order_number = s.increment_id');
        foreach($result as $order) {
        	updateOrder($order['entity_id'], '', $order['tracking_number'], 'usps', 'Priority Mail');
		}
    } else{
        echo 'There was an error uploading the file, please try again!';
    }
  }

  if($_POST){
    if($_POST['fedex'] == 'yes') {
      $db = Mage::getSingleton('core/resource')->getConnection('core_read');
      $write = Mage::getSingleton('core/resource')->getConnection('core_write');
      
      $result = $db->query('SELECT b.order_id AS order_id, s.entity_id AS entity_id, b.service_type as service_type FROM tracking_numbers b LEFT JOIN sales_flat_order s ON b.order_id = s.increment_id WHERE b.processed IS NULL AND entity_id IS NOT null GROUP BY order_id');
      if($result){
          foreach($result as $fedex_order) {
            if($fedex_order['carrier'] == 'UPS') {
              $carrier = 'ups';
            } else {
              $carrier = 'fedex';
            }
          updateOrder($fedex_order['entity_id'], $fedex_order['order_id'], $carrier, $fedex_order['service_type']);
          
		  $write->query('update tracking_numbers set processed = 1 where order_id = "' . $fedex_order['order_id'] . '"');
          echo $fedex_order['order_id'] . ' Processed<br>';
        }
        
      }
    }
  }

    function updateOrder($entityId, $orderId, $carrier, $carrierTitle) {
		global $db;
		
/*
		if($entityId == '') {
			$order_by_po = $db->query("SELECT so.entity_id AS order_id FROM sales_flat_order so left join sales_flat_order_payment pay on pay.parent_id = so.entity_id where po_number = '" . $orderId . "'");
		    foreach ($order_by_po as $order_po) {
		    	$entityId = $order_po['order_id'];
		    }
		}
*/
		
		$order = Mage::getModel('sales/order')->load($entityId);
			    
	    if($order) {
		    $email = $order->getCustomerEmail();
		    $orderNumber = $order->getIncrementId();
		
		    $orderStatus = $order->getStatus();
		
		    //This converts the order to "Completed".
		    if( $orderStatus == 'processing' || $orderStatus == 'pending' || $orderStatus == 'payment_expected') {
		      $converter = Mage::getModel('sales/convert_order');
		      $shipment = $converter->toShipment($order);
		
		      foreach ($order->getAllItems() as $orderItem) {
		
		        if (!$orderItem->getQtyToShip()) {
		            continue;
		        }
		        if ($orderItem->getIsVirtual()) {
		            continue;
		        }
		
		        $item = $converter->itemToShipmentItem($orderItem);
		
		        $qty = $orderItem->getQtyToShip();
		
		        $item->setQty($qty);
		        $shipment->addItem($item);
		      }
		
		    } else {
		
		      foreach ($order->getShipmentsCollection() as $shipment) {
		        $shipmentId = $shipment->getIncrementId();
		      }
		
		      $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
		    }
		
		    $data = array();
		    $data['carrier_code'] = $carrier;
		    $data['title'] = $carrierTitle;
		    
		    $tracking_numbers = $db->query("SELECT tracking_number FROM tracking_numbers WHERE order_id = " . $orderId . " GROUP BY tracking_number");
		    
		    foreach($tracking_numbers as $tracking_number) {
			    //var_dump($tracking_number);
			    //$track = Mage::getModel('sales/order_shipment_api')->addTrack($shipmentId,$carrier,$carrierTitle,$tracking_number);
			    $track = Mage::getModel('sales/order_shipment_track')->addData(array('carrier_code' => $carrier, 'title' => $carrierTitle, 'number' => $tracking_number['tracking_number']));
				$shipment->addTrack($track);
				
		    }
		    		
		    if( $orderStatus == "processing" || $orderStatus == "pending" || $orderStatus == "payment_expected") {
		      $shipment->register();
		      $shipment->setEmailSent(true);
		      $shipment->getOrder()->setIsInProcess(true);
		      $transactionSave = Mage::getModel('core/resource_transaction')->addObject($shipment)->addObject($shipment->getOrder())->save();
		    }
		
		    $track->save();
		    $shipment->sendEmail($email);
		    if( $orderStatus != "payment_expected") {
		      $order->setStatus('complete');
		      $order->addStatusToHistory('complete', '', false);
		    } else {
		      $order->setStatus('payment_expected');
		      // $order->addStatusToHistory('complete', '', false);
		    }
		    $order->save();
	    }

    }

    function csv_file_to_mysql_table($source_file, $target_table, $max_line_length=10000) {
      $db = Mage::getSingleton('core/resource')->getConnection('core_read');
      $db->query("TRUNCATE TABLE $target_table");
      if (($handle = fopen("$source_file", "r")) !== FALSE) {
          $columns = fgetcsv($handle, $max_line_length, ",");
          foreach ($columns as &$column) {
              $column = str_replace(".","",$column);
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
  <title>Tracking Number Upload</title>
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
  .frame {
    overflow-y: auto;
    border: 1px solid #ccc;
    height: 3em;
    line-height: 1em;
    margin: auto;
    width: 1600px;
}

.frame::-webkit-scrollbar {
    -webkit-appearance: none;
}

.frame::-webkit-scrollbar:vertical {
    width: 11px;
}

.frame::-webkit-scrollbar:horizontal {
    height: 11px;
}

.frame::-webkit-scrollbar-thumb {
    border-radius: 8px;
    border: 2px solid white; /* should match background, can't be transparent */
    background-color: rgba(0, 0, 0, .5);
}
  </style>
</head>
<body>
  <div style="width:600px;margin: auto;">
  <img style="clear:both" src="logo.png" alt="Egghead Ventures" />
  <form enctype="multipart/form-data" action="bulk-process.php" method="POST" style="width: 400px;float: left;text-align: left;">
    <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
    Choose a file to upload: <input name="uploadedfile" type="file" /><br />
    <input type="submit" value="Upload File" />
  </form>
  <form enctype="multipart/form-data" action="bulk-process.php" method="POST" style="float:left">
    <input type="hidden" name="fedex" value="yes" />
    <input type="submit" value="Process FedEx and UPS Orders" />
  </form>
  </div>
  <div style="clear:both"><iframe style="width: 820px;border: 0;margin-top: 40px;height:375px" src="dashboard/index.php"></iframe></div>
  <div class="frame" style="height:500px;overflow:auto;">
  <?php if ($handle = opendir(Mage::getBaseDir('base') . '/misc/import/labels')) {
			$files = array();
			$i = 0;	
		    while (false !== ($entry = readdir($handle))) {
		    	if ($entry != "." && $entry != ".." ) {
		    		$files[] = Mage::getBaseDir('base') . '/misc/import/labels/' . $entry;
		    		//echo date("F d Y H:i:s.",filemtime(Mage::getBaseDir('base') . '/misc/import/labels/' . $entry)) . ' <a target="_blank" href="http://www.bloomingbath.com/misc/import/labels/' . $entry . '">' . $entry . '</a><br>';
		    		$i++;
		    	}
		    }
		    closedir($handle);
			//krsort($files);
			usort($files, function($a, $b) {
			    return filemtime($a) < filemtime($b);
			});
			//print_r($files);
			
			foreach($files as $entry) {
				echo date("F d Y",filemtime($entry)) . ' <a target="_blank" href="http://www.bloomingbath.com/misc/import/labels/' . str_replace(Mage::getBaseDir('base') . '/misc/import/labels/', '', $entry) . '">' . str_replace(Mage::getBaseDir('base') . '/misc/import/labels/', '', $entry) . '</a><br>';
			}
		}
	?>
  </div>
</body>
</html>