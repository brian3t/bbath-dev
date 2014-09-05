<?php

class Mage_Sales_Model_Order_Process {

    public function updateOrder($orderId) {
    		
		$order = Mage::getModel('sales/order')->load($orderId);
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
		
		$db = Mage::getSingleton('core/resource')->getConnection('core_read');
		$result = $db->query('SELECT tracking_number FROM tracking_numbers WHERE order_id = "' . $orderNumber . '" LIMIT 1');
		$rows = $result->fetch(PDO::FETCH_OBJ);
		
		if($rows) {
			$tracking_number = $rows->tracking_number;
			$carrier = 'fedex';
			$carrierTitle = 'Federal Express';
		} else {
			$tracking_number = 'No Tracking Number';
			$carrier = 'usps';
			$carrierTitle = 'United States Postal Service';
		}
		
		$data = array();
		$data['carrier_code'] = $carrier;
		$data['title'] = $carrierTitle;
		$data['number'] = $tracking_number;
		
		$track = Mage::getModel('sales/order_shipment_track')->addData($data);
		$shipment->addTrack($track);
		
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
    
    public function awaitingPayment($orderId)
    {
    	$order = Mage::getModel('sales/order')->load($orderId);
    	$orderStatus = $order->getStatus();
    	
    	if( $orderStatus == 'complete') {
    		$order->setStatus('payment_expected');
    		$order->addStatusToHistory('payment_expected', '', false);
    		$order->save();
    	}
    }
    
    public function markComplete($orderId)
    {
    	$order = Mage::getModel('sales/order')->load($orderId);
    	$orderStatus = $order->getStatus();
    	
    		$order->setStatus('complete');
    		$order->addStatusToHistory('complete', '', false);
    		$order->save();
    }
    
    public function deleteOrder($orderId)
    { 
		
        $flag = false;
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');	
		$query="show tables";
		$rsc_table=$write->fetchCol($query);	
		
		$table_sales_flat_order = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');						
		$table_sales_flat_creditmemo_comment= Mage::getSingleton('core/resource')->getTableName('sales_flat_creditmemo_comment');
		$table_sales_flat_creditmemo_item= Mage::getSingleton('core/resource')->getTableName('sales_flat_creditmemo_item');
		$table_sales_flat_creditmemo= Mage::getSingleton('core/resource')->getTableName('sales_flat_creditmemo');
		$table_sales_flat_creditmemo_grid= Mage::getSingleton('core/resource')->getTableName('sales_flat_creditmemo_grid');
		$table_sales_flat_invoice_comment= Mage::getSingleton('core/resource')->getTableName('sales_flat_invoice_comment');
		$table_sales_flat_invoice_item= Mage::getSingleton('core/resource')->getTableName('sales_flat_invoice_item');
		$table_sales_flat_invoice= Mage::getSingleton('core/resource')->getTableName('sales_flat_invoice');
		$table_sales_flat_invoice_grid= Mage::getSingleton('core/resource')->getTableName('sales_flat_invoice_grid');
		$table_sales_flat_quote_address_item= Mage::getSingleton('core/resource')->getTableName('sales_flat_quote_address_item');
		$table_sales_flat_quote_item_option= Mage::getSingleton('core/resource')->getTableName('sales_flat_quote_item_option');
		$table_sales_flat_quote= Mage::getSingleton('core/resource')->getTableName('sales_flat_quote');
		$table_sales_flat_quote_address= Mage::getSingleton('core/resource')->getTableName('sales_flat_quote_address');
		$table_sales_flat_quote_item= Mage::getSingleton('core/resource')->getTableName('sales_flat_quote_item');
		$table_sales_flat_quote_payment= Mage::getSingleton('core/resource')->getTableName('sales_flat_quote_payment');
		$table_sales_flat_shipment_comment= Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_comment');
		$table_sales_flat_shipment_item= Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_item');
		$table_sales_flat_shipment_track= Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_track');
		$table_sales_flat_shipment= Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
		$table_sales_flat_shipment_grid= Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_grid');		
		$table_sales_flat_order_address= Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address');
		$table_sales_flat_order_item= Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
		$table_sales_flat_order_payment= Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
		$table_sales_flat_order_status_history= Mage::getSingleton('core/resource')->getTableName('sales_flat_order_status_history');					
		$table_sales_flat_order_grid= Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid');						
		$table_log_quote= Mage::getSingleton('core/resource')->getTableName('log_quote');				
        $quoteId='';

		$query=null;
		$order = Mage::getModel('sales/order')->load($orderId);					
		if($order->increment_id){
			/*$query="show tables like 'sales_flat_order'";
			$rs=$write->fetchAll($query);*/						
			$incId=$order->increment_id;
			if(in_array($table_sales_flat_order,$rsc_table)){
				$query='SELECT entity_id   FROM  '.$table_sales_flat_order.'    WHERE increment_id="'.mysql_escape_string($incId).'"';
				
				$rs=$write->fetchAll($query);												
			
				$query='SELECT quote_id    FROM   '.$table_sales_flat_order.'        WHERE entity_id="'.mysql_escape_string($orderId).'"';
				$rs1=$write->fetchAll($query);
				$quoteId=$rs1[0]['quote_id'];							
			}		
			
			$query='SET FOREIGN_KEY_CHECKS=1';
			$rs3=$write->query($query);
			//print_r($rsc_table);
			//echo $table_sales_flat_creditmemo_comment;
			if(in_array($table_sales_flat_creditmemo_comment,$rsc_table)){
			//echo "DELETE FROM ".$table_sales_flat_creditmemo_comment."    WHERE parent_id IN (SELECT entity_id FROM ".$table_sales_flat_creditmemo." WHERE order_id=".$orderId.")";
			//die;
			$write->query("DELETE FROM ".$table_sales_flat_creditmemo_comment."    WHERE parent_id IN (SELECT entity_id FROM ".$table_sales_flat_creditmemo." WHERE order_id='".mysql_escape_string($orderId)."')");
			}
			//die;
			
			
			if(in_array('sales_flat_creditmemo_item',$rsc_table)){
			$write->query("DELETE FROM ".$table_sales_flat_creditmemo_item."       WHERE parent_id IN (SELECT entity_id FROM ".$table_sales_flat_creditmemo." WHERE order_id='".mysql_escape_string($orderId)."')");
			}
			
			
			if(in_array($table_sales_flat_creditmemo,$rsc_table)){
			$write->query("DELETE FROM ".$table_sales_flat_creditmemo."            WHERE order_id='".mysql_escape_string($orderId)."'");
			}
			
			
			
			if(in_array($table_sales_flat_creditmemo_grid,$rsc_table)){
			$write->query("DELETE FROM ".$table_sales_flat_creditmemo_grid."        WHERE order_id='".mysql_escape_string($orderId)."'");
			}
			
			
			if(in_array($table_sales_flat_invoice_comment,$rsc_table)){
			
			$write->query("DELETE FROM ".$table_sales_flat_invoice_comment." WHERE parent_id IN (SELECT entity_id FROM ".$table_sales_flat_invoice." WHERE order_id='".mysql_escape_string($orderId)."')");
			}
			
			if(in_array($table_sales_flat_invoice_item,$rsc_table)){
			
			$write->query("DELETE FROM ".$table_sales_flat_invoice_item."     WHERE parent_id IN (SELECT entity_id FROM ".$table_sales_flat_invoice." WHERE order_id='".mysql_escape_string($orderId)."')");
			}
			
			
			if(in_array($table_sales_flat_invoice,$rsc_table)){
			
			$write->query("DELETE FROM ".$table_sales_flat_invoice."         WHERE order_id='".mysql_escape_string($orderId)."'");
			}
			
			if(in_array($table_sales_flat_invoice_grid,$rsc_table)){
			
			$write->query("DELETE FROM ".$table_sales_flat_invoice_grid."     WHERE order_id='".mysql_escape_string($orderId)."'");
			}	
			
			if($quoteId){						
				if(in_array($table_sales_flat_quote_address_item,$rsc_table)){							
				$write->query("DELETE FROM ".$table_sales_flat_quote_address_item."     WHERE parent_item_id  IN (SELECT address_id FROM ".$table_sales_flat_quote_address." WHERE quote_id=".$quoteId.")");
				}
				
				$table_sales_flat_quote_shipping_rate= Mage::getSingleton('core/resource')->getTableName('sales_flat_quote_shipping_rate');
				if(in_array($table_sales_flat_quote_shipping_rate,$rsc_table)){
				$write->query("DELETE FROM ".$table_sales_flat_quote_shipping_rate."    WHERE address_id IN (SELECT address_id FROM ".$table_sales_flat_quote_address." WHERE quote_id=".$quoteId.")");
				}
				
				if(in_array($table_sales_flat_quote_item_option,$rsc_table)){
				$write->query("DELETE FROM ".$table_sales_flat_quote_item_option."     WHERE item_id IN (SELECT item_id FROM ".$table_sales_flat_quote_item." WHERE quote_id=".$quoteId.")");
				}
			
				
				if(in_array($table_sales_flat_quote,$rsc_table)){
				$write->query("DELETE FROM ".$table_sales_flat_quote."                 WHERE entity_id=".$quoteId);
				}
				
				if(in_array($table_sales_flat_quote_address,$rsc_table)){
				$write->query("DELETE FROM ".$table_sales_flat_quote_address."         WHERE quote_id=".$quoteId);
				}
				
				if(in_array($table_sales_flat_quote_item,$rsc_table)){
				$write->query("DELETE FROM ".$table_sales_flat_quote_item."             WHERE quote_id=".$quoteId);
				}
				
				if(in_array('sales_flat_quote_payment',$rsc_table)){
				$write->query("DELETE FROM ".$table_sales_flat_quote_payment."         WHERE quote_id=".$quoteId);
				}
				
			}
			
			
			if(in_array($table_sales_flat_shipment_comment,$rsc_table)){
			$write->query("DELETE FROM ".$table_sales_flat_shipment_comment."    WHERE parent_id IN (SELECT entity_id FROM ".$table_sales_flat_shipment." WHERE order_id='".mysql_escape_string($orderId)."')");
			}
			
			if(in_array($table_sales_flat_shipment_item,$rsc_table)){
			$write->query("DELETE FROM ".$table_sales_flat_shipment_item."         WHERE parent_id IN (SELECT entity_id FROM ".$table_sales_flat_shipment." WHERE order_id='".mysql_escape_string($orderId)."')");
			}
			
			
			if(in_array($table_sales_flat_shipment_track,$rsc_table)){						
			$write->query("DELETE FROM ".$table_sales_flat_shipment_track."         WHERE order_id  IN (SELECT entity_id FROM ".$table_sales_flat_shipment." WHERE order_id='".mysql_escape_string($orderId)."')");
			}
			
			
			if(in_array($table_sales_flat_shipment,$rsc_table)){
			
			$write->query("DELETE FROM ".$table_sales_flat_shipment."             WHERE order_id='".mysql_escape_string($orderId)."'");
			}
			
			
			if(in_array($table_sales_flat_shipment_grid,$rsc_table)){
			$write->query("DELETE FROM ".$table_sales_flat_shipment_grid."         WHERE order_id='".mysql_escape_string($orderId)."'");
			}
			
			if(in_array($table_sales_flat_order,$rsc_table)){
			$write->query("DELETE FROM ".$table_sales_flat_order."                     WHERE entity_id='".mysql_escape_string($orderId)."'");
			}
			
			if(in_array($table_sales_flat_order_address,$rsc_table)){
			$write->query("DELETE FROM ".$table_sales_flat_order_address."            WHERE parent_id='".mysql_escape_string($orderId)."'");
			}
			
			if(in_array($table_sales_flat_order_item,$rsc_table)){						
			$write->query("DELETE FROM ".$table_sales_flat_order_item."                 WHERE order_id='".mysql_escape_string($orderId)."'");
			}
			if(in_array($table_sales_flat_order_payment,$rsc_table)){
			$write->query("DELETE FROM ".$table_sales_flat_order_payment."             WHERE parent_id='".mysql_escape_string($orderId)."'");
			}
			if(in_array($table_sales_flat_order_status_history,$rsc_table)){
			$write->query("DELETE FROM ".$table_sales_flat_order_status_history."     WHERE parent_id='".mysql_escape_string($orderId)."'");
			}
			if($incId&&in_array($table_sales_flat_order_grid,$rsc_table)){						
				$write->query("DELETE FROM ".$table_sales_flat_order_grid."                 WHERE increment_id='".mysql_escape_string($incId)."'");

			}
			
			$query="show tables like '%".$table_log_quote."'";
			$rsc_table_l=$write->fetchCol($query);	
			if($quoteId&&$rsc_table_l){						
					$write->query("DELETE FROM ".$table_log_quote." WHERE quote_id=".$quoteId);							
			}
			$write->query("SET FOREIGN_KEY_CHECKS=1");						
		}						
	}
	
	public function ediInvoice($orderId)
    {
    	define('SAVE_LOCATION', Mage::getBaseDir('base') . '/misc/export/edi/');
    	$admin = Mage::getSingleton('admin/session')->getUser()->getFirstname();

    	$order = Mage::getModel('sales/order')->load($orderId);
    	$customer_id = $order->getCustomerId();
    	$customer_name = $this->getCustomerName($customer_id);
    	
		if($order->getStatus() == 'asn_sent') {
			$fileName = $customer_name . '_invoice_export_'.date("Ymd_His").$order->getRealOrderId() . '.csv';
			
			$orderItems = $order->getItemsCollection();
			
			if($orderItems) {
		        $itemInc = $order->getItemsCollection()->count();
		        $qtyOrdered = 0;
		        $total_qty_ordered = 0;
	        	$num_items = 0;
	        	$subtotal = 0;
	        	
	        	$common = $this->getCommonOrderValues($order, $customer_name);
	        	$fp = fopen(SAVE_LOCATION . $fileName, 'w');
	        	fputcsv($fp, $this->getInvoiceHeadRowValues(), ',', '"');
	        	
		        foreach ($orderItems as $item)
		        {			
		        
				        	$product_options = $item->getData('product_options');
			            	$product_options = unserialize($product_options);
			            	$covalent_file_path = ($product_options['info_buyRequest']['file']);
			            	
			            	$num_items = $num_items + 1;
			            	$item_details = array(
			            		number_format($item->getRowTotal(),2,'.', ''),
					            '',
								(int)$item->getQtyShipped(),
								'EA',
					            '',
					            '',
					            $itemInc,
					            $num_items,
								(int)$item->getQtyInvoiced(),
					            '',
					            number_format($item->getData('price'),2,'.', ''),
					            '',
								'UP',
								Mage::getModel('catalog/product')->loadByAttribute('sku',$item->getSku())->getUpc(),
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            $covalent_file_path,
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
					            '',
				        	);
			            	$record = array_merge($common, $item_details);
			            	fputcsv($fp,$record, ',', '"');
		        }

		        fclose($fp);
	            $file_contents = file_get_contents(SAVE_LOCATION . $fileName);
				$file_contents = str_replace('~', '\\' ,$file_contents);
	            $file_contents = $this->normalize($file_contents);
	            file_put_contents(SAVE_LOCATION . $fileName, $file_contents);
	            $this->ftp_put_edi_files($fileName, SAVE_LOCATION, 'Outbox/810_Test/');
			}
			
			
	        $order->setStatus('payment_expected');
			$order->addStatusToHistory('payment_expected', 'Order Invoiced by ' . $admin, false);
			$order->save();
	    }
    }
    
    public function getCustomerName($customer_id) {
    	if($customer_id == 845) {
	    	return 'TARGET';
    	} elseif($customer_id == 1115) {
	    	return 'BBB';
    	} elseif($customer_id == 1849) {
	    	return 'ZZTGTWEB';
    	} elseif($customer_id == 850) {
    		return 'ToyRUs';
    	} else {
	    	return '';
    	}
    }
    
    public function ediAsn($orderId, $asn_date)
    {
    	define('SAVE_LOCATION', Mage::getBaseDir('base') . '/misc/export/edi/');
    	$db2 = Mage::getSingleton('core/resource')->getConnection('core_read');
    	$admin = Mage::getSingleton('admin/session')->getUser()->getFirstname();

    	$order = Mage::getModel('sales/order')->load($orderId);
    	$customer_id = $order->getCustomerId();
    	$customer_name = $this->getCustomerName($customer_id);
    	
		if($order->getStatus() == 'complete') {
			$fileName = $customer_name . '_asn_export_'.date("Ymd_His").$order->getRealOrderId() . '.csv';
			
			$orderItems = $order->getItemsCollection();
			
			if($orderItems) {
		        $itemInc = $order->getItemsCollection()->count();
		        $qtyOrdered = 0;
		        $total_qty_ordered = 0;
	        	$num_items = 0;
	        	$subtotal = 0;
	        	
	        	foreach ($orderItems as $item) {
					if(strpos($item->getName(),'Blooming Bath') !== false ) {
		            	$pack_quantity = 4;
		            	$pack_weight = 7.5;
	            	} else {
		            	$pack_quantity = 12;
		            	$pack_weight = 3;
	            	}
	            	
	            	$total_order_lading_qty = $total_order_lading_qty + ((int)$item->getQtyShipped() / $pack_quantity);
	            	$total_order_weight = $total_order_weight + (((int)$item->getQtyShipped() / $pack_quantity) * $pack_weight);
				}
	        	
	        	$common = $this->getCommonAsnValues($order, $customer_name, $total_order_lading_qty, $total_order_weight, $asn_date);
	        	$ship_method = $common[1];
	        	$common = $common[0];
	        	
	        	$fp = fopen(SAVE_LOCATION . $fileName, 'w');
	        	fputcsv($fp, $this->getAsnHeadRowValues(), ',', '"');
	        	
	        	if($ship_method != 'FDEG' && $ship_method != 'UPSN') {
		        	$get_tare_ucc = 'SELECT ucc FROM bulk_uccs WHERE order_num is null order by id asc LIMIT 1';
					$tare_id = $db2->fetchOne($get_tare_ucc);
					$use_ucc = 'UPDATE bulk_uccs set order_num = \'' . $order->getRealOrderId() . '\' WHERE ucc = \'' . $tare_id . '\'';
					$db2->query($use_ucc);
					$tare_id_qualifier = 'GM';
	        	} else {
		        	$tare_id = '';
		        	$tare_id_qualifier = '';
	        	}
	        	
		        foreach ($orderItems as $item)
		        {
	            	$num_items = $num_items + 1;
	            	if(strpos($item->getName(),'Blooming Bath') !== false ) {
		            	$pack_quantity = 4;
	            	} else {
		            	$pack_quantity = 12;
	            	}
	            	
	            	$product_options = $item->getData('product_options');
	            	$product_options = unserialize($product_options);
	            	$covalent_file_path = ($product_options['info_buyRequest']['file']);
	            	
	            	$item_lading_qty = (int)$item->getQtyShipped() / $pack_quantity;
	            	
	            	for($i = $item_lading_qty; $i > 0; $i--)
					{
						$get_ucc = 'SELECT ucc FROM bulk_uccs WHERE order_num is null order by id asc LIMIT 1';
						$carton_id = $db2->fetchOne($get_ucc);
						$use_ucc = 'UPDATE bulk_uccs set order_num = \'' . $order->getRealOrderId() . '\' WHERE ucc = \'' . $carton_id . '\'';
						$db2->query($use_ucc);			            	
		            	
		            	$item_details = array(
		            		$total_order_lading_qty,
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							$carton_id,
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							$tare_id_qualifier,
							$tare_id,
							'',
							'',
							'',
							'',
							'',
							'',
							'UP',
							Mage::getModel('catalog/product')->loadByAttribute('sku',$item->getSku())->getUpc(),
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							$pack_quantity,
							'EA',
							(int)$item->getQtyOrdered(),
							'',
							'',
							$pack_quantity,
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							'',
							$covalent_file_path,
							'',
							'',
							'',
							'',
							'',
			        	);
		            	$record = array_merge($common, $item_details);
		            	fputcsv($fp,$record, ',', '"');
	            	}
		        }

		        fclose($fp);
	            $file_contents = file_get_contents(SAVE_LOCATION . $fileName);
				$file_contents = str_replace('~', '\\' ,$file_contents);
	            $file_contents = $this->normalize($file_contents);
	            file_put_contents(SAVE_LOCATION . $fileName, $file_contents);
	            //$this->ftp_put_edi_files($fileName, SAVE_LOCATION, 'Outbox/856_Test/');
			}
			
	        $order->setStatus('asn_sent');
			$order->addStatusToHistory('asn_sent', 'Order ASN sent by ' . $admin, false);
			$order->save();
	    }
    }
    
    function ftp_put_edi_files($fileName, $local_location, $remote_location) {
    
		$ftp_server = 'covtxhou02-71.covalentworks.com';
		$ftp_user_name = 'Node1185';
		$ftp_user_pass = 'Fu3230';
	
	    $connection = ftp_connect($ftp_server);
		$login = ftp_login($connection, $ftp_user_name, $ftp_user_pass);
		ftp_pasv($connection, true);
		ftp_chdir($connection, $remote_location);
		
		if (!$connection || !$login) {
			$mail = Mage::getModel('core/email');
			$mail->setToName('Blooming Bath');
			$mail->setToEmail('support@bloomingbath.com');
			$mail->setBody('Something is wrong at Covalentworks');
			$mail->setSubject('FTP Failed To Login');
			$mail->setFromEmail('support@bloomingbath.com');
			$mail->setFromName('Blooming Bath');
			$mail->setType('text');// YOu can use Html or text as Mail format
			$mail->send();
		} else {
			$upload = ftp_put($connection, $fileName, $local_location . $fileName, FTP_BINARY);
		}
		
		if (!$upload) {
			$mail = Mage::getModel('core/email');
			$mail->setToName('Blooming Bath');
			$mail->setToEmail('support@bloomingbath.com');
			$mail->setBody('Something is wrong at Covalentworks');
			$mail->setSubject('FTP Failed To Upload Files');
			$mail->setFromEmail('support@bloomingbath.com');
			$mail->setFromName('Blooming Bath');
			$mail->setType('text');// YOu can use Html or text as Mail format
			$mail->send();
		}
		
		ftp_close($connection);
    }

	function getPos($remote_location = 'Inbox/850_Test') {
    	
		$ftp_server = 'covtxhou02-71.covalentworks.com';
		$ftp_user_name = 'Node1185';
		$ftp_user_pass = 'Fu3230';
	
	    $connection = ftp_connect($ftp_server);
		$login = ftp_login($connection, $ftp_user_name, $ftp_user_pass);
		ftp_pasv($connection, true);
		ftp_chdir($connection, $remote_location);
		
		$contents = ftp_nlist($connection, ".");
		
		foreach ($contents as $file) {
		   ftp_get($connection, Mage::getBaseDir('base') . '/misc/import/' . $file, $file, FTP_BINARY);
		}
		
		ftp_close($connection);
    }
    
    function normalize($s) {
	    // Normalize line endings
	    // Convert all line-endings to UNIX format
	    
	    $s = str_replace("\r", "\r\n", $s);
	    $s = str_replace("\n", "\r\n", $s);
	    // Don't allow out-of-control blank lines
	    $s = preg_replace("/\n{2,}/", "\n\n", $s);
	
	    return $s;
	}
	
	public function getAsnHeadRowValues()
    {
        return array(
			'Receiver ID',
			'Receiver Name',
			'Transaction Set Purpose Code',
			'ASN Number',
			'ASN Date',
			'ASN Time',
			'ASN Structure Code',
			'Shipped Date',
			'Shipped Time',
			'Estimated Delivery Date',
			'Estimated Delivery Time',
			'Shipment Packaging Code ',
			'Shipment Quantity',
			'Shipment Weight Qualifier',
			'Shipment Weight',
			'Shipment Weight UOM',
			'Shipment Volume',
			'Shipment Volume UOM',
			'Contact Name',
			'Contact Phone',
			'Contact Fax',
			'Contact Email',
			'Shipment Method of Payment',
			'F.O.B. Point Code',
			'F.O.B. Point',
			'Routing Sequence Code',
			'SCAC Code',
			'Transportation Method',
			'Routing/Carrier Name',
			'Shipment Status Code',
			'Service Level Code',
			'Master Bill of Lading',
			'Bill of Lading',
			'PRO/Tracking/Pickup #',
			'Invoice Number',
			'Shipment Packing List Number',
			'Manifest Number',
			'Route Number',
			'Mutually Defined',
			'Ship To Name',
			'Ship To Code Type',
			'Ship To Code',
			'Ship To Additional Name 1',
			'Ship To Address 1',
			'Ship To Address 2',
			'Ship To City',
			'Ship To State',
			'Ship To ZipCode',
			'Ship To Country',
			'Bill To Name',
			'Bill To Code Type',
			'Bill To Code',
			'Bill To Additional Name 1',
			'Bill To Address 1',
			'Bill To Address 2',
			'Bill To City',
			'Bill To State',
			'Bill To ZipCode',
			'Bill To Country',
			'Ship From Name',
			'Ship From Code Type',
			'Ship From Code',
			'Ship From Additional Name 1',
			'Ship From Address 1',
			'Ship From Address 2',
			'Ship From City',
			'Ship From State',
			'Ship From ZipCode',
			'Ship From Country',
			'Reserved Shipment Field 1',
			'Reserved Shipment Field 2',
			'Reserved Shipment Field 3',
			'Reserved Shipment Field 4',
			'Reserved Shipment Field 5',
			'Reserved Shipment Field 6',
			'Reserved Shipment Field 7',
			'Reserved Shipment Field 8',
			'Reserved Shipment Field 9',
			'Reserved Shipment Field 10',
			'PO Number',
			'PO Date',
			'Release Number',
			'Contract Number',
			'PO Description Code',
			'PO Description',
			'Order Packaging Code ',
			'Order Lading Quantity',
			'Order Weight Qualifier',
			'Order Weight',
			'Order Weight UOM',
			'Order Volume',
			'Order Volume UOM',
			'Order Status Code',
			'Internal Vendor Number',
			'Department Number',
			'Invoice Number',
			'Vendor Order Number',
			'Buyer Buyer Name',
			'Buyer Buyer Code Type',
			'Buyer Buyer Code',
			'Store Name',
			'Store Code Type',
			'Store Code',
			'Store Address 1',
			'Store Address 2',
			'Store City',
			'Store State',
			'Store ZipCode',
			'Store Country',
			'Reserved Order Field 1',
			'Reserved Order Field 2',
			'Reserved Order Field 3',
			'Reserved Order Field 4',
			'Reserved Order Field 5',
			'Carton ID Qualifier',
			'Carton ID',
			'Tracking Number',
			'Carton Weight Qualifier',
			'Carton Weight',
			'Carton Weight UOM',
			'Carton Volume',
			'Carton Volume UOM',
			'Carton Length',
			'Carton Width',
			'Carton Height',
			'Carton Dimension UOM',
			'Tare ID Qualifier',
			'Tare ID',
			'Reserved Package Field 1',
			'Reserved Package Field 2',
			'Reserved Package Field 3',
			'Reserved Package Field 4',
			'Reserved Package Field 5',
			'PO Line Number',
			'UPC/EAN Qualifier',
			'UPC/EAN Code',
			'Vendor Item Qualifier',
			'Vendor Item Code',
			'Buyer Item Qualifier',
			'Buyer Item Code',
			'Color Description',
			'Size Description',
			'Item Description',
			'Shipped Quantity',
			'Unit of Measurement ',
			'Ordered Quantity',
			'Unit of Measurement ',
			'Item Status Code',
			'Pack',
			'Size',
			'Size Unit of Measurement',
			'Inner Pack',
			'Item Packaging Code',
			'Item Weight Qualifier',
			'Item Weight',
			'Item Weight UOM',
			'Item Volume',
			'Item Volume UOM',
			'Item Length',
			'Item Width',
			'Item Height',
			'Item Dimension UOM',
			'Manufacturer Date',
			'Expiration Date',
			'Roll Number Sequential',
			'Roll Number',
			'Lot Number',
			'Commodity Code',
			'Country of Origin',
			'Item Packing List Number',
			'Serial Number',
			'Covalent Path File Reference',
			'Reserved Item Field 1',
			'Reserved Item Field 2',
			'Reserved Item Field 3',
			'Reserved Item Field 4',
			'Reserved Item Field 5'
		);
	}
	
    public function getInvoiceHeadRowValues()
    {
        return array(
            'Receiver ID',
			'Receiver Name',
			'Invoice Date',
			'Invoice Number',
			'Purchase Order Date',
			'Purchase Order Number',
			'Release Number',
			'Invoice Type',
			'Currency',
			'Internal Vendor Number',
			'Department Number',
			'Merchandise Type Code',
			'Promotion/Deal Number',
			'Lot/Batch Number',
			'Authorization Number',
			'Job/Project Number',
			'Customer Order Number',
			'Seller\'s Invoice Number',
			'ASN Number',
			'PRO Number',
			'Bill of Lading Number',
			'Packing List Number',
			'Account Payable Vendor Number',
			'Contact Name',
			'Contact Phone',
			'Contact Fax',
			'Contact Email',
			'Shipment Method Of Payment',
			'F.O.B. Point Code',
			'F.O.B. Point',
			'Note',
			'Transportation Method',
			'SCAC Code',
			'Carrier Name',
			'Terms Type Code',
			'Terms Basis Date Code',
			'Terms Start Date',
			'Terms Discount Percent',
			'Terms Discount Due Date',
			'Terms Discount Days Due',
			'Terms Net Due Date',
			'Terms Net Days',
			'Terms Discount Amount',
			'Terms Day of Month',
			'Terms Description',
			'Delivery Requested Date',
			'Requested Ship Date',
			'Cancel After Date',
			'Effective Date',
			'Remit To Name',
			'Remit To Code Type',
			'Remit To Code',
			'Remit To Address 1',
			'Remit To Address 2',
			'Remit To City',
			'Remit To State',
			'Remit To ZipCode',
			'Remit To Country',
			'Ship To Name',
			'Ship To Code Type',
			'Ship To Code',
			'Ship To Additional Name 1',
			'Ship To Address 1',
			'Ship To Address 2',
			'Ship To City',
			'Ship To State',
			'Ship To ZipCode',
			'Ship To Country',
			'Buyer/Bill To Name',
			'Buyer/Bill To Code Type',
			'Buyer/Bill To Code',
			'Buyer/Bill To Additional Name 1',
			'Buyer/Bill To Address 1',
			'Buyer/Bill To Address 2',
			'Buyer/Bill To City',
			'Buyer/Bill To State',
			'Buyer/Bill To ZipCode',
			'Buyer/Bill To Country',
			'Supplier/Ship From Name',
			'Supplier/Ship From Code Type',
			'Supplier/Ship From Code',
			'Supplier/Ship From Additional Name 1',
			'Supplier/Ship From Address 1',
			'Supplier/Ship From Address 2',
			'Supplier/Ship From City',
			'Supplier/Ship From State',
			'Supplier/Ship From ZipCode',
			'Supplier/Ship From Country',
			'Allowance/Charge Indicator',
			'Allowance/Charge Code',
			'Allowance/Charge Amount',
			'Allowance/Charge Percent Qualifier',
			'Allowance/Charge Percent',
			'Allowance/Charge Rate',
			'Allowance/Charge UOM',
			'Allowance/Charge Quantity',
			'Allowance/Charge Handling Method',
			'Allowance/Charge Description',
			'Allowance/Charge Indicator 2',
			'Allowance/Charge Code 2',
			'Allowance/Charge Amount 2',
			'Allowance/Charge Percent Qualifier 2',
			'Allowance/Charge Percent 2',
			'Allowance/Charge Rate 2',
			'Allowance/Charge UOM 2',
			'Allowance/Charge Quantity 2',
			'Allowance/Charge Handling Method 2',
			'Allowance/Charge Description 2',
			'Allowance/Charge Indicator 3',
			'Allowance/Charge Code 3',
			'Allowance/Charge Amount 3',
			'Allowance/Charge Percent Qualifier 3',
			'Allowance/Charge Percent 3',
			'Allowance/Charge Rate 3',
			'Allowance/Charge UOM 3',
			'Allowance/Charge Quantity 3',
			'Allowance/Charge Method of Handling Code 3',
			'Allowance/Charge Description 3',
			'Allowance/Charge Indicator 4',
			'Allowance/Charge Code 4',
			'Allowance/Charge Amount 4',
			'Allowance/Charge Percent Qualifier 4',
			'Allowance/Charge Percent 4',
			'Allowance/Charge Rate 4',
			'Allowance/Charge UOM 4',
			'Allowance/Charge Quantity 4',
			'Allowance/Charge Method of Handling Code 4',
			'Allowance/Charge Description 4',
			'Tax Type',
			'Tax Amount',
			'Tax Percent',
			'Tax Type 2',
			'Tax Amount 2',
			'Tax Percent 2',
			'Tax Type 3',
			'Tax Amount 3',
			'Tax Percent 3',
			'Tax Type 4',
			'Tax Amount 4',
			'Tax Percent 4',
			'Reserved Header Field 1',
			'Reserved Header Field 2',
			'Reserved Header Field 3',
			'Reserved Header Field 4',
			'Reserved Header Field 5',
			'Reserved Header Field 6',
			'Reserved Header Field 7',
			'Reserved Header Field 8',
			'Reserved Header Field 9',
			'Reserved Header Field 10',
			'Total Invoice Amount',
			'Total Extended Line Amount',
			'Total Invoice Amount Less Terms Discount',
			'Total Number of Units Shipped',
			'Unit of Measurement',
			'Total Weight',
			'Unit of Measurement',
			'Number of Line Items',
			'Line Number',
			'Quantity Invoiced',
			'Unit of Measurement',
			'Unit Price',
			'Basis of Unit Price Code',
			'UPC/EAN Qualifier',
			'UPC/EAN Code',
			'Vendor Item Qualifier',
			'Vendor Item Code',
			'Buyer Item Qualifier',
			'Buyer Item Code',
			'Color Description',
			'Size Description',
			'Item Description',
			'Retail Price',
			'Pack',
			'Size',
			'Unit of Measurement',
			'Pallet Block and Tiers',
			'Inner Pack',
			'Store Quantity',
			'Store Number',
			'Shipped Quantity',
			'Shipped Unit of Measurement',
			'Shipment/Order Status Code',
			'Quantity Different',
			'Change Reason Code',
			'Alternate Unit Price',
			'Alternate Quantity Invoiced',
			'Alternate Unit of Measurement',
			'Item Allowance/Charge Indicator',
			'Item Allowance/Charge Code',
			'Item Allowance/Charge Amount',
			'Item Allowance/Charge Percent Qualifier',
			'Item Allowance/Charge Percent',
			'Item Allowance/Charge Rate',
			'Item Allowance/Charge UOM',
			'Item Allowance/Charge Quantity',
			'Item Allowance/Charge Handling Method',
			'Item Allowance/Charge Description',
			'Item Allowance/Charge Indicator 2',
			'Item Allowance/Charge Code 2',
			'Item Allowance/Charge Amount 2',
			'Item Allowance/Charge Percent Qualifier 2',
			'Item Allowance/Charge Percent 2',
			'Item Allowance/Charge Rate 2',
			'Item Allowance/Charge UOM 2',
			'Item Allowance/Charge Quantity 2',
			'Item Allowance/Charge Handling Method 2',
			'Item Allowance/Charge Description 2',
			'Covalent Path File Reference',
			'Reserved Detail Field 1',
			'Reserved Detail Field 2',
			'Reserved Detail Field 3',
			'Reserved Detail Field 4',
			'Reserved Detail Field 5',
			'Reserved Detail Field 6',
			'Reserved Detail Field 7',
			'Reserved Detail Field 8',
			'Reserved Detail Field 9',
			'Reserved Detail Field 10',
    	);
    }
    
    protected function getCommonOrderValues($order, $customer_name)
    {	
    	$shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($order)->load();
	    foreach ($shipmentCollection as $shipment) {
	
			foreach($shipment->getAllTracks() as $tracknum) {
	            $tracknums[] = $tracknum->getNumber();
	            $shipping_title[] = $tracknum->getTitle();
	        }
	        $shipped_date = Mage::getModel('core/date')->date('m/d/Y', $shipment->getCreatedAt());
		}
		
		$ship_method = reset($shipping_title);
		$tracking_number = reset($tracknums);
		
		if(strpos($ship_method,'NLRT') !== false) {
			$transportation_method = 'C';
			$transportation_carrier = 'National Retail Trans';
		} elseif(strpos($ship_method,'FDEG') !== false) {
			$transportation_method = 'U';
			$transportation_carrier = 'FedEx Ground';
		} elseif(strpos($ship_method,'UPGF') !== false) {
			$transportation_method = 'C';
			$transportation_carrier = 'UPS Ground Freight';
		} else {
			$transportation_method = 'M';
		}
		
		if($customer_name == 'TARGET') {
			$receiver_id = '086111470100';
		} elseif($customer_name == 'BBB') {
			$receiver_id = '129086880888';
			$shipped_date = '';
		} elseif($customer_name == 'ZZTGTWEB') {
			$receiver_id = 'ZZTGTWEB';
		} elseif($customer_name == 'ToyRUs') {
			$receiver_id = '126063711145';
			$shipped_date = '';
		}
		
		$payment = $order->getPayment();
		
        return array(
        	$receiver_id,
			'',
			Mage::getModel('core/date')->date('m/d/Y', strtotime(date())),
            $order->getRealOrderId(),
            '',
            $payment->getPoNumber(),
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
			'',
			$tracking_number,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $transportation_method,
			$ship_method,
            $transportation_carrier,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $shipped_date,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            number_format($order->getData('grand_total'),2,'.', '')
		);
    }
    
    protected function getCommonAsnValues($order, $customer_name, $total_order_lading_qty, $total_order_weight, $asn_date)
    {	
    	$shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($order)->load();
	    foreach ($shipmentCollection as $shipment) {
	
			foreach($shipment->getAllTracks() as $tracknum) {
	            $tracknums[] = $tracknum->getNumber();
	            $shipping_title[] = $tracknum->getTitle();
	        }
		}
		
		$ship_method = reset($shipping_title);
		$tracking_number = reset($tracknums);
		
		if(strpos($ship_method,'NLRT') !== false) {
			$transportation_method = 'C';
			$transportation_carrier = 'National Retail Trans';
		} elseif(strpos($ship_method,'FDEG') !== false) {
			$transportation_method = 'U';
			$transportation_carrier = 'FedEx Ground';
		} elseif(strpos($ship_method,'UPGF') !== false) {
			$transportation_method = 'C';
			$transportation_carrier = 'UPS Ground Freight';
		} elseif(strpos($ship_method,'UPSN') !== false) {
			$transportation_method = 'U';
			$transportation_carrier = 'UPS Ground';
		} else {
			$transportation_method = 'M';
			$transportation_carrier = 'Motor';
		}
		
		$asn_id = date('mdyhis');
		
		if($customer_name == 'TARGET') {
			$receiver_id = '086111470100';
			$shipped_date = '';
			$estimated_delivery_date = '';
			$shipment_packaging_code = '';
			$shipment_quantity = '';
			$shipment_weight = '';
			$shipment_weight_UOM = '';
		} elseif($customer_name == 'BBB') {
			$receiver_id = '129086880888';
			$shipped_date = $asn_date;
			$estimated_delivery_date = '';
			$shipment_packaging_code = 'CTN25';
			$shipment_quantity = $total_order_lading_qty;
			$shipment_weight = $total_order_weight;
			$shipment_weight_UOM = 'LB';
		} elseif($customer_name == 'ZZTGTWEB') {
			$receiver_id = 'ZZTGTWEB';
			$shipped_date = '';
			$estimated_delivery_date = '';
			$shipment_packaging_code = '';
			$shipment_quantity = '';
			$shipment_weight = '';
			$shipment_weight_UOM = '';
		} elseif($customer_name == 'ToyRUs') {
			$receiver_id = '126063711145';
			$shipped_date = $asn_date;
			$estimated_delivery_date = Mage::getModel('core/date')->date('m/d/Y', strtotime(date($asn_date, strtotime("+6 days"))));
			$shipment_packaging_code = 'CTN25';
			$shipment_quantity = $total_order_lading_qty;
			$shipment_weight = $total_order_weight;
			$shipment_weight_UOM = 'LB';
		}
		
		$payment = $order->getPayment();
		
		$common_values = array(
        	$receiver_id,
			'',
			'',
			$asn_id,
			$asn_date,
			//Mage::getModel('core/date')->date('m/d/Y', strtotime(date('Y-m-d H:i:s'))),
			Mage::getModel('core/date')->date('H:i:s', strtotime(date($asn_date))),
			'',
			$shipped_date,
			'',
			$estimated_delivery_date,
			'',
			$shipment_packaging_code,
			$shipment_quantity,
			'',
			$shipment_weight,
			$shipment_weight_UOM,
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			reset($shipping_title),
			$transportation_method,
			$transportation_carrier,
			'',
			'',
			$tracking_number,
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			$payment->getPoNumber(),
			'',
			'',
			'',
			'',
			'',
			'CTN25'
		);
		return array($common_values, $ship_method);
    }
    
    public function updateShipping($orderId, $customShipTitle) {
	    
		$order = Mage::getModel('sales/order')->load($orderId);
		$setShipment = $order->setShippingDescription($customShipTitle)->save();
		
    }

}