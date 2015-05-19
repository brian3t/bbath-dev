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
class Bluejalappeno_Orderexport_Model_Export_Creditsitems extends Bluejalappeno_Orderexport_Model_Export_Abstractcsv
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
        $fileName = 'credit_memo_items_export_'.date("Ymd_His").'.csv';
        $fp = fopen(Mage::getBaseDir('export').'/'.$fileName, 'w');

        $this->writeHeadRow($fp);
        foreach ($orders as $order) {
        	$order = Mage::getModel('sales/order_creditmemo')->load($order);
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
        $common = $this->getCommonOrderValues($order);
		
		$orderItems = $order->getAllItems();
        $itemInc = 0;
        
        
        foreach ($orderItems as $item)
        {
			if($item->getData('base_price') > 0) {
                $record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
                fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
            }
        }
     }

    /**
	 * Returns the head column names.
	 *
	 * @return Array The array containing all column names
	 */
    protected function getHeadRowValues()
    {
        return array(
            'Credit Memo Number',
            'Originating Order Number',
            'Store',
            'Customer ID',
            'Date',
            'Name',
            'SKU',
            'QTY',
            'Subtotal Refunded',
            'Discount Refunded',
            'Tax Refunded'
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
    	$originating_order = Mage::getModel('sales/order')->load($order->getData('order_id'));
    	
        return array(
            $order->getData('increment_id'),
            //$order->getState(),
            $originating_order->getIncrementId(),
            $this->getStoreName($originating_order),
            $originating_order->getCustomerId(),
            Mage::helper('core')->formatDate($order->getData('created_at'), 'medium', true)
        );
    }
    
    protected function getOrderItemValues($item, $order, $itemInc=1)
    {	
        return array(
            $item->getName(),
            $this->getItemSku($item),
            $item->getData('qty'),
            Mage::helper('core')->currency($item->getData('row_total'), true, false),
			Mage::helper('core')->currency($item->getData('discount_amount'), true, false),
			Mage::helper('core')->currency($item->getData('tax_amount'), true, false)
        );
    }
}
?>