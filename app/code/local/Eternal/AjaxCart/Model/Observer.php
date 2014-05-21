<?php

class Eternal_AjaxCart_Model_Observer {

    public function addToCartEvent($observer) {

        if (!Mage::helper('eternal_ajaxcart')->getConfig('general/enable'))
            return;
            
        $is_ajax = Mage::app()->getFrontController()->getRequest()->getParam('ajaxcart');
        
        if (!$is_ajax)
            return;
            
        $request = Mage::app()->getFrontController()->getRequest();

        if (!$request->getParam('in_cart') && !$request->getParam('is_checkout')) {

            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);

            $_response = Mage::getModel('eternal_ajaxcart/response')
                    ->setProductName($observer->getProduct()->getName())
                    ->setMessage(Mage::helper('checkout')->__('%s was added into cart.', $observer->getProduct()->getName()));

            //append updated blocks
            $_response->addUpdatedBlocks($_response);

            $_response->send();
        }
        if ($request->getParam('is_checkout')) {

            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);

            $_response = Mage::getModel('eternal_ajaxcart/response')
                    ->setProductName($observer->getProduct()->getName())
                    ->setMessage(Mage::helper('checkout')->__('%s was added into cart.', $observer->getProduct()->getName()));
            $_response->send();
        }
    }

    public function updateItemEvent($observer) {

        if (!Mage::helper('eternal_ajaxcart')->getConfig('general/enable'))
            return;
        
        $is_ajax = Mage::app()->getFrontController()->getRequest()->getParam('ajaxcart');
        
        if (!$is_ajax)
            return;
        
        $request = Mage::app()->getFrontController()->getRequest();

        if (!$request->getParam('in_cart') && !$request->getParam('is_checkout')) {

            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);

            $_response = Mage::getModel('eternal_ajaxcart/response')
                    ->setMessage(Mage::helper('checkout')->__('Item was updated.'));

            //append updated blocks
            $_response->addUpdatedBlocks($_response);

            $_response->send();
        }
        if ($request->getParam('is_checkout')) {

            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);

            $_response = Mage::getModel('eternal_ajaxcart/response')
                    ->setMessage(Mage::helper('checkout')->__('Item was updated.'));
            $_response->send();
        }
    }

    public function getConfigurableOptions($observer) {
        if (!Mage::helper('eternal_ajaxcart')->getConfig('general/enable'))
            return;
        
        $is_ajax = Mage::app()->getFrontController()->getRequest()->getParam('ajaxcart');
        
        if (!$is_ajax)
            return;
        
        $_response = Mage::getModel('eternal_ajaxcart/response');

        $product = Mage::registry('current_product');
        if (!$product->isConfigurable() && !$product->getTypeId() == 'bundle'){return false;exit;}

        //append configurable options block
        $_response->addConfigurableOptionsBlock($_response);
        $_response->send();
    }

    public function getGroupProductOptions() {
        if (!Mage::helper('eternal_ajaxcart')->getConfig('general/enable'))
            return;
        
        $id = Mage::app()->getFrontController()->getRequest()->getParam('product');
        $options = Mage::app()->getFrontController()->getRequest()->getParam('super_group');

        if($id) {
            $product = Mage::getModel('catalog/product')->load($id);
            if($product->getData()) {
                if($product->getTypeId() == 'grouped' && !$options) {
                    $_response = Mage::getModel('eternal_ajaxcart/response');
                    Mage::register('product', $product);
                    Mage::register('current_product', $product);

                    //add group product's items block
                    $_response->addGroupProductItemsBlock($_response);
                    $_response->send();
                }
            }
        }
    }

}