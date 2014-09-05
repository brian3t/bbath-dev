<?php
class Medma_Ship_IndexController extends Mage_Core_Controller_Front_Action
{
    public function shippingAction()
	{
		if(isset($_POST["shipping_method"])) {
			$orderId     		= $this->getRequest()->getParam('order_id');
			$customShipTitle	= 'Shipping - ' . $_POST["shipping_method"];
			$order = Mage::getModel('sales/order')->load($orderId);//print_r($order);
			#$setShipment = $order->setShippingMethod('excellence_excellence')->setShippingDescription($customShipTitle)->save();
			$setShipment = $order->setShippingDescription($customShipTitle)->save();
		}
		
		$this->_redirectReferer('');
	}
}
