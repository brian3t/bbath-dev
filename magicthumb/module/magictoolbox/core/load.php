<?php

    require_once(BP . DS . 'app' . DS . 'etc' . DS . 'magictoolbox' . DS . 'core' . DS . 'magicthumb.module.core.class.php');
    $tool = new MagicThumbModuleCoreClass();

    // allow to use different ini files for different themes
    // get ini file from current theme folder by default
    $interface = Mage::getSingleton('core/design_package')->getPackageName();
    $theme = Mage::getSingleton('core/design_package')->getTheme('template');
    $iniFile = BP . DS . 'app' . DS . 'design' . DS . 'frontend' . DS . $interface . DS . $theme . DS . 'magicthumb.settings.ini';
    if(!file_exists($iniFile)) {
        // if we can't found ini file for current theme we should get default ini file
        $iniFile = BP . DS . 'app' . DS . 'etc' . DS . 'magictoolbox' . DS . 'magicthumb.settings.ini';
    }
    // load INI
    $tool->params->loadINI($iniFile);

    /* load locale */

    $mz_m = $this->__('MagicThumb_Message');
    if($mz_m != 'MagicThumb_Message') {
        $tool->params->set('message', $mz_m);
    }



    $GLOBALS["magictoolbox"]["magicthumb"] = & $tool;

    require_once(BP . DS . 'app' . DS . 'etc' . DS . 'magictoolbox' . DS . 'core' . DS . 'addons.php');

?>
