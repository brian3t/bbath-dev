<?php
if (isset($_GET['product_id']))
{
	$product_id = $_GET['product_id'];
}
else{
	echo 'Need product_id input';
	die();
}
require_once ("../app/Mage.php");
$app = Mage::app('default');
header('Content-Type: application/json');

$model = Mage::getModel('catalog/product'); 
$_product = $model->load($product_id); 
$stocklevel = (int)Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($_product)->getIsInStock();
if ($stocklevel) {
	echo 1;               
} else {
	echo 0;
}