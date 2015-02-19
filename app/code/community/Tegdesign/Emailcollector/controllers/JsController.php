<?php
/**
 * JsController.php
 * Controller responsible for outputing modal Javscript
 * Used when using the Email Popup Collector v2 and above
 * Used in Magento 1.9x and greater and EE 1.14x and greater for RWD
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */

class Tegdesign_Emailcollector_JsController extends Mage_Core_Controller_Front_Action {

	public function modalAction() {

		$epc = Mage::helper('tegdesign_emailcollector');

		$js = '';

		$js = file_get_contents($epc->getBaseRelativePath() . 'js/tegdesign/emailcollector/jquery.remodal.js');

// Email Popup Collector additions

		$js .= "if (typeof jQuery == 'undefined') {\n";
		$js .= "	//console.log(jquery not loaded);\n";

		if ($epc->getDebugEnabled()) {
			$js .= "alert('jQuery library not found. Popup collection needs it to function.');\n";
		}

		$js .= "} else {\n"; 

		$js .= "	jQuery(function() {\n"; 

		$js .= "		var show_email_collector = true;\n";

		$js .= "		var navPath = (window.location.pathname.substr(-1) === '/') ? window.location.pathname : window.location.pathname + '/';\n"; 

		// always show the email collector when debug mode is on

		if ($epc->getDebugEnabled()) {

		if (!$epc->getIsManualPopupModeEnabled()) {
			$js .= "		showEmailCollector();";
		}

		} else {

		$js .= "		if (show_email_collector) {";
		$js .= "			checkEmailCollectorCookie();\n";
		$js .= "		}";

		}

		$js .= "	});\n"; // end jQuery dom ready
		$js .= "}\n"; // endif jQuery check existance


		$js .= "function showEmailCollector() {\n";
		// show the poup
		$js .= "	jQuery('.epc-modal-container').show();\n";
		$js .= "	var inst = jQuery('[data-remodal-id=email_popup_collector]').remodal();\n"; 
		$js .= "  inst.open();\n";

		$js .= "	jQuery('#email_collector_form').on('submit', function() {\n"; 

		$js .= " if (!epcValidateEmail()) { return false; }";

		$js .= "	});\n";

		// show the close button if enabled
		if (!$epc->getShowCloseXEnabled()) {
		$js .= "	jQuery('.remodal-close').hide();\n"; 
		}

		$js .= "	}\n"; 
		
		// end showEmailCollector JS



		$js .= "function checkEmailCollectorCookie() {\n";

		// has the cookie already been set, if so dont show the popup, just return
		$js .= "	if (document.cookie.indexOf('email_collector') >= 0) {\n";
		
		$js .= "		//console.log('cookie found');\n";

		$js .= "	} else {\n";

		// set a cookie
		$js .= "		var expTime = 1000*60*60*24*" . $epc->getCookieExpiration() . ";\n";
		$js .= "		var expires = new Date((new Date()).valueOf() + expTime);\n";

		$js .= "		var parts = location.hostname.split('.');\n";

		$js .= "		if (parts.length - 1 == 1) {\n"; 
		$js .= "			var subdomain = parts.shift();\n"; 
		$js .= "			var upperleveldomain = parts.join('.');\n"; 
		$js .= "			var cookie_domain = subdomain + '.' + upperleveldomain;\n"; 
		$js .= "		} else {\n"; 
		$js .= "			var cookie_domain = parts.join('.');\n"; 
		$js .= "		}\n";

		// todo upperlevel domain issue?
		//$js .= "		document.cookie = \"email_collector=true;expires=\" + expires.toUTCString() + \";max-age=\" + expTime + \"; path=/; domain=.\" + cookie_domain;\n";

		$js .= "        document.cookie = \"email_collector=true;expires=\" + expires.toUTCString() + \";max-age=\" + expTime + \"; path=/\";\n";

		if (!$epc->getIsManualPopupModeEnabled()) {
			$js .= "		showEmailCollector();\n";
		}

		$js .= "	}\n"; 

		$js .= "}\n"; // end checkEmailCollectorCookie JS


		$js .= "function email_collector_track(track) {\n";

		$js .= "} \n"; // end email_collector_track JS

		// ouput response as js
		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'application/javascript')
			->setBody($js);

	}

}