<?php
/**
 * Cookie.php
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */
class Tegdesign_Emailcollector_Model_System_Config_Source_Cookie extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {

		$options = array();

		$options[0]['value'] = '7';
		$options[0]['label'] = '7 days';

		$options[1]['value'] = '14';
		$options[1]['label'] = '14 days';

		$options[2]['value'] = '30';
		$options[2]['label'] = '30 days';

		$options[3]['value'] = '60';
		$options[3]['label'] = '60 days';

		$options[4]['value'] = '100';
		$options[4]['label'] = '100 days';

		$options[5]['value'] = '365';
		$options[5]['label'] = '365 days';

    	return $options;

    }

}