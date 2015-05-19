<?php
require_once("../app/Mage.php");
umask(0);
ini_set('display_errors',true); Mage::setIsDeveloperMode(true);
Mage::app();

$orders = Mage::getModel('sales/order')->getCollection()
			->addFieldToFilter('store_id', 1)
			->addFieldToFilter('status', 'processing')
			->addFieldToFilter('shipping_method', array(
								array('like' => '%Standard_Shipping%'),
								array('like' => '%freeshipping_freeshipping%'),
								array('like' => '%adminshipping_adminshipping%')
							   ));
//$orders->load(true);

$output = getShippingEstimate('200034325');
echo $output;
exit;

foreach ($orders as $order) {
   	$orderIncrementId = $order->getIncrementId();
    $output = getShippingEstimate($orderIncrementId);
    echo $output;
    unset($output);
}

function getShippingEstimate($orderIncrementId) {
	
	$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
	$weight = $order->getWeight();
	
	$order_quote_id = $order->getData('quote_id');
	
    //$quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore('international')->getId());
    $quote = Mage::getModel('sales/quote')->load($order_quote_id)->setStoreId(Mage::app()->getStore('international')->getId());

/*
    $_product = Mage::getModel('catalog/product')->load($productId);

    $_product->getStockItem()->setUseConfigManageStock(false);
    $_product->getStockItem()->setManageStock(false);

    $quote->addProduct($_product, $productQty);
    $quote->getShippingAddress()->setCountryId($country_id)->setPostcode($postcode); 
    $quote->getShippingAddress()->collectTotals();
    $quote->getShippingAddress()->setCollectShippingRates(true);
    $quote->getShippingAddress()->collectShippingRates();
*/
	$quote->getShippingAddress()->collectTotals();
    $quote->getShippingAddress()->setCollectShippingRates(true);
    $quote->getShippingAddress()->collectShippingRates();
    $_rates = $quote->getShippingAddress()->getShippingRatesCollection();

    $shippingRates = array();
    foreach ($_rates as $_rate):
            if($_rate->getPrice() > 0) {
                $shippingRates[] =  array('Title' => $_rate->getMethodTitle(), 'Price' => $_rate->getPrice());
                if($_rate->getMethodTitle() == 'Smart Post') {
	                $smartpost = $_rate->getPrice();
                }
                if(strpos($_rate->getMethodTitle(),'Priority Mail') !== false) {
	                
	                $usps_values = array('11.55','10.55','9.75','9.00','7.05','6.35','5.95','6.70','7.70','8.85','10.80','12.20','13.10','15.50','7.55','9.00','10.15','14.25','15.65','16.75','18.65','21.80');
	                $stamps_com_values = array('8.99','8.29','7.68','7.17','5.74','5.35','5.09','5.20','5.40','5.90','8.65','10.18','11.15','12.53','6.16','7.25','8.10','10.66','13.37','14.42','16.28','19.53');
	                $move_method_rebate = .25;
	                if(in_array($smartpost = $_rate->getPrice(), $usps_values)) {
		                $priority_mail = str_replace($usps_values, $stamps_com_values, $_rate->getPrice());
	                } else {
		            	$priority_mail = $_rate->getPrice() - ($_rate->getPrice()*.192);
	                }
	                $priority_mail = floatval($priority_mail - $move_method_rebate);
                }
            }
    endforeach;
    var_dump($shippingRates);
	exit;
    
    if($priority_mail < $smartpost) {
	    $order->setShippingMethod('premiumrate_USPS_Priority_(3_Day)');
		$order->setShippingDescription('USPS Priority (3 Day)')->save();
		$preferred_method = 'USPS';
    } else {
	    $order->setShippingMethod('adminshipping_adminshipping');
		$order->setShippingDescription('FedEx SmartPost')->save();
		$preferred_method = 'SmartPost';
    }
	
	//echo $order->getWeight();
    //echo 'Smartpost' . $smartpost . '<br>';
    //echo 'Priority' . $priority_mail . '<br>';
    //echo 'Difference' . ($smartpost - $priority_mail);
    
    $result =  'Order Number: ' . $orderIncrementId . '<br>'; 
    $result .= 'Smartpost: ' . $smartpost . '<br>';
    $result .= 'Priority: ' . $priority_mail . '<br>';
    $result .= 'Difference: ' . ($smartpost - $priority_mail) . '<br>';
    $result .= 'Shipping set to: ' . $preferred_method . '<br>';
    $result .= 'Calculated Weight: ' . $weight . '<br>';
    
    return $result;

}

class stamps_com
{
	private $Authenticator;

	//API LOGIN
	private $IntegrationID = "1bdeb8ca-8af7-4352-9ab5-829aa5ae8fb6";
	private $Username = "EggheadLLC-01";
	private $Password = "postage1";

	private $wsdl = "https://swsim.testing.stamps.com/swsim/swsimv45.asmx?wsdl";

	public $client;

	public $output;
	//public $functions;
	//public $types;


	public $ServiceType = array(

		"US-FC" =>  "USPS First-Class Mail",
		"US-MM" =>  "USPS Media Mail",
		"US-PP" =>  "USPS Parcel Post ",
		"US-PM" =>  "USPS Priority Mail",
		"US-XM" =>  "USPS Priority Mail Express",
		"US-EMI" =>  "USPS Priority Mail Express International",
		"US-PMI" =>  "USPS Priority Mail International",
		"US-FCI" =>  "USPS First Class Mail International",
		"US-CM" =>  "USPS Critical Mail",
		"US-PS" =>  "USPS Parcel Select",
		"US-LM" =>  "USPS Library Mail"
	);
	
	function __construct()
	{
		$this->connect();
		$this->GetRates("92009","92011",null,"1",20,4,5,"Package","2015-05-20",'0',null);
		
		//echo "<pre>";
		//print_r($this);

	}

	function connect(){

		$authData = array(
		    "Credentials"       => array(
		        "IntegrationID"     => $this->IntegrationID,
		        "Username"          => $this->Username,
		        "Password"          => $this->Password
		));

		$this->client = new SoapClient('https://swsim.testing.stamps.com/swsim/swsimv38.asmx?wsdl');
		$auth = $this->client->AuthenticateUser($authData);
		$this->Authenticator = $auth->Authenticator;

		//$this->functions = $this->client->__getFunctions();
		//$this->types = $this->client->__getTypes();

	}

	function GetRates($FromZIPCode,$ToZIPCode = null,$ToCountry = null,$WeightLb,$Length,$Width,$Height,$PackageType,$ShipDate,$InsuredValue,$ToState = null){

		$data = array(
		    
		        "Authenticator"     => $this->Authenticator,
		        "Rate" => array(
		        	"FromZIPCode" => $FromZIPCode,
					"WeightLb" => $WeightLb,
					"Length" => $Length,
					"Width" => $Width,
					"Height" => $Height,
					"PackageType" => $PackageType,
					"ShipDate" => $ShipDate,
					"InsuredValue" => $InsuredValue

		       	)
			
		);

		if($ToZIPCode == null && $ToCountry != null){
			$data["Rate"]['ToCountry'] = $ToCountry;
		}else{
			$data["Rate"]['ToZIPCode'] = $ToZIPCode;
		}

		if($ToState != null){
			$data["Rate"]['ToState'] = $ToState;
		}

		$r = $this->client->GetRates($data);
		$r = $r->Rates->Rate;

		echo "<pre>";

		foreach ($r as $k => $v) {
			
			foreach ($data['Rate'] as $kk => $vv) {
				$result[$k][$kk] = $v->$kk;
			}

			 $result[$k] =  $result[$k] + array(
			 	"ServiceType" => $this->ServiceType[$v->ServiceType],
			 	"Amount" => $v->Amount,
			 	"PackageType" => $v->PackageType,
			 	"WeightLb" => $v->WeightLb,
			 	"Length" => $v->Length,
			 	"Width" => $v->Width,
			 	"Height" => $v->Height,
			 	"ShipDate" => $v->ShipDate,
			 	"DeliveryDate" => $v->DeliveryDate,
			 	"RateCategory" => $v->RateCategory,
			 	"ToState" => $v->ToState,
			);
		}

		print_r($result);
	
	}
}

$stamps_com = new stamps_com;
