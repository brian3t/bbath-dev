<?php
/**
 * GoController.php
 * Controller responsible for adding new emails to the email collector database
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */
//ob_start();

require_once Mage::getModuleDir('', 'Tegdesign_Emailcollector') . '/lib/MailChimp.class.php';

class Tegdesign_Emailcollector_GoController extends Mage_Core_Controller_Front_Action {

    const DEBUG_FILE = 'tegdesign_emailcollector_debug.log';
    const PASSWD_LEN = 6;
    const FORCE_DEBUG_MODE = false;
    const MC_ADVANCED_MERGE_ENABLED = false;
    const MC_EXTRA_FIELDS_ENABLED = true;

    public function joinAction() {

        $epc = Mage::helper('tegdesign_emailcollector');
        $generated_coupon = NULL;

        if (!$this->getRequest()->isPost()) {
            $this->debugLog('error', 'no post data', true);
        }

        $postData = Mage::app()->getRequest()->getPost();
        $this->debugLog('postData', print_r($postData, true), false);

        if (!isset($postData['store_id'])) {
            $this->debugLog('returned', 'no store id posted', true);
        }

       try {
            $store = Mage::getModel('core/store')->load($postData['store_id']);
            $store_name = $store->getName();
            $store_id = $store->getId();
            $website_id = $store->getWebsiteId();
        } catch (Exception $e) {
            $this->debugLog('store exception', $e->getMessage(), true);
        }

        if (!isset($postData['popup_email']) || $postData['popup_email'] == '') {
            $this->debugLog('returned', 'no email posted', true);
        }

        $postData['popup_email'] = trim(strtolower($postData['popup_email']));

        if (!$this->canAddEmail($postData['popup_email'], $store_id, $website_id)) {
            $this->debugLog('error', 'Email already exists in Magento', false);
		} else {
	        // are we registering a customer ?
	        if ($epc->getForceRegEnabled()) {
	            if (isset($postData['password']) && !empty($postData['password'])) {
	                $this->registerCustomer($postData, $store);
	            }
	        }
	
	        if ($epc->getAddToNewsletterEnabled()) {
	
	            try {
	
	                $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($postData['popup_email']);
	                            
	                if (!$subscriber->getId() 
	                    || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED 
	                    || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
	                        
	                    $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
	                    $subscriber->setSubscriberEmail($postData['popup_email']);
	                    $subscriber->setSubscriberConfirmCode($subscriber->RandomSequence());
	                    $subscriber->setStoreId($store_id);
	                    $subscriber->save();
	                }
	
	            } catch (Exception $e) {
	                $this->debugLog('newsletter subscribe error', $e->getMessage());
	            }
	
	        }
	
	        try {
	
	            $emailcollector = Mage::getModel('tegdesign_emailcollector/emails');
	            $emailcollector->setEmail($postData['popup_email']);
	            $emailcollector->setDateCollected(strtotime('now'));
	            $emailcollector->setStoreId($store_id);
	            $emailcollector->setWebsiteId($website_id);
	                            
	            $generated_coupon = '';
	
	            if ($epc->getUseCouponEnabled()) { 
	                $generated_coupon = $this->generateCoupon($postData['popup_email'], $epc->getPromoCoupon(), $store);
	                $emailcollector->setCoupon($generated_coupon);
	            } else {
		            $generated_coupon = NULL;
	            }
	
	            if (array_key_exists('firstname', $postData)) {
	                $emailcollector->setFirstname($postData['firstname']);
	            }
	
	            if (array_key_exists('lastname', $postData)) {
	                $emailcollector->setLastname($postData['lastname']);
	            }
	
	            if (array_key_exists('extra', $postData)) {
	                $emailcollector->setExtra($postData['extra']);
	            }
	
	            // add email to database
	            $emailcollector->save();
	
	        } catch (Exception $e) {
	            $this->debugLog('error saving email', $e->getMessage());
	        }
	
	        if ($epc->getMagentoEmailEnabled()) {
	            $this->sendMagentoEmail($postData['popup_email'], $generated_coupon, $store);
	        }
		}
	    
	    $this->addOrUpdateToBronto($postData['popup_email'], $postData, $generated_coupon); //Even if in Magento update Bronto
    }
    
    public function updateAction() {
	    $postData = Mage::app()->getRequest()->getPost();

        if ($this->getRequest()->isPost()) {
	        $this->debugLog('postData', print_r($postData, true), false);
			$this->addOrUpdateToBronto($postData['popup_email'], $postData, NULL);
		}
		
		return json_encode('Success');
    }
    
    public function addOrUpdateToBronto($email, $fields, $generated_coupon = NULL) {
	    
	    if(!empty($fields)) {
		    $bronto_fields = array();
		    foreach($fields as $key => $value) {
			    if($value != '' && $key != 'popup_email' && $key != 'ok' && $key != 'store_id') {
					$bronto_fields[] = array('fieldId' => $key, 'content' => $value);
			    }
		    }
		    $this->debugLog('bronto', print_r($bronto_fields, true), false);
	    }
	    
	    if($generated_coupon != '') {
		    $bronto_fields[] = array(
		    					array('fieldId' => '0bce03e90000000000000000000000017054', 'content' => $generated_coupon), //coupon_code
		    					array('fieldId' => '0bce03e90000000000000000000000017809', 'content' => date('Y-m-d')) // coupon_date
		    				);
		    $this->debugLog('bronto_coupon', print_r($bronto_fields, true), false);
	    }

/*
	    $fields = array(
				  	array('fieldId' => '0bce03e90000000000000000000000017022', 'content' => 'test'), //source
				  	array('fieldId' => 'bc278cdf-f23d-4e11-8992-2611d956c7bb', 'content' => 'Thomas'), //first_name
				  	array('fieldId' => '2a8aad8c-3153-439b-b043-2155e7870ede', 'content' => 'Morris'), //last_name
				  	array('fieldId' => '0bce03e9000000000000000000000001704e', 'content' => '1970-07-07'), //childs_birthday FORMAT YYYY-MM-DD
				  	array('fieldId' => '0bce03e900000000000000000000000170ba', 'content' => 'Yes'), //gift_purchase Yes/No
				  	array('fieldId' => '0bce03e90000000000000000000000017100', 'content' => 'Boy'), //childs_gender Boy/Girl/Surprise
				  	array('fieldId' => '0bce03e90000000000000000000000017054', 'content' => $generated_coupon), //coupon_code
				  	array('fieldId' => '0bce03e90000000000000000000000017022', 'content' => 'modal') //source
				  );
*/
		//**dev.bronto.com/api/v4/functions/add/addorupdatecontacts
		
	    $client = new SoapClient('https://api.bronto.com/v4?wsdl', array('trace' => 1, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS));
 
		try {
			// Add in a valid API token
			$token = Mage::getStoreConfig('bronto/settings/api_token', $store_id);;
			
			//print "logging in\n";
			$sessionId = $client->login(array('apiToken' => $token))->return;
			
			$session_header = new SoapHeader("http://api.bronto.com/v4",
								'sessionHeader',
								array('sessionId' => $sessionId));
			$client->__setSoapHeaders(array($session_header));
			
			// Replace SOME CONTENT with a string. We assume here
			// the field is storing a string. The value you pass in
			// should match the type set for the field.
			// Replace SOME FIELD ID with a valid field ID. Field IDs
			// can be obtained by calling readFields. Field IDs are also
			// available in the footer when viewing an individual field in
			// the UI.
			
			// Note: The lists you set in this call will be absolute, not 
			// incremental, to lists the contact may already be on. The contact 
			// will be removed from any list(s) not specified in this call and 
			// will only be added to lists you specify in this call. If your intent 
			// is to incrementally add a contact to a list without affecting their 
			// membership on other lists, use the addToList function. If you want to 
			// incrementally remove a contact from a list, use the removeFromList 
			// function. If you want to use this call to incrementally add the contact 
			// to a new list and retain their current list membership, you'll need to 
			// call readContacts, obtain the ids for the lists the contact is currently 
			// a member of, and pass in those ids along with the new list ids when
			// calling updateContacts.
			$contacts = array('email' => $email,
						'listIds' => '0bce03ec000000000000000000000005e201',
						'fields' => $bronto_fields);
			
			//print "Adding contact with the following attributes\n";
			$write_result = $client->addOrUpdateContacts(array($contacts))->return;
			
			if ($write_result->errors) {
				$this->debugLog('bronto_error', print_r($write_result->results), false);
				//print_r($write_result->results);
			} elseif ($write_result->results[0]->isNew == true) {
				$this->debugLog('bronto_add', "The contact has been added.  Contact Id: " . $write_result->results[0]->id, false);
				//print "The contact has been added.  Contact Id: " . $write_result->results[0]->id . "\n";
			} else {
				$this->debugLog('bronto_update', "The contact has been updated. " . $write_result->results[0]->id, false);
				//print "The contact's information has been updated.  Contact Id: " . $write_result->results[0]->id . "\n";
			}
			
		} catch (Exception $e) {
			//print "uncaught exception\n";
			//print_r($e);
		}
    }

    public function doMailchimp($email, $generated_coupon = '') {

        $this->debugLog('info', 'mailchimp enabled');

        $epc = Mage::helper('tegdesign_emailcollector');

        $mailchimp_apikey = $epc->getMailChimpAPIKey();
        $mailchimp_listid = $epc->getMailChimpListId();

        $mailchimp = new MailChimp($mailchimp_apikey);
        $mc_data = array();

        // Mailchimp extra merge fields
        if (self::MC_ADVANCED_MERGE_ENABLED) {
            $this->debugLog('info', 'MC_ADVANCED_MERGE_ENABLED');

            /*
            if you wish to control the extra post data
            that is sent to MailChimp
            uncomment these lines
            note the postData array contains all fields
            posted from the popup collector
            if you turn on debugging you can see the values
            in the DEBUG_FILE
            */

            //$mc_data['you_merge_field_from_mailchimp'] = $postData['field_name_from_popup'];
            //$mc_data['another_merge_field'] = $postData['some_field_from_popup'];

        } else {

            // Use the standard method for extra fields
            if (self::MC_EXTRA_FIELDS_ENABLED) {
                $this->debugLog('info', 'MC_EXTRA_FIELDS_ENABLED');
                if ($epc->getExtraFieldsEnabled()) {
                    $this->debugLog('info', 'MC_EXTRA_FIELDS_ENABLED epcExtraFieldsEnabled');
                    foreach ($postData as $key => $value) {
                        if (!empty($value)) {
                            $mc_data[$key] = $value;
                        }
                    }
                }
            }

        }

        if ($epc->getMailChimpAutoresponderEnabled()) {

            $this->debugLog('info', 'mailchimp auto responder enabled');

            if ($epc->getUseCouponEnabled()) {
            
                $mailchimp_autoresponderfield = $epc->getMailchimpAutoresponderfield();
            
                if ($epc->getMailchimpSendCouponEnabled()) {

                    $mailchimp_coupon_merge_field = $epc->getMailchimpMergeField();

                    if ($generated_coupon == '') {
                        $mc_data[$mailchimp_coupon_merge_field] = $generated_coupon;
                    } else {
                        $mc_data[$mailchimp_coupon_merge_field] = $this->generateCoupon($email, $store);
                    }

                    // this is the value that triggers the autoresponder at Mailchimp
                    $mc_data[$mailchimp_autoresponderfield] = 'yes';

                }
                
            }

        }

        $this->debugLog('list_id', $mailchimp_listid);
        $this->debugLog('mc_data', print_r($mc_data, true));

        $result = $mailchimp->call('lists/subscribe', array(
            'id'                => $mailchimp_listid,
            'email'             => array('email' => $email),
            'merge_vars'        => $mc_data,
            'double_optin'      => false,
            'update_existing'   => true,
            'replace_interests' => false,
            'send_welcome'      => false,
        ));

        $this->debugLog('mc', print_r($result, true));

    }

    public function sendMagentoEmail($email, $generated_coupon, $store) {

        $this->debugLog('info', 'magento_template_mode');

        $epc = Mage::helper('tegdesign_emailcollector');

        $store_name = $store->getName();
        $store_id = $store->getId();

        $template = Mage::getModel('core/email_template');
        $translate  = Mage::getSingleton('core/translate');
        $template_id = $epc->getMagentoEmailTemplate();
        $template_collection =  $template->load($template_id);
        $template_data = $template_collection->getData();

        // make sure the template exists
        if (empty($template_data)) {
            $this->debugLog('error', 'template empty', true);
        }

        $template_id = $template_data['template_id'];
        $mail_subject = $template_data['template_subject'];

        $from_email = Mage::getStoreConfig('trans_email/ident_general/email');
        $from_name = Mage::getStoreConfig('trans_email/ident_general/name');

        $sender = array();
        $sender['name'] = Mage::getStoreConfig('trans_email/ident_general/name');
        $sender['email'] = Mage::getStoreConfig('trans_email/ident_general/email');

        $model = $template->setReplyTo($sender['email'])->setTemplateSubject($mail_subject);

        $vars = array();
        if ($epc->getUseCouponEnabled()) {
            $vars['coupon'] = $generated_coupon;
            $this->debugLog('vars', $vars);
        }

        // send the email
        $model->sendTransactional($template_id, $sender, $email, $store_name, $vars, $store_id);

        if ($template->getSentSuccess()) {

            Mage::dispatchEvent('emailcollector_email_sent_after', array(
                'email'   => $email,
                'vars'    => $vars
            ));

        } else {
            $template_dump = $template->getData();
            $this->debugLog('info', $template_dump, true);
        }

        $translate->setTranslateInline(true);

    }

/*
    public function redirectToLandingPage($error = false, $store_id) {
        $backtrace = debug_backtrace();
        $epc = Mage::helper('tegdesign_emailcollector');

        if ($error) {
            // allows a customer to get in twice if enabled
            $this->debugLog('error redirect on line', $backtrace[0]['line']);
            header('Location: ' . $epc->getErrorUrl());
            exit();
        } else {
            $this->debugLog('info', 'redirected to redirect url');
            header('Location: ' . $epc->getRedirectUrl());
            exit();
        }
    }
*/

    public function canAddEmail($email, $store_id, $website_id) {

        if ($this->isEmailValid($email)) {

            if ($this->isFreshEmail($email, $store_id, $website_id)) {
                return true;
            } else {
                $this->debugLog('error', 'email not fresh', false);
                return false;
            }
        } else {
            $this->debugLog('error', 'email not valid', true);
            return false;
        }

    }

    public function isFreshEmail($email, $store_id, $website_id) {

        // check to see if email already exists in system
        $size = Mage::getModel('tegdesign_emailcollector/emails')
                    ->getCollection()
                    ->addFieldToFilter('email', $email)
                    ->addFieldToFilter('store_id', $store_id)
                    ->addFieldToFilter('website_id', $website_id)
                    ->getSize();

        //$this->debugLog('size', $size);
        //$size = $collection->getSize();
        if ($size == 0) {
            return true;
        } else {
            return false;
        }

    }

    public function registerCustomer($postData, $store) {

        try {

            $epc = Mage::helper('tegdesign_emailcollector');

            if (Mage::helper('core/string')->strlen($postData['password']) <= self::PASSWD_LEN) {
                $this->debugLog('registerCustomer', 'PASSWD_LEN', true);
            }

            $website_id = $store->getWebsiteId();
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId($website_id);
            $customer->loadByEmail($postData['popup_email']);

            // customer exists
            if ($customer->getId()) {
                $this->debugLog('customer save', 'customer exists');
                return;
            }

            $customer->setEmail($postData['popup_email']); 
            $customer->setPassword($postData['password']);
            $customer->setConfirmation(null);
            
            // setting data such as email, firstname, lastname, and password
            if ($epc->getExtraFieldsEnabled()) {
                
                foreach ($postData as $key => $value) {
                    // find any date fields
                    if (strpos($key,'epcdate_') !== false) {
                        $key = str_replace('epcdate_', '', $key);
                        $value = strtotime($value);
                    }

                    if (!empty($value)) {
                        $customer->setData($key, $value);
                    }
                }

            }

            $customer->setStore($store);
            $customer->setWebsiteId($website_id);
            $customer->save(); 
            $customer->sendNewAccountEmail();

            Mage::dispatchEvent('emailcollector_customer_save_after', array(
                'customer_id'   => $customer->getId()
            ));

            $this->loginCustomer($customer, $website_id, $email);

        } catch (Exception $e) {
            $this->debugLog('register customer error', $e->getMessage());
        }

    }

    public function loginCustomer($customer, $website_id, $email) {

        try {

            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId($website_id);
            $customer->loadByEmail($email);

            Mage::getSingleton('customer/session')
                ->setCustomerAsLoggedIn($customer)
                ->renewSession();

        } catch (Exception $e) {
            $this->debugLog('login error', $e->getMessage());
        }

    }

    public function generateUniqueId($length = null) {
        $rndId = crypt(uniqid(rand(),1));
        $rndId = strip_tags(stripslashes($rndId));
        $rndId = str_replace(array(".", "$"),"",$rndId);
        $rndId = strrev(str_replace("/","",$rndId));

        if (!is_null($rndId)){
            return strtoupper(substr($rndId, 0, $length));
        }

        return strtoupper($rndId);
    }

    public function generateCoupon($email, $coupon_code, $store) {

        $store_id = $store->getId();

        $code = $this->generateUniqueId(12);
        $label = $email; //coupon label

        $coupon = Mage::getModel('salesrule/coupon')->load($coupon_code, 'code');
        $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());

        $from_date = $rule->getFromDate();
        $to_date = $rule->getToDate();
        $uses_per_coupon = $rule->getUsesPerCoupon();
        $uses_per_customer = $rule->getUsesPerCustomer();
        $customer_group_ids = $rule->getCustomerGroupIds();
        $conditions = $rule->getConditionsSerialized();
        $actions = $rule->getActionsSerialized();
        $rules_processing = $rule->getStopRulesProcessing();
        $adv = $rule->getIsAdvanced();
        $pids = $rule->getProductIds();
        $sorto = $rule->getSortOrder();
        $disqty = $rule->getDiscountQty();
        $sfreeship = $rule->getSimpleFreeShipping();
        $applyship = $rule->getApplyToShipping();
        $rss = $rule->getIsRss();
        $coupon_type = $rule->getCouponType();
        $simple_action = $rule->getSimpleAction();
        $amount = $rule->getDiscountAmount();

        $name = $label;
        $labels[0] = $label;

        $new_coupon = Mage::getModel('salesrule/rule');

        $new_coupon->setName($name)
            ->setDescription($name)
            ->setFromDate($from_date)
            ->setToDate($to_date)
            ->setCouponCode($code)
            ->setUsesPerCoupon($uses_per_coupon)
            ->setUsesPerCustomer($uses_per_customer)
            ->setCustomerGroupIds($customer_group_ids)
            ->setIsActive(1)
            ->setConditionsSerialized($conditions)
            ->setActionsSerialized($actions)
            ->setStopRulesProcessing($rules_processing)
            ->setIsAdvanced($adv)
            ->setProductIds($pids)
            ->setSortOrder($sorto)
            ->setDiscountQty($disqty)
            ->setDiscountStep($sfreeship)
            ->setSimpleFreeShipping($sfreeship)
            ->setApplyToShipping($applyship)
            ->setIsRss($rss)
            ->setWebsiteIds($store->getWebsiteId())
            ->setCouponType($coupon_type)
            ->setStoreLabels($labels)
            ->setDiscountAmount($amount)
            ->setSimpleAction($simple_action);

        $new_coupon->save();

        return $code;

    }

    public function debugLog($code, $msg, $redirect = false, $store_id = 0) {
        if (Mage::helper('tegdesign_emailcollector')->getDebugEnabled() || self::FORCE_DEBUG_MODE) {
            
            $backtrace = debug_backtrace();
            Mage::log('LINE: ' . $backtrace[0]['line'] . ' > ' . $code . ' - ' . $msg, null, self::DEBUG_FILE);

            if ($redirect) {
                if ($store_id == 0) {
                    $store_id = Mage::helper('tegdesign_emailcollector')->getStoreId();
                }
                exit;
                //$this->redirectToLandingPage(true, $store_id);
            }
        } else {
            if ($redirect) {
                if ($store_id == 0) {
                    $store_id = Mage::helper('tegdesign_emailcollector')->getStoreId();
                }
                exit;
                //$this->redirectToLandingPage(true, $store_id);
            }
        }
    }

    public function isEmailValid($email) {
        $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';
        return preg_match($pattern,$email);
    }

}