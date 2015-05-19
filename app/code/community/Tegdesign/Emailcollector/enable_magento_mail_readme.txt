Readme first

In order to always send mail to both Mailchimp Autoresponder and Magento Email:

1- Disable magento sending mail in GoController:

Open Tegdeign/Emailcollector/controllers/GoController.php:

Look for this if block
if (Mage::getStoreConfig('tegdesign_emailcollector_options/settings/emailcollector_template', $store_id)) {
}

Inside this if block, look for codeblock that does this:

                                        $model->sendTransactional($templateId, $sender, $postData['popup_email'], $store_name, $vars, $store_id);

                                        if (!$mailTemplate->getSentSuccess()) {

                                            $template_dump = $mailTemplate->getData();
                                            Mage::log('CODE4:', null, 'tegdesign_emailcollector.log');
                                            Mage::log($template_dump, null, 'tegdesign_emailcollector.log');

                                        }

                                        $translate->setTranslateInline(true);


Comment it out that block;

2- Force mailchimpautoresponder to always yes:

Open Tegdeign/Emailcollector/controllers/GoController.php:

Look for the if statement 

						elseif (Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_autoresponder_enabled', $store_id)) {

                            $mailchimp_autoresponder = true;

                        }

And remove the elseif statement. Keep the assignment only:

                            $mailchimp_autoresponder = true;


3- Pass the variables to mailchimp sending function:
Open file Tegdesign/EmailCollector/lib/MailChimp.class.php

Go to function _raw_request
After the finish of curl request, add magento email sending code:

		/*
		 * add magento mail sending
		 */
		$model = $args['model'];
		$templateId = $args['templateId'];
		$sender = $args['sender'];
		$postData = $args['postData'];
		$store_name = $args['store_name'];
		$vars = $args['vars'];
		$store_id = $args['store_id'];
		$mailTemplate = $args['mailTemplate'];
		$translate = $args['translate'];
		

		$model->sendTransactional($templateId, $sender, $postData['popup_email'], $store_name, $vars, $store_id);

		if (!$mailTemplate->getSentSuccess()) {

			$template_dump = $mailTemplate->getData();
			Mage::log('CODE4:', null, 'tegdesign_emailcollector.log');
			Mage::log($template_dump, null, 'tegdesign_emailcollector.log');

		}

		$translate->setTranslateInline(true);

		/*
		 * end magento mail sending
		 */

Remember to fix the error of sending two coupon codes instead of one:
 At line
                                         if ($generated_coupon == '') {

                                             $mc_data[$mailchimp_coupon_merge_field] = $generated_coupon;

                                         } else {

                                             $mc_data[$mailchimp_coupon_merge_field] = $this->generateCoupon($postData['popup_email'], $store);
                                         }
Change to:
                                         if ($generated_coupon !== '') {


That's all. 

