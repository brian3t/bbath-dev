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
class Bluejalappeno_Orderexport_Model_Export_Totals extends Bluejalappeno_Orderexport_Model_Export_Abstractcsv
{
    const ENCLOSURE = '"';
    const DELIMITER = ',';

    /**
     * Concrete implementation of abstract method to export given orders to csv file in var/export.
     *
     * @param $orders List of orders of type Mage_Sales_Model_Order or order ids to export.
     * @return String The name of the written csv file in var/export
     */
    public function exportOrders($orders,$items)
    {
        $fileName = 'order_export_'.date("Ymd_His").'.csv';
        $fp = fopen(Mage::getBaseDir('export').'/'.$fileName, 'w');

        $this->writeHeadRow($fp);
        foreach ($orders as $order) {
        	$order = Mage::getModel('sales/order')->load($order);
            $this->writeOrder($order, $fp);
        }

        fclose($fp);

        return $fileName;
    }
    
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
    protected function writeOrder($order, $fp)
    {	
    	if($order->getStatus() != 'canceled') {
	        $common = $this->getCommonOrderValues($order);
			fputcsv($fp, $common, self::DELIMITER, self::ENCLOSURE);
		}
        /*
$orderItems = $order->getAllItems();
        $itemInc = 0;
        
        
        foreach ($orderItems as $item)
        {
        	if ( $item->getParentItemId()) {
		         $parent_product_type = Mage::getModel('sales/order_item')->load($item->getParentItemId())->getProductType();
		          //if Parent product type is Bundle
		          if ($parent_product_type == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
		              $record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
					  fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
		          }
		      }
      
        	if(!$item->isDummy()) {
                $record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
                fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
            }
        }
*/
    }

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
        	'CC Fees',
            'Order Shipping Method',
            'Order Subtotal',
            'Order Tax',
            'Order Shipping',
            'Order Discount',
            'Order Grand Total',
            'Order Base Grand Total',
        	'Order Paid',
            'Order Refunded',
            'Tax Refunded',
            'Shipping Refunded',
            'Subtotal Refunded',
            'Order Due',
            'Order Discount Code',
/*             'Total Qty Items Ordered', */
            'Customer Name',
            'Customer Email',
/*             'Shipping Name', */
/*             'Shipping Company', */
/*
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
            'Item UPC',
            'Item Options',
            'Item Cost',
            'Item Original Price',
    		'Item Price',
            'Item Qty Ordered',
        	'Item Qty Invoiced',
        	'Item Qty Shipped',
        	'Item Qty Canceled',
            'Item Qty Refunded',
            'Item Tax',
            'Item Discount',
    		'Item Total',
    		'Item Subtotal Before Discount',
    		'Item Subtotal Refunded',
    		'Item Tax Refunded',
    		'Product Type'*/
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
    protected function getCommonOrderValues($order)
    {
        $shippingAddress = !$order->getIsVirtual() ? $order->getShippingAddress() : null;
        $billingAddress = $order->getBillingAddress();
        $payment_type = $this->getCcType($order);
        $payment_types = array('DI','MC','VI');
        if(in_array($payment_type, $payment_types)) {
	        $cc_fees = ($order->getData('grand_total') * .022) + .03;
        } elseif ($payment_type = '') {
	        $cc_fees = ($order->getData('grand_total') * .019) + .03;
        } else {
	        $cc_fees = 0;
        }
         
        return array(
            $order->getRealOrderId(),

            Mage::helper('core')->formatDate($order->getCreatedAt(), 'medium', true),
			$order->getStatus(),
            $this->getStoreName($order),
            $this->getPaymentMethod($order),
            $this->getCcType($order),
            $this->formatPrice($cc_fees,$order),
            $this->getShippingMethod($order),
            $this->formatPrice($order->getData('subtotal'), $order),
            $this->formatPrice($order->getData('tax_amount'), $order),
            $this->formatPrice($order->getData('shipping_amount'), $order),
            $this->formatPrice($order->getData('discount_amount'), $order),
            $this->formatPrice($order->getData('grand_total'), $order),
            $this->formatPrice($order->getData('base_grand_total'), $order),
            $this->formatPrice($order->getData('total_paid'), $order),
            $this->formatPrice($order->getData('total_refunded') * -1, $order),
            $this->formatPrice($order->getData('tax_refunded') * -1, $order),
            $this->formatPrice($order->getData('shipping_refunded') * -1, $order),
            $this->formatPrice($order->getData('subtotal_refunded') * -1, $order),
            $this->formatPrice($order->getData('total_due'), $order),
            $order->getData('discount_description'),
/*            $this->getTotalQtyItemsOrdered($order), */
            $order->getCustomerName(),
            $order->getCustomerEmail(),
/*            $shippingAddress ? $shippingAddress->getName() : '',
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

    /**
	 * Returns the item specific values.
	 *
	 * @param Mage_Sales_Model_Order_Item $item The item to get values from
	 * @param Mage_Sales_Model_Order $order The order the item belongs to
	 * @return Array The array containing the item specific values
	 */
    protected function getOrderItemValues($item, $order, $itemInc=1)
    {	if($item->getData('product_type') == 'bundle') {
	    $upc = 'bundle';
    } else {
	    $upc = $this->getItemUpc($item);
    }

        return array(
            $itemInc,
            $item->getName(),
            $item->getStatus(),
            $this->getItemSku($item),
            $upc,
/*             preg_replace( "/\r|\n/", "", $this->getItemOptions($item)), */
            $this->formatPrice($item->getCost(), $order),
/*             $this->formatPrice($item->getOriginalPrice(), $order), */
            $this->formatPrice($item->getData('price'), $order),
            (int)$item->getQtyOrdered(),
            (int)$item->getQtyInvoiced(),
            (int)$item->getQtyShipped(),
            (int)$item->getQtyCanceled(),
        	(int)$item->getQtyRefunded(),
            $this->formatPrice($item->getTaxAmount(), $order),
            $this->formatPrice($item->getDiscountAmount(), $order),
            $this->formatPrice($this->getItemTotal($item), $order),
            $this->formatPrice($this->getItemTotal($item) - $item->getTaxAmount() + $item->getDiscountAmount(), $order),
            $this->formatPrice($item->getData('amount_refunded'), $order),
            $this->formatPrice($item->getData('tax_refunded'), $order),
            $this->getItemCpgType($item)
        );
    }
}
?>