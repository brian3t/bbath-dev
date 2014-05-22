<?php
echo "test";
require_once '../app/Mage.php';

Mage::app();

$quote = Mage::getModel('sales/quote')->setStoreId(2);

if ('do customer orders') {
	// for customer orders:
		$customer = Mage::getModel('customer/customer')->setWebsiteId(2)->setStoreId(2)->loadByEmail('supportbbb@bloomingbath.com');
	$quote->assignCustomer($customer);
} else {
	// for guesr orders only:
	$quote->setCustomerEmail('supportbbb@bloomingbath.com');
}

// add product(s)
$product = Mage::getModel('catalog/product')->load(8);
$buyInfo = array(
	'qty' => 1,
	// custom option id => value id
	// or
	// configurable attribute id => value id
);
$quote->addProduct($product, new Varien_Object($buyInfo));

$addressData = array(
	'firstname' => 'Test',
	'lastname' => 'Test',
	'street' => 'Sample Street 10',
	'city' => 'Somewhere',
	'postcode' => '123456',
	'telephone' => '123456',
	'country_id' => 'US',
	'region_id' => 12, // id from directory_country_region table
);

$billingAddress = $quote->getBillingAddress()->addData($addressData);
$shippingAddress = $quote->getShippingAddress()->addData($addressData);

$shippingAddress->setFreeShipping(true)
		        ->setCollectShippingRates(true)->collectShippingRates()
		        ->setShippingMethod('freeshipping_freeshipping')
		        ->setPaymentMethod('purchaseorder');

$quote->getPayment()->importData(array('method' => 'purchaseorder', 'po_number' => $poNumber));

$quote->collectTotals()->save();
var_dump($quote);
$service = Mage::getModel('sales/service_quote', $quote);
$service->submitAll();
$order = $service->getOrder();


printf("Created order %s\n", $order->getIncrementId());