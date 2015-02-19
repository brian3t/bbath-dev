var fload = true;
chkAutoRespSel = function() {

    if ($('tegdesign_emailcollector_options_settings_mailchimp_autoresponder_enabled').getValue() > 0) {

        $('row_tegdesign_emailcollector_options_settings_mailchimp_autoresponderfield').show();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_apikey').show();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_listid').show();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_send_coupon_enabled').show();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_coupon_merge_field').show();

        $('tegdesign_emailcollector_options_settings_mailchimp_enabled').setValue(1);
        $('tegdesign_emailcollector_options_settings_magento_email').setValue(0);
        $('row_tegdesign_emailcollector_options_settings_emailcollector_template').hide();

    } else {

        if ($('tegdesign_emailcollector_options_settings_mailchimp_enabled').getValue() == 0) {
            $('row_tegdesign_emailcollector_options_settings_mailchimp_apikey').hide();
            $('row_tegdesign_emailcollector_options_settings_mailchimp_listid').hide();
            $('row_tegdesign_emailcollector_options_settings_mailchimp_send_coupon_enabled').hide();
            $('row_tegdesign_emailcollector_options_settings_mailchimp_coupon_merge_field').hide();
        }

        $('tegdesign_emailcollector_options_settings_magento_email').setValue(1);
        $('row_tegdesign_emailcollector_options_settings_emailcollector_template').show();

        $('row_tegdesign_emailcollector_options_settings_mailchimp_autoresponderfield').hide();
    }

}
chkMCSel = function() {

    if ($('tegdesign_emailcollector_options_settings_mailchimp_enabled').getValue() > 0) {

        $('row_tegdesign_emailcollector_options_settings_mailchimp_apikey').show();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_listid').show();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_send_coupon_enabled').show();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_coupon_merge_field').show();

    } else {

        $('row_tegdesign_emailcollector_options_settings_mailchimp_apikey').hide();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_listid').hide();
        $('tegdesign_emailcollector_options_settings_mailchimp_autoresponder_enabled').setValue(0);
        $('row_tegdesign_emailcollector_options_settings_mailchimp_autoresponderfield').hide();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_send_coupon_enabled').hide();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_coupon_merge_field').hide();
    }

}
chkMSel = function() {

    if ($('tegdesign_emailcollector_options_settings_magento_email').getValue() > 0) {
        
        $('row_tegdesign_emailcollector_options_settings_emailcollector_template').show();

        $('tegdesign_emailcollector_options_settings_mailchimp_autoresponder_enabled').setValue(0);
        $('row_tegdesign_emailcollector_options_settings_mailchimp_autoresponderfield').hide();
        
    } else {

        $('tegdesign_emailcollector_options_settings_mailchimp_autoresponder_enabled').setValue(1);
        $('tegdesign_emailcollector_options_settings_mailchimp_enabled').setValue(1);
        $('row_tegdesign_emailcollector_options_settings_mailchimp_apikey').show();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_listid').show();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_autoresponderfield').show();
        
        $('row_tegdesign_emailcollector_options_settings_emailcollector_template').hide();
    }

}
chkCSel = function() {

    if ($('tegdesign_emailcollector_options_promosettings_use_coupon').getValue() > 0) {

        $('row_tegdesign_emailcollector_options_promosettings_promocoupon').show();

        if (!fload) {
            $('tegdesign_emailcollector_options_promosettings_promocoupon').toggleClassName('required-entry');
        }

        fload = false;

    } else {

        $('row_tegdesign_emailcollector_options_promosettings_promocoupon').hide();
        $('tegdesign_emailcollector_options_promosettings_promocoupon').toggleClassName('required-entry');
        document.getElementById('tegdesign_emailcollector_options_promosettings_promocoupon').selectedIndex = -1;

        fload = false;

    }

}
chkRSel = function() {

    if ($('tegdesign_emailcollector_options_promosettings_redirect_opts').getValue() == 'redirect_url') {

        $('row_tegdesign_emailcollector_options_promosettings_redirect_url').show();

    } else {

        $('row_tegdesign_emailcollector_options_promosettings_redirect_url').hide();
        $('tegdesign_emailcollector_options_promosettings_redirect_url').toggleClassName('required-entry');
        document.getElementById('tegdesign_emailcollector_options_promosettings_redirect_opts').selectedIndex = 1;

    }

}

chkMCCSel = function() {

    if ($('tegdesign_emailcollector_options_settings_mailchimp_send_coupon_enabled').getValue() > 0) {
        $('row_tegdesign_emailcollector_options_settings_mailchimp_coupon_merge_field').show();
    } else {
        $('row_tegdesign_emailcollector_options_settings_mailchimp_coupon_merge_field').hide();
    }

}

chkMCCSel = function() {

    if ($('tegdesign_emailcollector_options_settings_mailchimp_send_coupon_enabled').getValue() > 0) {
        $('row_tegdesign_emailcollector_options_settings_mailchimp_coupon_merge_field').show();
    } else {
        $('row_tegdesign_emailcollector_options_settings_mailchimp_coupon_merge_field').hide();
    }

}

chkMCASel = function() {
    if ($('tegdesign_emailcollector_options_settings_mailchimp_autoresponder_enabled').getValue() > 0) {
        $('row_tegdesign_emailcollector_options_settings_mailchimp_send_coupon_enabled').show();
    } else {
        $('row_tegdesign_emailcollector_options_settings_mailchimp_send_coupon_enabled').hide();
        $('row_tegdesign_emailcollector_options_settings_mailchimp_coupon_merge_field').hide();
    }
}



Event.observe(window, 'load', function() {

    // Event.observe('tegdesign_emailcollector_options_settings_mailchimp_autoresponder_enabled', 'change', chkAutoRespSel);
    // chkAutoRespSel();

    Event.observe('tegdesign_emailcollector_options_settings_mailchimp_enabled', 'change', chkMCSel);
    chkMCSel();

    Event.observe('tegdesign_emailcollector_options_settings_magento_email', 'change', chkMSel);
    chkMSel();

    Event.observe('tegdesign_emailcollector_options_promosettings_use_coupon', 'change', chkCSel);
    chkCSel();

    Event.observe('tegdesign_emailcollector_options_promosettings_redirect_opts', 'change', chkRSel);
    chkRSel();

    Event.observe('tegdesign_emailcollector_options_settings_mailchimp_send_coupon_enabled', 'change', chkMCCSel);
    chkMCCSel();

    Event.observe('tegdesign_emailcollector_options_settings_mailchimp_autoresponder_enabled', 'change', chkMCASel);
    chkMCASel();

});