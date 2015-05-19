<?php
/**
 * Labels.php
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */
class Tegdesign_Emailcollector_Model_System_Config_Source_Labels extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {

		$options = array();

		$options[0]['value'] = 'placeholder';
		$options[0]['label'] = 'Use HTML5 Placeholder Text (Modern Browsers)';

		$options[1]['value'] = 'label';
		$options[1]['label'] = 'Use Regular Label above the Input Field';

		$options[2]['value'] = 'both';
		$options[2]['label'] = 'Use Both Placeholder and Label above Input Field';

    	return $options;

    }

}