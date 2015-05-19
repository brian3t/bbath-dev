<?php
class Moogento_Pickpack_Model_Statements
{

	function createStatement() {
		
		$accounts = array(3994,1115,1849,850);
		
		foreach($accounts as $account) {
			
			$db = Mage::getSingleton('core/resource')->getConnection('core_read');
			$header = array(
				'Invoice Number',
				'PO Number',
				'Order Date',
				'Order Total'
			);
			if($account == 850) {
				$name = 'TRU';
				$dating = 60;
			} elseif($account == 1115) {
				$name = 'BBB';
				$dating = 30;
			} elseif($account == 1849) {
				$name = 'Target.com';
				$dating = 60;
			} elseif($account == 3994) {
				$name = 'Target';
				$dating = 60;
			}
			
			$file_name = Mage::getBaseDir() . '/misc/statements/' .$name . '_Statement_' . date("Y-m-d") . '.csv';
			$fp = fopen($file_name,'w');
			fputcsv($fp,$header, ',', '"');
			
			$stmt = $db->query("SELECT increment_id, po_number, DATE_FORMAT(created_at,'%m-%d-%Y') AS created_at, grand_total FROM sales_flat_order LEFT JOIN sales_flat_order_payment ON sales_flat_order_payment.parent_id = sales_flat_order.entity_id WHERE sales_flat_order.customer_id = " . $account . " AND STATUS = 'payment_expected' AND created_at < DATE_SUB(CURDATE(), INTERVAL " . $dating . " DAY) order by created_at");
			$rows = $stmt->fetchAll();
			//print_r($rows);
			
			$total = 0;
			
			foreach($rows as $row) {
			    $total = $total + $row['grand_total'];
			    $string = array_values($row);
			    fputcsv($fp,$string, ',', '"');
			}
			
			fputcsv($fp, array('','','Total', $total));
			
			fclose($fp);
			
			$config = array('ssl' => 'tls', 'port' => 587, 'auth' => 'login', 'username' => 'support@bloomingbath.com', 'password' => '@BB11111');
			$smtpConnection = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $config);
			
			$mail = new Zend_Mail();
			$mail->setFrom("support@bloomingbath.com","Blooming Bath");
			$mail->addTo("support@bloomingbath.com","support@bloomingbath.com");
			$mail->addCc("theresa@eggheadventures.com","theresa@eggheadventures.com");
			$mail->setSubject($name . " Statement " . date("Y-m-d") . " Past " . $dating . " Days");
			$mail->setBodyHtml(""); // here u also use setBodyText options.
			
			// this is for to set the file format
			$at = new Zend_Mime_Part(file_get_contents($file_name));
			
			$at->type        = 'application/csv'; // if u have PDF then it would like -> 'application/pdf'
			$at->disposition = Zend_Mime::DISPOSITION_INLINE;
			$at->encoding    = Zend_Mime::ENCODING_8BIT;
			$at->filename    = $file_name;
			$mail->addAttachment($at);
			$mail->send($smtpConnection);    
		
		}
	}
}