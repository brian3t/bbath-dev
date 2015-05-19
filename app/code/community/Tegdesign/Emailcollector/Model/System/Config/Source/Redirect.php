<?php
/**
 * Redirect.php
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */
class Tegdesign_Emailcollector_Model_System_Config_Source_Redirect extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {

		$options = array();

		$options[0]['value'] = 'redirect_url';
		$options[0]['label'] = 'Redirect to URL';

		$options[1]['value'] = 'redirect_same_page';
		$options[1]['label'] = 'Redirect to same page';

    	return $options;

    }

}