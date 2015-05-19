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
 * @author     Genevieve Eddison, Jonathan Feist, Farai Kanyepi <sales@bluejalappeno.com>
 * */
class Bluejalappeno_Orderexport_Sales_Order_ExportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Exports orders defined by id in post param "order_ids" to csv and offers file directly for download
     * when finished.
     */
    public function csvexportAction()
    {
    	$orders = $this->getRequest()->getPost('order_ids', array());
    	$items = $this->getRequest()->getPost('item_ids', array());

		switch(Mage::getStoreConfig('order_export/export_orders/output_type')){
			case 'Standard':
				$file = Mage::getModel('bluejalappeno_orderexport/export_csv')->exportOrders($orders,$items);
				//$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
				//$this->_prepareDownloadResponse($file, file_get_contents('/home/edi/export/'.$file));
				if(empty($items)) {
					$this->_redirect('*/sales_order');
				} else {
					$id = 4131;
					$order = Mage::getModel('sales/order')->load($id);
					Mage::register('sales_order', $order);
					Mage::register('current_order', $order);
			        $html = $this->getLayout()->createBlock('adminhtml/sales_order_view_history')->toHtml();
			        Mage::log('html'.$html . $id);
			        $this->getResponse()->setBody($html);
				}
				break;
			case 'Sage':
				$file = Mage::getModel('bluejalappeno_orderexport/export_sage')->exportOrders($orders);
				$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
				break;
			case 'Highrise':
				$failedList = '';
    			$successCount = 0;
    			$failCount = 0;
            	try {
                	$results = Mage::getModel('bluejalappeno_orderexport/export_highrise')->exportOrders($orders);
	            	foreach ($results as $orderid => $status) {
	    				if ($status > 0 ) $successCount++;
	    				else {
	    					$failedList.= $orderid .' ';
	    					$failCount++;
	    				}
	    			}
            	}
            	catch (Exception $e) {
            		Mage::log($e->getMessage());
            		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sales')->__($e->getMessage()));
            	}

	        	if ($failCount > 0) {
	        		$failedString = $successCount .' order(s) have been imported. The following orders failed to import: ' .$failedList;
	        		$this->_getSession()->addError($this->__( $failedString));

	        	}
	        	else {
	            	$this->_getSession()->addSuccess($this->__('%s order(s) have been imported', $successCount));
	        	}
	        	$this->_redirect('*/sales_order');
				//$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
				break;
		}
    }
    
    //**EGGHEAD ADD
    public function csvimportAction() {
	    $file = Mage::getModel('bluejalappeno_orderexport/export_csv')->importOrders();
	    $this->_redirect('*/sales_order');
    }
    
    public function ordersreportAction()
    {
    	$orders = $this->getRequest()->getPost('order_ids', array());

		$file = Mage::getModel('bluejalappeno_orderexport/export_reports')->exportOrders($orders);
		$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
    }
    
    public function totalsAction()
    {
    	$orders = $this->getRequest()->getPost('order_ids', array());

		$file = Mage::getModel('bluejalappeno_orderexport/export_totals')->exportOrders($orders);
		$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
    }
    
    public function creditsAction()
    {
    	$credits = $this->getRequest()->getPost('creditmemo_ids', array());

		$file = Mage::getModel('bluejalappeno_orderexport/export_credits')->exportOrders($credits);
		$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
    }
    
    public function creditsitemsAction()
    {
    	$credits = $this->getRequest()->getPost('creditmemo_ids', array());

		$file = Mage::getModel('bluejalappeno_orderexport/export_creditsitems')->exportOrders($credits);
		$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
    }
    
    public function invoicesAction()
    {
    	$invoices = $this->getRequest()->getPost('invoice_ids', array());

		$file = Mage::getModel('bluejalappeno_orderexport/export_invoices')->exportOrders($invoices);
		$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
    }

    public function invoicesitemsAction()
    {
    	$invoices = $this->getRequest()->getPost('invoice_ids', array());

		$file = Mage::getModel('bluejalappeno_orderexport/export_invoicesitems')->exportOrders($invoices);
		$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
    }
    //**
}
?>