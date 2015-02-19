<?php
/**
 * Customervalue.php
 * Custom renderer for grid to display customer lifetime value
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */
class Tegdesign_Emailcollector_Block_Adminhtml_Emails_Renderer_Customervalue extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
	
		$db_conn = Mage::getSingleton('core/resource');
		$r_conn = $db_conn->getConnection('core_read');

		$customer_value = 0;
		$email =  $row->getData('email');

		$tbl = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		$sql = 'SELECT SUM(subtotal_invoiced) AS clv FROM ' . $tbl . ' WHERE customer_email = "' . $email . '" GROUP BY customer_email';
		$rsp = $r_conn->fetchAll($sql);

		if (!empty($rsp)) {
			$customer_value = $rsp[0]['clv'];
		}

		$customer_value = Mage::helper('core')->currency($customer_value, true, false);

		return $customer_value;
	
	}
	
}