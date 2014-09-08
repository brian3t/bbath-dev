<?
class Egghead_AddProductsToOrderGrid_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{

    protected function _prepareColumns()
    {
 		
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
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
 
        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
            'width' => '250px',
        ));
 
        $this->addColumn('products', array(
            'header'    => Mage::helper('catalog')->__('Products'),
            'index'     => 'entity_id',
            'type' => 'text',
            'renderer' => new Egghead_AddProductsToOrderGrid_Block_Adminhtml_Order_Renderer_ProductNames(),
        ));
        
        $this->addColumn('shipping_description', array(
            'header'    => Mage::helper('sales')->__('Shipping'),
            'index'     => 'entity_id',
            'type' => 'text',
            'renderer' => new Egghead_AddProductsToOrderGrid_Block_Adminhtml_Order_Renderer_ShippingDescription(),
        ));
 
        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));
 
        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));
 
        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
 
        return $this;
    }
 
}