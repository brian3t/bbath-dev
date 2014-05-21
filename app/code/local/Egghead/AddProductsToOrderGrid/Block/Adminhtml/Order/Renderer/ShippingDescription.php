<?php
class Egghead_AddProductsToOrderGrid_Block_Adminhtml_Order_Renderer_ShippingDescription extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{
		$orderId = $row->getData($this->getColumn()->getIndex());
        $order = Mage::getModel('sales/order')->load($orderId);
		
		return $order->getShippingDescription();

	}

}
?>