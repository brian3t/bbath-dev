<?php
$mageFilename = '../app/Mage.php';

require_once $mageFilename;

Varien_Profiler::enable();

Mage::setIsDeveloperMode(true);

ini_set('display_errors', 1);

umask(0);
Mage::app('default');
Mage::register('isSecureArea', 1);
function generateUniqueId($length = null){
	$rndId = crypt(uniqid(rand(),1));
	$rndId = strip_tags(stripslashes($rndId));
	$rndId = str_replace(array(".", "$"),"",$rndId);
	$rndId = strrev(str_replace("/","",$rndId));
	if (!is_null($rndId)){
		return strtoupper(substr($rndId, 0, $length));
	}
	return strtoupper($rndId);
}
function getAllCustomerGroups(){
	//get all customer groups
	$customerGroups = Mage::getModel('customer/group')->getCollection();
	$groups = array();
	foreach ($customerGroups as $group){
		$groups[] = $group->getId();
	}
	return $groups;
}
function getAllWbsites(){
	//get all wabsites
	$websites = Mage::getModel('core/website')->getCollection();
	$websiteIds = array();
	foreach ($websites as $website){
		$websiteIds[] = $website->getId();
	}
	return $websiteIds;
}
//read comments for each line
function generateRule(){
	$uniqueId = generateUniqueId(10);
	$rule = Mage::getModel('salesrule/rule');
	$rule->setName('Health Deals');
	$rule->setDescription('Health Deals');
	$rule->setFromDate(date('Y-m-d'));//starting today
	//$rule->setToDate('2012-01-14');//if you need an expiration date
	$rule->setCouponCode($uniqueId);
	$rule->setUsesPerCoupon(1);//number of allowed uses for this coupon
	$rule->setUsesPerCustomer(1);//number of allowed uses for this coupon for each customer
	$rule->setCustomerGroupIds(getAllCustomerGroups());//if you want only certain groups replace getAllCustomerGroups() with an array of desired ids
	$rule->setIsActive(1);
	$rule->setStopRulesProcessing(1);//set to 1 if you want all other rules after this to not be processed
	$rule->setIsRss(0);//set to 1 if you want this rule to be public in rss
	$rule->setIsAdvanced(1);//have no idea what it means :)
	//$rule->setProductIds(355);
	$rule->setSortOrder(0);// order in which the rules will be applied

	$rule->setSimpleAction('by_percent');
	//all available discount types
	//by_percent - Percent of product price discount
	//by_fixed - Fixed amount discount
	//cart_fixed - Fixed amount discount for whole cart
	//buy_x_get_y - Buy X get Y free (discount amount is Y)

	$rule->setDiscountAmount('100');//the discount amount/percent. if SimpleAction is by_percent this value must be <= 100
	$rule->setDiscountQty(1);//Maximum Qty Discount is Applied to
	$rule->setDiscountStep(0);//used for buy_x_get_y; This is X
	$rule->setSimpleFreeShipping(2);//set to 1 for Free shipping for only a specific item or 2 for a shipment
	$rule->setApplyToShipping(0);//set to 0 if you don't want the rule to be applied to shipping
	$rule->setWebsiteIds(getAllWbsites());//if you want only certain websites replace getAllWbsites() with an array of desired ids

	$labels = array();
    $labels[0] = 'Health Deals';
    $rule->setStoreLabels($labels);

	$conditions = array();
	$conditions[1] = array(
		'type' => 'salesrule/rule_condition_product_combine',
		'aggregator' => 'all',
		'value' => 1,
		'new_child' => ''
	);
	//the conditions above are for 'if all of these conditions are true'
	//for if any one of the conditions is true set 'aggregator' to 'any'
	//for if all of the conditions are false set 'value' to 0.
	//for if any one of the conditions is false set 'aggregator' to 'any' and 'value' to 0
	$conditions['1--1'] = array
	(
		'type' => 'salesrule/rule_condition_product',
		'attribute' => 'sku',
		'operator' => '==',
		'value' => 'BLOOMINGBATH'
	);
	//the constraints above are for 'Subtotal is equal or grater than 200'
	//for 'equal or less than' set 'operator' to '<='... You get the idea other operators for numbers: '==', '!=', '>', '<'
	//for 'is one of' set operator to '()';
	//for 'is not one of' set operator to '!()';
	//in this example the constraint is on the subtotal
	//for other attributes you can change the value for 'attribute' to: 'total_qty', 'weight', 'payment_method', 'shipping_method', 'postcode', 'region', 'region_id', 'country_id'

	//to add an other constraint on product attributes (not cart attributes like above) uncomment and change the following:
	/*
$conditions['1--2'] = array
(
'type' => 'salesrule/rule_condition_product_found',//-> means 'if all of the following are true' - same rules as above for 'aggregator' and 'value'
//other values for type: 'salesrule/rule_condition_product_subselect' 'salesrule/rule_condition_combine'
'value' => 1,
'aggregator' => 'all',
'new_child' => '',
);

$conditions['1--2--1'] = array
(
'type' => 'salesrule/rule_condition_product',
'attribute' => 'sku',
'operator' => '==',
'value' => '12',
);
*/
	//$conditions['1--2--1'] means sku equals 12. For other constraints change 'attribute', 'operator'(see list above), 'value'

	$rule->setData('actions',$conditions);
	$rule->loadPost($rule->getData());
	$rule->setCouponType(2);
	//add one line for each store view you have. The key is the store view ID
	$rule->save();
}

for ($i=1;$i<=499;$i++){//replace 200 with the number of coupons you want
	generateRule();
}
?>