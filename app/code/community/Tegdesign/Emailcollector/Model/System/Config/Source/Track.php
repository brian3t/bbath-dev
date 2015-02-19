<?php
/**
 * Track.php
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */
class Tegdesign_Emailcollector_Model_System_Config_Source_Track extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {

		$options = array();

		$options[0]['value'] = 'dont_track';
		$options[0]['label'] = 'Dont track non-conversions';

		$options[1]['value'] = 'google_analytics_analyticsjs';
		$options[1]['label'] = 'Google Analytics new tracking code (analytics.js)';

		$options[2]['value'] = 'google_analytics_gajs';
		$options[2]['label'] = 'Google Analytics Pre-April 2013 tracking code (ga.js)';

		$options[3]['value'] = 'mixpanel';
		$options[3]['label'] = 'Mixpanel';

    	return $options;

    }

}