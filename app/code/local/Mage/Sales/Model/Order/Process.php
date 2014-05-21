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

}