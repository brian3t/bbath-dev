<?php
class Moogento_Pickpack_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
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
		
		$this->getMassactionBlock()->addItem('process_order', array(
		     'label'=> Mage::helper('sales')->__('Process Orders'),
		     'url'  => $this->getUrl('pickpack/sales_order/process'),
		));
		
		$this->getMassactionBlock()->addItem('seperator4', array(
		     'label'=> Mage::helper('sales')->__('---------------'),
		     'url'  => '',
		));
		
		$this->getMassactionBlock()->addItem('delete_order', array(
		     'label'=> Mage::helper('sales')->__('Delete Orders'),
		     'url'  => $this->getUrl('pickpack/sales_order/delete'),
		));
		
		$this->getMassactionBlock()->addItem('update_awaiting_payment', array(
		     'label'=> Mage::helper('sales')->__('Update Status To Awaiting Payment'),
		     'url'  => $this->getUrl('pickpack/sales_order/awaiting'),
		));
		//** END
		
		return $this;
	}
}
