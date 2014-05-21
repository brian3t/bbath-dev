<?php
class Egghead_AddProductsToOrderGrid_Block_Adminhtml_Order_Renderer_ProductNames extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{
		$orderId = $row->getData($this->getColumn()->getIndex());
        $order = Mage::getModel('sales/order')->load($orderId);
		
		foreach ($order->getItemsCollection() as $item) {
        	echo '(' . number_format($item->getQtyOrdered()) . ') ' . $item->getName() . '<br>';
		}

	}

}
?>