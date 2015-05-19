<?php
/* DO NOT MODIFY THIS FILE! THIS IS TEMPORARY FILE AND WILL BE RE-GENERATED AS SOON AS CACHE CLEARED. */

class Aitoc_Aitpermissions_Block_Rewrite_AdminSalesOrderGrid extends Mage_Adminhtml_Block_Sales_Order_Grid
{

	public function __construct()
	{
		parent::__construct();
	}
	
	// MUST override setCollection rather than _prepareCollection to get filtering and paging both working
    public function setCollection($collection)
    {
        parent::setCollection($collection);
        if ($this->_isExport) return;

        $collection->getSize(); // Get size before adding join
        $collection->join(
            'sales/order_item',
            '`sales/order_item`.order_id=`main_table`.entity_id',
            array(
                //'skus'  => new Zend_Db_Expr('group_concat(`sales/order_item`.sku SEPARATOR "<br/>")'),
                'names' => new Zend_Db_Expr('group_concat(concat( \'(\', TRUNCATE(`sales/order_item`.qty_ordered, 0), \') \', `sales/order_item`.name) SEPARATOR "<br/>")'),
            )
        );
        $collection->join(
            'sales/order',
            '`sales/order`.entity_id=`main_table`.entity_id',
            array(
                'shipping_description' => new Zend_Db_Expr('REPLACE(`sales/order`.shipping_description, \'Shipping Method - \', \'\')'),
                'ship_date' => 'gomage_deliverydate_formated'
            )
        );

		$collection->getSelect()->join('sales_flat_order_address', 'main_table.entity_id = sales_flat_order_address.parent_id', 'company');
		$collection->getSelect()->join('sales_flat_order_payment', 'main_table.entity_id = sales_flat_order_payment.parent_id', array('method' => new Zend_Db_Expr('UCASE(REPLACE(method, \'_\', \' \'))')));

        $collection->getSelect()->group('entity_id');
        //$collection->printlogquery(true);
    }

	//** EGGHEAD Added for rewriting grid
	protected function _prepareColumns()
    {
        parent::_prepareColumns();
        if($this->_isExport) return;
        
        unset($this->_columns['action']);
        unset($this->_columns['status']);
        unset($this->_columns['real_order_id']);
        unset($this->_columns['created_at']);
        unset($this->_columns['shipping_name']);
        unset($this->_columns['billing_name']);
        unset($this->_columns['base_grand_total']);
        unset($this->_columns['grand_total']);
        
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
            'filter_index' => 'main_table.increment_id'
        ));
 
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased from (store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
                'filter_index' => 'main_table.store_id'
            ));
        }
 
        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '150px',
            'filter_index' => 'main_table.created_at'
        ));
        
        $this->addColumn('company', array(
            'header' => Mage::helper('sales')->__('Company'),
            'index' => 'company',
            'width' => '250px',
            'filter_index' => 'sales_flat_order_address.company'
        ));
        
        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
            'width' => '250px',
            'filter_index' => 'main_table.billing_name'
        ));
        
        /*
$this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
            'width' => '250px',
            'filter_index' => 'main_table.shipping_name'
        ));
*/
  
/*
        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
            'filter_index' => 'main_table.base_grand_total'
        ));
*/
 
        $this->addColumnAfter('names', array(
            'header'    => Mage::helper('sales')->__('Products'),
            'index'     => 'names',
            'type'      => 'text',
            'width'		=> '450px',
            'filter_index' => '`sales/order_item`.name',
            'sortable'  => FALSE,
        ), 'shipping_name');
        
        $this->addColumnAfter('shipping_description', array(
            'header'    => Mage::helper('sales')->__('Shipping Method'),
            'index'     => 'shipping_description',
            'type'      => 'text',
            'filter_index' => '`sales/order`.shipping_description',
            'sortable'  => FALSE,
        ), 'grand_total');
        
        $this->addColumnAfter('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
            'filter_index' => 'main_table.grand_total'
            ), 'names');
            
        $this->addColumn('method', array(
            'header' => Mage::helper('sales')->__('Payment Method'),
            'index' => 'method',
            'width' => '100px',
            'filter_index' => 'sales_flat_order_payment.method'
        ));
		$this->addColumn('ship_date', array(
            'header' => Mage::helper('sales')->__('Ship Date'),
            'index' => 'ship_date',
            'width' => '100px',
            'filter_index' => '`sales/order`.gomage_deliverydate_formated'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'filter_index' => 'main_table.status',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
                
        $this->sortColumnsByOrder();
    }
    
	protected function _prepareMassaction()
	{
		parent::_prepareMassaction();
		
		$this->getMassactionBlock()->addItem('seperator1', array(
		     'label'=> Mage::helper('sales')->__('---------------'),
		     'url'  => '',
		));
		
	
		$this->getMassactionBlock()->addItem('pdfinvoice_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Invoice'),
		     'url'  => $this->getUrl('pickpack/sales_order/mooinvoice'),
			));
		
		$this->getMassactionBlock()->addItem('pdfpack_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Packing Sheet'),
		     'url'  => $this->getUrl('pickpack/sales_order/pack'),
			));
			
		$this->getMassactionBlock()->addItem('pdfinvoice_pdfpack_order', array(
		 'label'=> Mage::helper('sales')->__('PDF Invoice & Packing Sheet'),
		 'url'  => $this->getUrl('pickpack/sales_order/mooinvoicepack'),
		));		
		
		$this->getMassactionBlock()->addItem('pdfpick_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Order-separated Pick List'),
		     'url'  => $this->getUrl('pickpack/sales_order/pick'),
		));
		
		$this->getMassactionBlock()->addItem('pdfenpick_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Order-combined Pick List'),
		     'url'  => $this->getUrl('pickpack/sales_order/enpick'),
		));	
		
		$this->getMassactionBlock()->addItem('pdfstock_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Out-of-stock List'),
		     'url'  => $this->getUrl('pickpack/sales_order/stock'),
		));
		
		$this->getMassactionBlock()->addItem('pdflabel_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Address Labels'),
		     'url'  => $this->getUrl('pickpack/sales_order/label'),
		));
		
		$this->getMassactionBlock()->addItem('seperator2', array(
		     'label'=> Mage::helper('sales')->__('---------------'),
		     'url'  => '',
		));
		
		$this->getMassactionBlock()->addItem('csvpick_order', array(
		     'label'=> Mage::helper('sales')->__('CSV Order-separated Pick List'),
		     'url'  => $this->getUrl('pickpack/sales_order/pickcsv'),
			));
			
		//** Egghead Added
		
		
		$this->getMassactionBlock()->addItem('seperator3', array(
		     'label'=> Mage::helper('sales')->__('---------------'),
		     'url'  => '',
		));
		
/*
		$this->getMassactionBlock()->addItem('process_order', array(
		     'label'=> Mage::helper('sales')->__('Process Orders'),
		     'url'  => $this->getUrl('pickpack/sales_order/process'),
		));

		
		$this->getMassactionBlock()->addItem('seperator4', array(
		     'label'=> Mage::helper('sales')->__('---------------'),
		     'url'  => '',
		));
*/		
		$this->getMassactionBlock()->addItem('delete_order', array(
		     'label'=> Mage::helper('sales')->__('Delete Orders'),
		     'url'  => $this->getUrl('pickpack/sales_order/delete'),
		));
		
		$this->getMassactionBlock()->addItem('update_awaiting_payment', array(
		     'label'=> Mage::helper('sales')->__('Update Status To Awaiting Payment'),
		     'url'  => $this->getUrl('pickpack/sales_order/awaiting'),
		));
		
		$this->getMassactionBlock()->addItem('mark_complete', array(
		     'label'=> Mage::helper('sales')->__('Update Status To Complete'),
		     'url'  => $this->getUrl('pickpack/sales_order/complete'),
		));
/*
		
		$this->getMassactionBlock()->addItem('seperator5', array(
		     'label'=> Mage::helper('sales')->__('-------DEV ONLY--------'),
		     'url'  => '',
		));

		$this->getMassactionBlock()->addItem('get_edi_orders', array(
		     'label'=> Mage::helper('sales')->__('Get EDI Orders'),
		     'url'  => $this->getUrl('pickpack/sales_order/getedi'),
		));
		
		$this->getMassactionBlock()->addItem('get_ucc_labels', array(
		     'label'=> Mage::helper('sales')->__('Get UCC Labels'),
		     'url'  => $this->getUrl('pickpack/sales_order/getlabels'),
		));
		
		$this->getMassactionBlock()->addItem('create_asn', array(
		     'label'=> Mage::helper('sales')->__('Assign UCC Numbers'),
		     'url'  => $this->getUrl('pickpack/sales_order/createasn'),
		));
*/
		
/*
		$this->getMassactionBlock()->addItem('asn_edi_orders', array(
		     'label'=> Mage::helper('sales')->__('1 - Send ASN(s)'),
		     'url'  => $this->getUrl('pickpack/sales_order/ediasn'),
		));
*/
		$this->getMassactionBlock()->addItem('seperator6', array(
		     'label'=> Mage::helper('sales')->__('---------------'),
		     'url'  => '',
		));
		
		$this->getMassactionBlock()->addItem('change_shipping_method', array(
		     'label'=> Mage::helper('sales')->__('0 - Change Shipping Method'),
		     'url'  => $this->getUrl('pickpack/sales_order/changeship'),
		     'additional' => array(
				'visibility' => array(
					'name' => 'ship_method',
					'type' => 'text',
					'class' => 'required-entry',
					'label' => 'SCAC Code'
				)
			)
		));
		
		$this->getMassactionBlock()->addItem('add_bol_ship', array(
		     'label'=> Mage::helper('sales')->__('1 - Add BOL and Ship (Target Stores & TRU)'),
		     'url'  => $this->getUrl('pickpack/sales_order/bolship'),
		     'additional' => array(
				'ship_method' => array(
					'name' => 'ship_method',
					'type' => 'text',
					'class' => 'required-entry',
					'label' => 'SCAC Code'
				),
				'bol' => array(
					'name' => 'bol',
					'type' => 'text',
					'class' => 'required-entry',
					'label' => 'BOL#'
				),
				'load_id' => array(
					'name' => 'load_id',
					'type' => 'text',
					'label' => 'Load ID'
				)
			)
		));
		
		$this->getMassactionBlock()->addItem('labelonly_edi_orders', array(
		     'label'=> Mage::helper('sales')->__('2 - Create UCC Labels'),
		     'url'  => $this->getUrl('pickpack/sales_order/edilabel'),
		     'additional' => array(
				'visibility' => array(
					'name' => 'asn_date2',
					'type' => 'date',
					'class' => 'required-entry',
					'label' => 'ASN Date',
					'gmtoffset' => true,
		            'image'    => '/skin/adminhtml/default/default/images/grid-cal.gif',
		            'format'    => '%m/%d/%Y'
				)
			)
		));
		
		$this->getMassactionBlock()->addItem('asn_edi_orders', array(
		     'label'=> Mage::helper('sales')->__('3 - Send ASN(s)'),
		     'url'  => $this->getUrl('pickpack/sales_order/ediasn'),
		     'additional' => array(
				'visibility' => array(
					'name' => 'asn_date',
					'type' => 'date',
					'class' => 'required-entry',
					'label' => 'ASN Date',
					'gmtoffset' => true,
		            'image'    => '/skin/adminhtml/default/default/images/grid-cal.gif',
		            'format'    => '%m/%d/%Y'
				)
			)
		));
		
		$this->getMassactionBlock()->addItem('invoice_edi_orders', array(
		     'label'=> Mage::helper('sales')->__('4 - Send EDI Invoice(s)'),
		     'url'  => $this->getUrl('pickpack/sales_order/ediinvoice'),
		));
		
		
		//** END
		
		return $this;
	}
}


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
class Bluejalappeno_Orderexport_Block_Sales_Order_Grid extends Aitoc_Aitpermissions_Block_Rewrite_AdminSalesOrderGrid
{

}



/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminSalesOrderGrid.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ cooZCirCUrmokeDE('4a1e2dfeb0d57b0a72eaee3b169127a2'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Moogento_Pickpack_Block_Sales_Order_Grid extends Bluejalappeno_Orderexport_Block_Sales_Order_Grid
{
	protected function _prepareColumns()
	{
		parent::_prepareColumns();
		
		if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
		{
			$AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
    		if (count($AllowedStoreviews) <=1 && isset($this->_columns['store_id']))
    		{
    		    unset($this->_columns['store_id']);
    		}
		}
		return $this;
	}
} }

