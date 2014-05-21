<?php

/**
 * ShopSocially
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopsocially.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade ShopSocially tonewer
 * versions in the future. If you wish to customize ShopSocially for your
 * needs please contact partners@shopsocially.com for more information.
 *
 * @copyright Copyright (c) 2010 Velocita, Inc. DBA ShopSocially(http://www.shopsocially.com)

 * @license http://opensource.org/licenses/osl-3.0.php Open Software License(OSL 3.0)
 * @author Velocita, Inc. DBA ShopSocially (http://www.shopsocially.com)
 * */
class Magestudio_ShopSocially_Helper_Data extends Mage_Catalog_Helper_Data {

    private $_config = null;
    private $_adv_config = null;

    const PATH_TO_CONFIG = 'shopsocially/general';
    const PATH_TO_CONFIG_ADVANCED = 'shopsocially/advanced';

    public function getConfig() {
        if (!$this->_config) {
            $this->_config = new Varien_Object;
            $this->_config->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG, Mage::app()->getStore()));
        }
        return $this->_config;
    }

    public function getAdvancedConfig() {
        if (!$this->_adv_config) {
            $this->_adv_config = new Varien_Object;
            $this->_adv_config->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG_ADVANCED, Mage::app()->getStore()));
        }
        return $this->_adv_config;
    }

    public function getAccountId() {
        return $this->getConfig()->getAccountId();
    }

    public function getEmail() {
        return $this->getConfig()->getEmail();
    }

    public function isActive() {
        return ($this->getConfig()->getEnabled() && $this->getAccountId());
    }

    public function isUseSandbox() {
        return false; //$this->getConfig()->getSandbox();
    }

    public function isAutoplace() {
        return $this->getAdvancedConfig()->getAuto();
    }

    public function isPopup() {
        return $this->getAdvancedConfig()->getPopup();
    }

    public function getUrl() {
        return $this->getAdvancedConfig()->getUrl();
    }

    public function getWidth() {
        return $this->getAdvancedConfig()->getWidth();
    }

    public function getHeight() {
        return $this->getAdvancedConfig()->getHeight();
    }

    public function getAnchorClass() {
        return $this->getConfig()->getAnchorCssClass();
    }

}
