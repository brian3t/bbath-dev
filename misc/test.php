<?php 
define('MAGENTO', realpath(dirname(__FILE__)));
require_once '../app/Mage.php';

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$count = 0;

$db = Mage::getSingleton('core/resource')->getConnection('core_read');
	
	$header = array(
		'PO Number',
		'PO Date',
		'Ship To Code',
		'Ship To Address',
		'Ship To City',
		'Ship To State',
		'Ship To ZipCode',
		'Ordered Quantity',
		'Unit of Measure',
		'Vendor Item',
		'Buyer Item',
		'Buyer Item Number',
		'Color Description'
	);
	
    $fp = fopen('summary.csv','w');
    fputcsv($fp,$header, ',', '"');
    
    $stmt = $db->query('SELECT `PO Number`,`PO Date`,`Ship To Code`,`Ship To Address 1`,`Ship To City`,`Ship To State`,`Ship To ZipCode`,`Ordered Quantity`,`Unit of Measurement`,`Vendor Item Number`,`Buyer Item Qualifier`,`Buyer Item Number`,`Color Description` FROM bulk_po_import');
    $rows = $stmt->fetchAll();
    //print_r($rows);
    
    $qty = 0;
    $cartons = 0;
    $pallets = 0;
    
    foreach($rows as $row) {
	    $qty = $qty + $row['Ordered Quantity'];
	    $string = array_values($row);
	    fputcsv($fp,$string, ',', '"');
    }
    
    $cartons = $qty/4;
    $pallets = ceil($cartons/96);
    fputcsv($fp, array(''), ',', '"');
	fputcsv($fp, array('','','','','','','',$qty,'',$cartons,$pallets), ',', '"');
	fputcsv($fp, array('','','','','','','','','','Cartons','Pallets'), ',', '"');

	fclose($fp);
	
	$config = array('ssl' => 'tls', 'port' => 587, 'auth' => 'login', 'username' => 'support@bloomingbath.com', 'password' => '@BB11111');
	$smtpConnection = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $config);
	
    $mail = new Zend_Mail();
    $mail->setFrom("support@bloomingbath.com","Blooming Bath");
    $mail->addTo("support@bloomingbath.com","support@bloomingbath.com");
    $mail->setSubject("Order Summary " . date("Y-m-d H:i:s"));
    $mail->setBodyHtml(""); // here u also use setBodyText options.

    // this is for to set the file format
    $at = new Zend_Mime_Part(file_get_contents('summary.csv'));

    $at->type        = 'application/csv'; // if u have PDF then it would like -> 'application/pdf'
    $at->disposition = Zend_Mime::DISPOSITION_INLINE;
    $at->encoding    = Zend_Mime::ENCODING_8BIT;
    $at->filename    = 'file.csv';
    $mail->addAttachment($at);
    $mail->send($smtpConnection);    
 ?>