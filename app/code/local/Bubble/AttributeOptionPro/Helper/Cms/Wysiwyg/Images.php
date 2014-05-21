<?php
/**
 * @category    Bubble
 * @package     Bubble_AttributeOptionPro
 * @version     1.1.0
 * @copyright   Copyright (c) 2013 BubbleCode (http://shop.bubblecode.net)
 */
class Bubble_AttributeOptionPro_Helper_Cms_Wysiwyg_Images extends Mage_Cms_Helper_Wysiwyg_Images
{
    public function isUsingStaticUrlsAllowed()
    {
        if (Mage::getSingleton('adminhtml/session')->getStaticUrlsAllowed()) {
            return true;
        }

        return parent::isUsingStaticUrlsAllowed();
    }
}