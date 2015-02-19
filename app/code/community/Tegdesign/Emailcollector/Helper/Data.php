<?php
/**
 * Data.php
 * Generic helper functions
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */
class Tegdesign_Emailcollector_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function getStoreId() {
		$store_id = Mage::app()->getStore()->getStoreId();
		return $store_id;
	}

	public function getModuleEnabled()
	{
		return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/promosettings/module_enabled');
	}

	public function getDebugEnabled()
    {
        return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/promosettings/debug_mode');
    }

    public function getForceRegEnabled()
    {
        return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/regopts/forcereg');
    }

    public function getCaptureNameEnabled()
    {
        return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/regopts/capture_name');
    }


    public function getCSSFilename()
    {
/*
    	if ($this->getIsjQueryEnabled()) {
    		return 'css/tegdesign_emailcollector/jquery.css';
    	} else {
    		return 'css/tegdesign_emailcollector/prototype.css';
    	}
*/
		return 'css/tegdesign_emailcollector/jquery.remodal.css';
    }

    public function getShowCloseXEnabled()
    {
        return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/promosettings/show_close_x');
    }

	public function getPopupAdditionalPaths() 
	{
		return Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/additional_paths');
	}

	public function getLabelType() {
		return Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/labels');
	}

    public function getUseCouponEnabled() {
        return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/promosettings/use_coupon');	
    }

    public function getMagentoEmailEnabled() {
        return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/settings/magento_email');	
    }

    public function getMagentoEmailTemplate() {
        return Mage::getStoreConfig('tegdesign_emailcollector_options/settings/emailcollector_template');
    }

    public function getMailChimpEnabled() {
        return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/settings/mailchimp_enabled');	
    }

    public function getMailChimpAutoresponderEnabled() {
        return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/settings/mailchimp_autoresponder_enabled');	
    }

    public function getMailchimpSendCouponEnabled() {
    	return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/settings/mailchimp_send_coupon_enabled');
    }

    public function getMailchimpMergeField() {
    	return Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_coupon_merge_field');
    }

    public function getMailchimpAutoresponderfield() {
    	return Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_autoresponderfield');
    }

    public function getMailChimpAPIKey() {
        return Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_apikey');
    }

    public function getMailChimpListId() {
        return Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_listid');
    }

    public function getPromoCoupon() {
    	return Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/promocoupon');
    }

    public function getFieldLabelType()
    {
    	return Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/labels');

    }
    public function getAddToNewsletterEnabled()
    {
        return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/regopts/addtonewsletter');
    }

    public function getIsManualPopupModeEnabled()
    {
        return Mage::getStoreConfigFlag('tegdesign_emailcollector_options/advancedsettings/manual_popup_mode_enabled');
    }

    public function getFieldLabels()
    {

    	$field_labels = array();

    	$field_labels['label_type'] = 'label';
    	$field_labels['placeholder'] = false;
    	$field_labels['email_placeholder'] = '';

		$field_labels['label_type'] = $this->getFieldLabelType();

		if ($field_labels['label_type'] == 'label') {

			$field_labels['epc_label'] = 'block';

		} elseif ($field_labels['label_type'] == 'placeholder') {

			$field_labels['epc_label'] = 'none';
			$field_labels['placeholder'] = true;
			$field_labels['email_placeholder'] = 'placeholder="' . $this->__('E-mail Address') . '"';

		} elseif ($field_labels['label_type'] == 'both') {

			$field_labels['epc_label'] = 'block';
			$field_labels['placeholder'] = true;
			$field_labels['email_placeholder'] = 'placeholder="' . $this->__('E-mail Address') . '"';

		}

		return $field_labels;

    }

    public function getCookieExpiration()
    {
		if (Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/cookie_expires')) {
			$cookie_expires = (int)Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/cookie_expires');
		} else {
			$cookie_expires = 365;
		}
		return $cookie_expires;
	}
	
	/* 
		NOTE: if you installed magento in a different directory and are using the popup
		collector on just the homepage you may need to alter this to be something different
	*/
	public function getPostUrl($action='join') {
		$post_url = Mage::getUrl('',array('_secure'=>true)) . 'emailcollector/go/' . $action;
		
		return $post_url;
	}

	public function getBaseRelativePath() {
		return Mage::getUrl('',array('_secure'=>true));
	}
	
	public function getHomepageUrl() {
		$homepage_url = parse_url(Mage::getBaseUrl(), PHP_URL_PATH);
		return $homepage_url;
	}

	public function getPopupBtnTxt()
	{
		if (Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/popup_btntxt')) {
			$popup_btntxt = Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/popup_btntxt');
			$popup_btntxt = str_replace("'", "\'", $popup_btntxt);

		} else {
			$popup_btntxt = $this->__('Ok');
		}
		return $popup_btntxt;
	}


/*
	Magento attribute input types:
	Button, Checkbox, Checkboxes, Collection, Column, 
	Date, Editor, Fieldset, File, Gallery, Hidden, Image, 
	Imagefile, Label, Link, Multiline, Multiselect, Note, 
	Obscure, Password, Radio, Radios, Reset, Select, 
	Submit, Text, Textarea, Time

	EPC Collector current supports: select, text, date, and textarea
	If you need another input type you will have to add it to the
	function below.
*/

	public function cleanHtml($html)
	{

		// remove line breaks from html
		$html = str_replace(array("\r\n", "\r"), "\n", $html);
		$lines = explode("\n", $html);
		$new_lines = array();

		foreach ($lines as $i => $line) {
		    if(!empty($line))
		        $new_lines[] = trim($line);
		}

		$html = implode($new_lines);

		return $html;

	}


} // end class
