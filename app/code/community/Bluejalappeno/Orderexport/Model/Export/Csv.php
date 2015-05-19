<?php
/**
 * Magento Bluejalappeno Order Export Module
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
 * @category   Bluejalappeno
 * @package    Bluejalappeno_OrderExport
 * @copyright  Copyright (c) 2010 Wimbolt Ltd (http://www.bluejalappeno.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Genevieve Eddison <sales@bluejalappeno.com>
 * */
class Bluejalappeno_Orderexport_Model_Export_Csv extends Bluejalappeno_Orderexport_Model_Export_Abstractcsv
{
    const ENCLOSURE = '"';
    const DELIMITER = '|';
	private static $isNotLive;
	private static $homeEdiDir;

	static function init()
	{
		if (strpos(Mage::getBaseUrl(),"www.cobragolf.com") === false){
			self::$isNotLive = true;
			self::$homeEdiDir = '/home/tringuyen/editest';
		}
		else{
			self::$isNotLive = false;
			self::$homeEdiDir = '/home/edi';
		}
	}


	/**
     * Concrete implementation of abstract method to export given orders to csv file in var/export.
     *
     * @param $orders List of orders of type Mage_Sales_Model_Order or order ids to export.
     * @return String The name of the written csv file in var/export
     */
    public function exportOrders($orders,$items)
    {	
        //$this->writeHeadRow($fp);
        $admin = Mage::getSingleton('admin/session')->getUser()->getFirstname();
        if(is_array($orders)) {
		    foreach ($orders as $order) {
		    	$order = Mage::getModel('sales/order')->load($order);
		    	if($order->getStatus() == 'processing' || $order->getStatus() == 'pending') {
		    		$fileName = 'order_export_'.date("Ymd_His").$order->getRealOrderId();
		            $this->writeOrder($order, $fileName);
		            if($order->getStoreId() == 14) {
			            $status = 'in_process';
		            } else {
			            $status = 'sent_to_warehouse';
		            }
		            $order->setStatus($status);
		            $order->addStatusToHistory($status, 'Complete order sent to warehouse by ' . $admin, false);
		            $order->save();
		        }
		    }
		} else {
		    $order = Mage::getModel('sales/order')->load($orders);
			if($order->getStatus() == 'processing' || $order->getStatus() == 'pending') {
				$fileName = 'order_export_'.date("Ymd_His").$order->getRealOrderId();
		        $sentItems = $this->writeOrder($order, $fileName, $items);
		        if($order->getStoreId() == 14) {
			            $status = 'in_process';
		            } else {
			            $status = 'sent_to_warehouse';
		            }
		        $order->setStatus($status);
		        $order->addStatusToHistory($status, 'Item(s) ' . $sentItems . 'sent to warehouse by ' . $admin, false);
		        $order->save();
		    }
		}
    }
    
    //**EGGHEAD ADD
    public function importOrders()
    {
    	if ($handle = opendir(self::$homeEdiDir . '/import')) {
		
		    while (false !== ($entry = readdir($handle))) {
		    
		    	if ($entry != "." && $entry != ".." && strpos($entry,'856') != false) {
			    	$this->csv_file_to_mysql_table(self::$homeEdiDir . '/import/' . $entry, 'bulk_process');
				    $db = Mage::getSingleton('core/resource')->getConnection('core_read');
					$result = $db->query("SELECT b.shipmentid as shipmentid, s.entity_id as entity_id, b.trackingnumber as tracking_number, b.shipmentmethod as shipping_method FROM bulk_process b JOIN sales_flat_order s on CONVERT(SUBSTRING_INDEX(b.orderid, '-', 1), SIGNED INTEGER) = s.increment_id group by shipmentid");
				    foreach($result as $shipment) {
				    	$ship_params = preg_split('/ /', $shipment['shipping_method'], 2);
				    	$carrier = strtolower($ship_params[0]);
				    	$service = ucwords(strtolower($ship_params[1]));
						$this->updateOrder($shipment['entity_id'], $shipment['tracking_number'], $carrier, $service, $shipment['shipmentid']);
					}
		        }
		    }
		    closedir($handle);
		}
    }
    
    public function importOrdersB2b($entry)
    {
		$this->csv_file_to_mysql_table(self::$homeEdiDir . '/inventory/' . $entry, 'bulk_process_b2b');
	    $db = Mage::getSingleton('core/resource')->getConnection('core_read');
		$result = $db->query("SELECT b.CustPO AS shipmentid, s.entity_id AS entity_id, b.TrackingNo AS tracking_number, b.ShipVia AS shipping_method FROM bulk_process_b2b b JOIN sales_flat_order s ON CONVERT(SUBSTRING_INDEX(b.CustPO, '-', 1), SIGNED INTEGER) = s.increment_id  WHERE UPC != '847111111191' GROUP BY shipmentid");
	    foreach($result as $shipment) {
		    $ship_params = str_replace('COBRA ', '', $ship_params);
	    	$ship_params = preg_split('/ /', $shipment['shipping_method'], 2);
	    	$carrier = strtolower($ship_params[0]);
	    	$service = ucwords(strtolower($ship_params[1]));
			$this->updateOrderB2b($shipment['entity_id'], $shipment['tracking_number'], $carrier, $service, $shipment['shipmentid']);
		}

    }
    
    public function updateOrderB2b($orderId, $tracking_number, $carrier, $carrierTitle, $shipmentId) {
		
		//echo $shipmentId . '<br>';
		$order = Mage::getModel('sales/order')->load($orderId);
		$email = $order->getCustomerEmail();
		$orderNumber = $order->getIncrementId();
		
		try
		{
			
			$orderStatus = $order->getStatus();
		
			//This converts the order to "Completed".
			if($orderStatus == 'in_process') {
				$converter = Mage::getModel('sales/convert_order');
				$shipment = $converter->toShipment($order);
				
				
				foreach ($order->getAllItems() as $orderItem)
		        {	
		        	
		        	if (!$orderItem->getQtyToShip()) {
					    continue;
					}
					if ($orderItem->getIsVirtual()) {
					    continue;
					}
					
					$db1 = Mage::getSingleton('core/resource')->getConnection('core_read');
					$shipmentItems = $db1->query("SELECT UPC, Units FROM bulk_process_b2b where CustPO = '" . $shipmentId . "'");
					
					foreach($shipmentItems as $shipmentItem) {
						
						$product = Mage::getModel('catalog/product')->loadByAttribute('upc',$shipmentItem['UPC']);
						
						if($product) {
						    
						    if(!$orderItem->isDummy()) {
						    	if($orderItem->getData('product_type') != 'bundle') {
									if($this->getItemUpc($orderItem) == $shipmentItem['UPC']) {
										$item = $converter->itemToShipmentItem($orderItem);
										$qty = $shipmentItem['Units'];
										$item->setQty($qty);
										$shipment->addItem($item);
									}
								}
				            }
						}
					}
		        }
		        
				$data = array();
				$data['carrier_code'] = $carrier;
				$data['title'] = $carrierTitle;
				$data['number'] = $tracking_number;
				
				$track = Mage::getModel('sales/order_shipment_track')->addData($data);
				$shipment->addTrack($track);
				
				$shipment->register();
				$shipment->setEmailSent(true);
				$shipment->getOrder()->setIsInProcess(true);
					
				$transactionSave = Mage::getModel('core/resource_transaction')->addObject($shipment)->addObject($shipment->getOrder())->save();
				
				$track->save();
				$shipment->sendEmail($email);
				$order->setStatus('shipped'); 
				$order->addStatusToHistory('shipped', '', false);
		
				$order->save();
			
			}
		}
			
		catch (Exception $e)
			
		{
			mail('support@cobragolf.com', 'Tried To Ship Order ' . $orderNumber, $e->getMessage());
		}
	    
	}
    
    public function updateOrder($orderId, $tracking_number, $carrier, $carrierTitle, $shipmentId) {
		
		//echo $shipmentId . '<br>';
		$order = Mage::getModel('sales/order')->load($orderId);
		$email = $order->getCustomerEmail();
		$orderNumber = $order->getIncrementId();
		
		try
		{
			
			$orderStatus = $order->getStatus();
		
			//This converts the order to "Completed".
			if( $orderStatus == 'sent_to_warehouse') {
				$converter = Mage::getModel('sales/convert_order');
				$shipment = $converter->toShipment($order);
				
				
				foreach ($order->getAllItems() as $orderItem)
		        {	
		        	
		        	if (!$orderItem->getQtyToShip()) {
					    continue;
					}
					if ($orderItem->getIsVirtual()) {
					    continue;
					}
					
					$db1 = Mage::getSingleton('core/resource')->getConnection('core_read');
					$shipmentItems = $db1->query("SELECT UPC, SHIPPEDUNITS FROM bulk_process where SHIPMENTID = '" . $shipmentId . "'");
					
					foreach($shipmentItems as $shipmentItem) {
						
						$product = Mage::getModel('catalog/product')->loadByAttribute('upc',$shipmentItem['UPC']);
						
						if($product) {
							
	/*
							if($orderItem->getParentItemId()) {
								$parent_product_type = Mage::getModel('sales/order_item')->load($orderItem->getParentItemId())->getProductType();
								//if Parent product type is Bundle
								if($parent_product_type == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
									if($this->getItemUpc($orderItem) == $shipmentItem['UPC']) {
	
										$item = $converter->itemToShipmentItem($orderItem);
										$qty = $shipmentItem['SHIPPEDUNITS'];
										$item->setQty($qty);
										$shipment->addItem($item);
	
									}
								}
						    }
	*/
						    
						    if(!$orderItem->isDummy()) {
						    	if($orderItem->getData('product_type') != 'bundle') {
									if($this->getItemUpc($orderItem) == $shipmentItem['UPC']) {
										//echo 'UPC '. $shipmentItem['UPC'];
										//echo ' ID TO ADD TO SHIPMENT: ' . $orderItem->getId() . ' SHIPMENT PROD SKU: ' . $product->getSku() . 'QTY: ' . $shipmentItem['SHIPPEDUNITS'] . '<br>';
										$item = $converter->itemToShipmentItem($orderItem);
										$qty = $shipmentItem['SHIPPEDUNITS'];
										$item->setQty($qty);
										$shipment->addItem($item);
									}
								}
				            }
						}
					}
		        }
		        
				$data = array();
				$data['carrier_code'] = $carrier;
				$data['title'] = $carrierTitle;
				$data['number'] = $tracking_number;
				
				$track = Mage::getModel('sales/order_shipment_track')->addData($data);
				$shipment->addTrack($track);
				
				$shipment->register();
				$shipment->setEmailSent(true);
				$shipment->getOrder()->setIsInProcess(true);
					
				$transactionSave = Mage::getModel('core/resource_transaction')->addObject($shipment)->addObject($shipment->getOrder())->save();
				
				$track->save();
				$shipment->sendEmail($email);
				//$order->setStatus('complete'); 
				//$order->addStatusToHistory('complete', '', false);
		
				$order->save();
			
			}
		}
			
		catch (Exception $e)
			
		{
			mail('support@cobragolf.com', 'Tried To Ship Order ' . $orderNumber, $e->getMessage());
		}
	    
	}
	
	public function csv_file_to_mysql_table($source_file, $target_table, $max_line_length=10000) {
		$db = Mage::getSingleton('core/resource')->getConnection('core_read');
		$db->query("TRUNCATE TABLE $target_table");
	    if (($handle = fopen("$source_file", "r")) !== FALSE) { 
	        $columns = fgetcsv($handle, $max_line_length, ","); 
	        foreach ($columns as &$column) { 
	            $column = str_replace(" ","",$column); 
	        } 
	        $insert_query_prefix = "INSERT INTO $target_table (".join(",",$columns).")\nVALUES"; 
	        while (($data = fgetcsv($handle, $max_line_length, ",")) !== FALSE) { 
	            while (count($data)<count($columns)) 
	                array_push($data, NULL); 
	            $query = "$insert_query_prefix (".join(",",$this->quote_all_array($data)).");";
	            //echo $query;
	            $db->query($query); 
	        } 
	        fclose($handle);
	        rename($source_file, self::$homeEdiDir . '/archive/order_import_'.date("Ymd_His").'.csv');
	    } 
	} 
	
	public function quote_all_array($values) { 
	    foreach ($values as $key=>$value) 
	        if (is_array($value)) 
	            $values[$key] = $this->quote_all_array($value); 
	        else 
	            $values[$key] = $this->quote_all($value); 
	    return $values; 
	} 
	
	public function quote_all($value) { 
	    if (is_null($value)) 
	        return "NULL"; 
	
	    $value = "'" . $value . "'"; 
	    return $value; 
	}
    //**

    /**
	 * Writes the head row with the column names in the csv file.
	 *
	 * @param $fp The file handle of the csv file
	 */
    protected function writeHeadRow($fp)
    {
        fputcsv($fp, $this->getHeadRowValues(), self::DELIMITER, self::ENCLOSURE);
    }

    /**
	 * Writes the row(s) for the given order in the csv file.
	 * A row is added to the csv file for each ordered item.
	 *
	 * @param Mage_Sales_Model_Order $order The order to write csv of
	 * @param $fp The file handle of the csv file
	 */
	 
protected function writeOrder($order, $fileName, $items = null)
    {
        
        $orderItems = $order->getItemsCollection();
        
        if($orderItems) {
        	//$fp = fopen(self::$homeEdiDir . '/export/'.$fileName, 'w');
	        //$orderItems = $order->getItemsCollection('simple');
	        $itemInc = 0;
	        //fputcsv($fp, $common, self::DELIMITER, self::ENCLOSURE);
	        //fputcsv($fp, $shipping, self::DELIMITER, self::ENCLOSURE);
	        $qtyOrdered = 0;
	        
	        foreach ($orderItems as $item)
	        {
	            if (!$item->isDummy()) {
	            	if(in_array($item->getId(), $items) || $items == null) {
		            	$qtyOrdered = $qtyOrdered + $item->getQtyOrdered();
		                // $record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
		                $record[] = $this->getOrderItemValues($item, $order, ++$itemInc);
		                $sentItems .= $item->getSku() . ' ';
		                //fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
	                }
	            }
	        }
	        
	        //var_dump($record);
	        $product_types = $this->array_column($record, 'product_type');
	        $product_types = array_unique($product_types);
	        if(count($product_types) > 1) {
		        foreach($product_types as $product_type) {
		        	$common = $this->getCommonOrderValues($order, ' - ' . $product_type);
					$shipping = $this->getShippingOrderValues($order);
		        	$fp = fopen(self::$homeEdiDir . '/export/' . $fileName . ' - ' . $product_type . '.txt', 'w');
		        	$total_qty_ordered = 0;
		        	$num_items = 0;
		        	$subtotal = 0;
    		        fputcsv($fp, $common, self::DELIMITER, self::ENCLOSURE);
					fputcsv($fp, $shipping, self::DELIMITER, self::ENCLOSURE);
			        foreach($record as $item) {
				        if($item['product_type'] == $product_type) {
					        fputcsv($fp, array('D', $item['sku'], $item['sku'], '', '', $item['upc'], $item['qty'], $item['price'], '', ''), self::DELIMITER, self::ENCLOSURE);
					        $total_qty_ordered = $total_qty_ordered + $item['qty'];
					        $num_items = $num_items + 1;
					        if($order->getStoreId() == 14) {
					        	$subtotal = $subtotal + ($item['qty'] * $item['price']);
					        } else {
					        	$subtotal = 0;
					        }
				        }
			        }
			        fputcsv($fp, array('Z', $num_items, $total_qty_ordered, number_format($subtotal, 2), '', '', '', '', ''), self::DELIMITER, self::ENCLOSURE);
			        fclose($fp);
		            $file_contents = file_get_contents(self::$homeEdiDir . '/export/'.$fileName . ' - ' . $product_type . '.txt');
		            $file_contents = str_replace('"', '' ,$file_contents);
		            $file_contents = $this->normalize($file_contents);
		            file_put_contents(self::$homeEdiDir . '/export/'.$fileName . ' - ' . $product_type . '.txt', $file_contents);
		        }
	        } else {
		        $common = $this->getCommonOrderValues($order);
				$shipping = $this->getShippingOrderValues($order);
	        	$fp = fopen(self::$homeEdiDir . '/export/' . $fileName . '.txt', 'w');
	        	$total_qty_ordered = 0;
	        	$num_items = 0;
	        	$subtotal = 0;
		        fputcsv($fp, $common, self::DELIMITER, self::ENCLOSURE);
				fputcsv($fp, $shipping, self::DELIMITER, self::ENCLOSURE);
		        foreach($record as $item) {
			        fputcsv($fp, array('D', $item['sku'], $item['sku'], '', '', $item['upc'], $item['qty'], $item['price'], '', ''), self::DELIMITER, self::ENCLOSURE);
			        $total_qty_ordered = $total_qty_ordered + $item['qty'];
			        $num_items = $num_items + 1;
			        if($order->getStoreId() == 14) {
			        	$subtotal = $subtotal + ($item['qty'] * $item['price']);
			        } else {
			        	$subtotal = 0;
			        }
		        }
		        fputcsv($fp, array('Z', $num_items, $total_qty_ordered, number_format($subtotal, 2), '', '', '', '', ''), self::DELIMITER, self::ENCLOSURE);
		        fclose($fp);
	            $file_contents = file_get_contents(self::$homeEdiDir . '/export/'.$fileName . '.txt');
	            $file_contents = str_replace('"', '' ,$file_contents);
	            $file_contents = $this->normalize($file_contents);
	            file_put_contents(self::$homeEdiDir . '/export/'.$fileName . '.txt', $file_contents);
	        }
	        return $sentItems;
        }
    }
    
	function array_column(array $array, $returnvaluekey = null, $returnkey = null) {
	    if($returnvaluekey===null) {
	        return false;
	    }
	    $columns = array();
	    foreach($array as $key=>$value) {
	        if(!is_array($array[$key]) || empty($array[$key])) {
	            continue;
	        }
	        if($returnkey===null) {
	            if(isset($array[$key][$returnvaluekey])) {
	                $columns[] = $array[$key][$returnvaluekey];
	            }
	        }
	        else {
	            if(isset($array[$key][$returnkey]) && isset($array[$key][$returnvaluekey])) {
	                $columns[$array[$key][$returnkey]] = $array[$key][$returnvaluekey];
	            }
	        }
	    }
	    return $columns;
	}
	 
    /*
protected function writeOrder($order, $fp)
    {
        $common = $this->getCommonOrderValues($order);
        $shipping = $this->getShippingOrderValues($order);
                
        $orderItems = $order->getItemsCollection('simple');
        $itemInc = 0;
        fputcsv($fp, $common, self::DELIMITER, self::ENCLOSURE);
        fputcsv($fp, $shipping, self::DELIMITER, self::ENCLOSURE);
        $qtyOrdered = 0;
        $line_items_count = 0;
        
        foreach ($orderItems as $item)
        {
            if (!$item->isDummy()) {
            	$qtyOrdered = $qtyOrdered + $item->getQtyOrdered();
                // $record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
                $record = $this->getOrderItemValues($item, $order, ++$itemInc);
                fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
                $line_items_count++;
            }
        }
        
        $totals = $this->getOrderSummary($order, $qtyOrdered,$line_items_count);        
        fputcsv($fp, $totals, self::DELIMITER, self::ENCLOSURE);
    }
*/

    /**
	 * Returns the head column names.
	 *
	 * @return Array The array containing all column names
	 */
    protected function getHeadRowValues()
    {
        return array(
            'Order Number',
            'Order Date',
            'Order Status',
            'Order Purchased From',
            'Order Payment Method',
        	'Credit Card Type',
            'Order Shipping Method',
            'Order Subtotal',
            'Order Tax',
            'Order Shipping',
            'Order Discount',
            'Order Grand Total',
            'Order Base Grand Total',
        	'Order Paid',
            'Order Refunded',
            'Order Due',
            'Total Qty Items Ordered',
            'Customer Name',
            'Customer Email',
            'Shipping Name',
            'Shipping Company',
            'Shipping Street',
            'Shipping Zip',
            'Shipping City',
        	'Shipping State',
            'Shipping State Name',
            'Shipping Country',
            'Shipping Country Name',
            'Shipping Phone Number',
    		'Billing Name',
            'Billing Company',
            'Billing Street',
            'Billing Zip',
            'Billing City',
        	'Billing State',
            'Billing State Name',
            'Billing Country',
            'Billing Country Name',
            'Billing Phone Number',
            'Order Item Increment',
    		'Item Name',
            'Item Status',
            'Item SKU',
            'Item Options',
            'Item Original Price',
    		'Item Price',
            'Item Qty Ordered',
        	'Item Qty Invoiced',
        	'Item Qty Shipped',
        	'Item Qty Canceled',
            'Item Qty Refunded',
            'Item Tax',
            'Item Discount',
    		'Item Total'
    	);
    }

    /**
	 * Returns the values which are identical for each row of the given order. These are
	 * all the values which are not item specific: order data, shipping address, billing
	 * address and order totals.
	 *
	 * @param Mage_Sales_Model_Order $order The order to get values from
	 * @return Array The array containing the non item specific values
	 */
    protected function getCommonOrderValues($order, $product_type = NULL)
    {
        $shippingAddress = !$order->getIsVirtual() ? $order->getShippingAddress() : null;
        $billingAddress = $order->getBillingAddress();
        $shipping_method = trim($order->getShippingDescription());
        if($shipping_method == '2 Day Shipping') {
	        $shipping_method = 'EC1';
        } elseif($shipping_method == '3 Day Shipping') {
        	$shipping_method = 'EC2';
        } elseif($shipping_method == 'Free FedEx Ground Shipping') {
        	$shipping_method = 'ENC';
        } elseif($shipping_method == 'FedEx Ground (1-5 Days)') {
        	$shipping_method = 'ENC';
        } elseif($shipping_method == 'Free Expedited Shipping Upgrade (2-3 Days)') {
        	$shipping_method = 'EC2';
        } elseif($shipping_method == 'Free Shipping') {
        	$shipping_method = 'ENC';
        } elseif($order->getStoreId() == 14) {
        	$shipping_method = '';
        } else {
	        echo 'Shipping Method not found!';
	        exit;
        }
        
        if($order->getStoreId() == 14) {
	        $jesta_prefix = 'RBB';
	        $order_type = 'COBRA B2B';
	        $payment = $order->getPayment();
	        $po_number = $order->getRealOrderId() . $product_type; //Flipped position for Jesta B2B
	        $order_number =  $payment->getPoNumber() . $product_type; //Flipped position for Jesta B2B
	        $first_name = $order->getCustomerFirstname();
	        $last_name = $order->getCustomerLastname();
	        $customerObj = Mage::getModel('customer/customer')->load($order->getCustomerId());
	        $account_id = $customerObj->getData('edi_id');
	        if(strpos($product_type,'CL') === true) {
		        $customerGroupId = $order->getCustomerGroupId();
				$customer_group = Mage::getModel('customer/group')->load($customerGroupId)->getCustomerGroupCode();
	        } else {
		        $customer_group = '';
	        }
        } else {
	        $jesta_prefix = 'EE';
	        $po_number = $order->getRealOrderId() . $product_type;
	        $order_number = $order->getRealOrderId() . $product_type;
	        $order_type = 'Cobra Ecomm';
	        $account_id = 502188;
	        $first_name = 'Cobra';
	        $last_name = 'Ecomm';
	        $customer_group = '';
        }
        
        return array(
        	'H',
            $order_number,
            $po_number,
            $order_type,
            Mage::getModel('core/date')->date('m/d/Y', strtotime($order->getCreatedAt())),
            $jesta_prefix,
            Mage::getModel('core/date')->date('m/d/Y', strtotime($order->getCreatedAt())),
            '',
            Mage::getModel('core/date')->date('m/d/Y', strtotime($order->getCreatedAt() . ' +30 day')),
            $account_id,
            /*
	        $order->getStatus(),
            $this->getStoreName($order),
            $this->getPaymentMethod($order),
            $this->getCcType($order),
            $this->getShippingMethod($order),
            $this->formatPrice($order->getData('subtotal'), $order),
            $this->formatPrice($order->getData('tax_amount'), $order),
            $this->formatPrice($order->getData('shipping_amount'), $order),
            $this->formatPrice($order->getData('discount_amount'), $order),
            $this->formatPrice($order->getData('grand_total'), $order),
            $this->formatPrice($order->getData('base_grand_total'), $order),
            $this->formatPrice($order->getData('total_paid'), $order),
            $this->formatPrice($order->getData('total_refunded'), $order),
            $this->formatPrice($order->getData('total_due'), $order),
            $this->getTotalQtyItemsOrdered($order),
            */
            /*
$order->getCustomerFirstname(),
            $order->getCustomerLastname(),
*/
			$first_name,
			$last_name,
			'',
			$customer_group,
			'',
			'',
			'',
			'USD',
			//$order->getShippingMethod(),
			$shipping_method,
			'',
			2,
			'',
			''
            /*
$order->getCustomerEmail(),
            $shippingAddress ? $shippingAddress->getName() : '',
            $shippingAddress ? $shippingAddress->getData("company") : '',
            $shippingAddress ? $this->getStreet($shippingAddress) : '',
            $shippingAddress ? $shippingAddress->getData("postcode") : '',
            $shippingAddress ? $shippingAddress->getData("city") : '',
            $shippingAddress ? $shippingAddress->getRegionCode() : '',
            $shippingAddress ? $shippingAddress->getRegion() : '',
            $shippingAddress ? $shippingAddress->getCountry() : '',
            $shippingAddress ? $shippingAddress->getCountryModel()->getName() : '',
            $shippingAddress ? $shippingAddress->getData("telephone") : '',
            $billingAddress->getName(),
            $billingAddress->getData("company"),
            $this->getStreet($billingAddress),
            $billingAddress->getData("postcode"),
            $billingAddress->getData("city"),
            $billingAddress->getRegionCode(),
            $billingAddress->getRegion(),
            $billingAddress->getCountry(),
            $billingAddress->getCountryModel()->getName(),
            $billingAddress->getData("telephone")
*/
        );
    }
    
    protected function getShippingOrderValues($order)
    {
        $shippingAddress = !$order->getIsVirtual() ? $order->getShippingAddress() : null;
        $billingAddress = $order->getBillingAddress();
        
        if($order->getStoreId() == 14) {
	        $address_id = $shippingAddress->getData("ship_to_id");
	        if(!isset($address_id)) {
		        $address_id = 'DROP';
	        }
	        if($address_id == ' ') {
		        $address_id = 'DROP';
	        }
	        //$address_id = 'DROP';
        } else {
	        $address_id = 'DROP';
        }

        return array(
        	'S',
        	$address_id,
        	$shippingAddress ? $shippingAddress->getName() : '',
        	$shippingAddress ? $shippingAddress->getStreet1() : '',
        	$shippingAddress ? $shippingAddress->getStreet2() : '',
        	'',
        	$shippingAddress ? $shippingAddress->getData("city") : '',
        	$shippingAddress ? $shippingAddress->getRegionCode() : '',
        	$shippingAddress ? $shippingAddress->getCountry() : '',
        	$shippingAddress ? substr($shippingAddress->getData("postcode"),0,5) : '',
        	$shippingAddress ? $shippingAddress->getName() : '',
        	$shippingAddress ? $shippingAddress->getData("telephone") : '',
        	'',
        	$order->getCustomerEmail()
        );
    }
    
    protected function getOrderSummary($order, $qtyOrdered)
    {
        return array(
        	'Z',
        	count($order->getAllVisibleItems()),
        	$qtyOrdered,
        	str_replace('$', '', $this->formatPrice($order->getSubtotal(), $order)),
        	'',
        	'',
        	//str_replace('$', '', $this->formatPrice($order->getShippingAmount(), $order)),
        	'',
        	'',
        	''
        	//str_replace('$', '', $this->formatPrice($order->getGrandTotal(), $order)),
        );
    }
    
    /*
protected function getOrderSummary($order, $qtyOrdered, $line_items_count)
    {	
        return array(
        	'Z',
        	$line_items_count,
        	$qtyOrdered,
        	str_replace('$', '', $this->formatPrice($order->getSubtotal(), $order)),
        	'',
        	'',
        	//str_replace('$', '', $this->formatPrice($order->getShippingAmount(), $order)),
        	'',
        	'',
        	''
        	//str_replace('$', '', $this->formatPrice($order->getGrandTotal(), $order)),
        );
    }
*/


    /**
	 * Returns the item specific values.
	 *
	 * @param Mage_Sales_Model_Order_Item $item The item to get values from
	 * @param Mage_Sales_Model_Order $order The order the item belongs to
	 * @return Array The array containing the item specific values
	 */
    protected function getOrderItemValues($item, $order, $itemInc=1)
    {
    	/*
$custom_note_text = '';
    	$orderComments = $order->getAllStatusHistory();
    	
    	$optionsArr = $item->getProductOptions();
    	
    	if(count($optionsArr['options']) > 0) {
	        foreach ($optionsArr['options'] as $option) {
	        	if($option['label'] == 'Custom Note') {
	            	$custom_note_text = substr($option['value'], 0, 80);
	        	}
	         }
	    }
*/
		if($order->getStoreId() == 14) {
			$price = str_replace('$', '', $this->formatPrice($item->getData('price') * ((100 - $item->getData('discount_percent'))/100), $order));
		} else {
			$price = str_replace('$', '', $this->formatPrice(0, $order));
		}

        return array(
        	//'D',
            //$itemInc,
            //$item->getName(),
            //$item->getStatus(),
            'sku' => $this->getItemSku($item),
            //$this->getItemSku($item),
            //$this->getItemOptions($item),
            //$this->formatPrice($item->getOriginalPrice(), $order),
            //$this->formatPrice($item->getData('price'), $order),
            //'',
            //'',
            'product_type' => $this->getItemCpgType($item),
            'upc' => $this->getItemUpc($item),
            'qty' => (int)$item->getQtyOrdered(),
            //'price' => str_replace('$', '', $this->formatPrice($item->getData('price'), $order)),
            'price' => $price,
            //$custom_note_text,
            //''
            //(int)$item->getQtyInvoiced(),
            //(int)$item->getQtyShipped(),
            //(int)$item->getQtyCanceled(),
        	//(int)$item->getQtyRefunded(),
            //$this->formatPrice($item->getTaxAmount(), $order),
            //$this->formatPrice($item->getDiscountAmount(), $order),
            //$this->formatPrice($this->getItemTotal($item), $order)
        );
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
	
	public function importStock() {
	
		$count = 0;
		
		$db = Mage::getSingleton('core/resource')->getConnection('core_read');
		$db->query("TRUNCATE TABLE bulk_inventory_update");
		
		if ($handle = opendir(self::$homeEdiDir . '/inventory')) {
				
		    while (false !== ($entry = readdir($handle))) {
		    	if ($entry != "." && $entry != ".." && $entry != "archive" && strpos($entry,'Bulk') == true && strpos($entry,'zip') == false && strpos($entry,'BtoB') === false && strpos($entry,'ca_B') === false && strpos($entry,'ca_C') === false) {
					//echo self::$homeEdiDir . '/inventory/'.$entry;
					
					
					$file = fopen(self::$homeEdiDir . '/inventory/'.$entry, 'r');
					while (($line = fgetcsv($file)) !== FALSE) {
					
						if ($count == 0) {
							foreach ($line as $key=>$value) {
								$cols[$value] = $key;
							}
						}
					
						$count++;
					
						if ($count == 1) continue;
					
						#Convert the lines to cols
						if ($count > 0) {
							foreach($cols as $col=>$value) {
								unset(${$col});
								${$col} = $line[$value];
							}
						}
					
						// Check if SKU exists
						$product = Mage::getModel('catalog/product')->loadByAttribute('upc',$UPC);
					
						if ( $product ) {
							$db->query("INSERT INTO bulk_inventory_update VALUES ('" . $product->getId() . "', '" . $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product) . "'," . $QTY . "," . $Cost . ",'" . $Label . "')");
							 
							$product->setCost($Cost);
							
							if($Label != '') {
								if($Label == 'AC') {
									$option_id = 687;
								} elseif($Label == 'AP') {
									$option_id = 688;
								} elseif($Label == 'CL') {
									$option_id = 686;
								} elseif($Label == 'FT') {
									$option_id = 689;
								} elseif($Label == 'RM') {
									$option_id = 685;
								} elseif($Label == 'WA') {
									$option_id = 892;
								}
								
								$product->setData('cpg_product_type', $option_id);
							}
							$product->save();
							unset($option_id);
		
						}			
					}
					fclose($file);
					
					rename(self::$homeEdiDir . '/inventory/'.$entry, self::$homeEdiDir . '/archive/bulk_import_'.date("Ymd_His").'.csv');
				
		        } elseif($entry != "." && $entry != ".." && $entry != "archive" && strpos($entry,'Bulk') == false && strpos($entry,'BtoB') === false && strpos($entry,'Tracking') == false && strpos($entry,'zip') == false && strpos($entry,'ca_B') === false && strpos($entry,'ca_C') === false) {
					
					$file = fopen(self::$homeEdiDir . '/inventory/'.$entry, 'r');
					while (($line = fgetcsv($file)) !== FALSE) {
					
						if ($count == 0) {
							foreach ($line as $key=>$value) {
								$cols[$value] = $key;
							}
						}
					
						$count++;
					
						if ($count == 1) continue;
					
						#Convert the lines to cols
						if ($count > 0) {
							foreach($cols as $col=>$value) {
								unset(${$col});
								${$col} = $line[$value];
							}
						}
					
						// Check if SKU exists
						$product = Mage::getModel('catalog/product')->loadByAttribute('upc',$UPC);
					
						if ( $product ) {
							
							$db->query("INSERT INTO bulk_inventory_update VALUES ('" . $product->getId() . "', '" . $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product) . "'," . $QTY . "," . $Cost . ",'" . $Label . "')");
							
							$product->setCost($Cost);
							
							if($Label != '') {
								if($Label == 'AC') {
									$option_id = 687;
								} elseif($Label == 'AP') {
									$option_id = 688;
								} elseif($Label == 'CL') {
									$option_id = 686;
								} elseif($Label == 'FT') {
									$option_id = 689;
								} elseif($Label == 'RM') {
									$option_id = 685;
								} elseif($Label == 'WA') {
									$option_id = 892;
								}
								
								$product->setData('cpg_product_type', $option_id);
							}
							$product->save();
							unset($option_id);
		
						}
					
					}
					fclose($file);
					
					rename(self::$homeEdiDir . '/inventory/'.$entry, self::$homeEdiDir . '/archive/inventory_import_'.date("Ymd_His").'.csv');
					
		        } elseif($entry != "." && $entry != ".." && $entry != "archive" && strpos($entry,'Bulk') == false && strpos($entry,'Tracking') == true && strpos($entry,'zip') == false && strpos($entry,'BtoB') === false && strpos($entry,'ca_B') === false && strpos($entry,'ca_C') === false) {
			      	
			      	//importOrdersB2b(self::$homeEdiDir . '/inventory/'.$entry);
			      	$file_contents = file_get_contents(self::$homeEdiDir . '/inventory/'.$entry);
			      	
			      	try {
				      	$mail = new Zend_Mail();
			            $mail->setFrom("support@cobragolf.com","Apache");
			            $mail->addTo("support@cobragolf.com","support@cobragolf.com");
			            $mail->setSubject("Custom Order Tracking " . date("Y-m-d H:i:s"));
			            $mail->setBodyHtml("Tracking info."); // here u also use setBodyText options.
			 
			            // this is for to set the file format
			            $at = new Zend_Mime_Part($file_contents);
			 
			            $at->type        = 'application/csv'; // if u have PDF then it would like -> 'application/pdf'
			            $at->disposition = Zend_Mime::DISPOSITION_INLINE;
			            $at->encoding    = Zend_Mime::ENCODING_8BIT;
			            $at->filename    = 'custom_tracking.csv';
			            $mail->addAttachment($at);
			            $mail->send();
			            
			            rename(self::$homeEdiDir . '/inventory/'.$entry, self::$homeEdiDir . '/archive/custom_tracking_'.date("Ymd_His").'.csv');

			        } catch(Exception $e)
			        
			        {
			            echo $e->getMessage();
			 
			        }
		        }
		    }
		    closedir($handle);
		    
		    $prod_query = "SELECT prod_id, SUM(qty) as qty, cost, cpg_product_type FROM bulk_inventory_update GROUP BY prod_id";
		    $update_qty = $db->fetchAll($prod_query);		    
		    
		    if(count($update_qty) < 2) {
			    mail('support@cobragolf.com', 'No Inventory Files Available', 'Check the FTP site.');
		    } else {
			    		    
		    foreach($update_qty as $item) {
		    
		    	$product = Mage::getModel('catalog/product')->load($item["prod_id"]);
		    	
		    	if($product->getResource()->getAttribute('do_not_use_jesta_inventory')->getFrontend()->getValue($product) != 'Yes') {
			    	$QTY = $item["qty"];
			    	$COST = $item["cost"];
			    	$CPG_PRODUCT_TYPE = $item["cpg_product_type"];
			    	$productId = $product->getId();
					$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
					$stockItemId = $stockItem->getId();
					$stock = array();
			
					if (!$stockItemId) {
						$stockItem->setData('product_id', $product->getId());
						$stockItem->setData('stock_id', 1);
					} else {
						$stock = $stockItem->getData();
					}
					
					if($QTY > 1) {
						$stockItem->setData('is_in_stock', 1);
					} else {
						$stockItem->setData('is_in_stock', 0);
					}
					
					$stockItem->setData('qty', $QTY);
					$message .= 'SKU: ' . $product->getSku() . ' QTY: ' .$QTY . "\r\n";
			
					$stockItem->save();
			
					unset($stockItem);
				}
				unset($product);
			    
		    }
		    
			mail('support@cobragolf.com', 'Final Inventory Update', $message);
			
		    $message = '';
		
		    $zero_stock_items = $db->query("select entity_id from catalog_product_entity where entity_id not in (SELECT prod_id from bulk_inventory_update) and type_id='simple'");
		    
		    foreach($zero_stock_items as $item) {
		    
			    $product = Mage::getModel('catalog/product')->load($item["entity_id"]);
			    
			    if($product) {
			    	if($product->getResource()->getAttribute('do_not_use_jesta_inventory')->getFrontend()->getValue($product) != 'Yes') {
						if($product->getResource()->getAttribute('cpg_product_type')->getFrontend()->getValue($product) != 'CL') {
							
							$productId = $product->getId();
							$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
							$stockItemId = $stockItem->getId();
							$stock = array();
					
							if (!$stockItemId) {
								$stockItem->setData('product_id', $product->getId());
								$stockItem->setData('stock_id', 1);
							} else {
								$stock = $stockItem->getData();
							}
							
							$stockItem->setData('is_in_stock', 0);
							$stockItem->setData('qty', 0);
							
							$message .= 'Name: ' . $product->getName() . ' SKU: ' . $product->getSku() . "\r\n";
					
							$stockItem->save();
					
							unset($stockItem);
							unset($product);
							
						}
					}
				}
		    }
		    
		    mail('support@cobragolf.com', 'Zero Out Inventory', $message);
		}
		}
	}

}
/*
 * init function
 */
Bluejalappeno_Orderexport_Model_Export_Csv::init();
?>