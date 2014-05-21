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
class Magestudio_ShopSocially_Block_Script extends Mage_Core_Block_Template {
    /**
     * Prepate layout
     * @return object
     */
    const DEFAULT_ANCHOR = 'buttons-set';

    protected function _toHtml() {

        $rez = '';

        if ($this->getAnchorClass() && $this->getAnchorClass() != self::DEFAULT_ANCHOR) {
            $anchorClass = $this->getAnchorClass();
            $rez = parent::_toHtml();
        } else {
            $anchorClass = self::DEFAULT_ANCHOR;
        }

        if (Mage::helper('shopsocially')->isActive()) {

            $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            $order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
            $rez.='	<script language="javascript" type="text/javascript" src="https://shopsocially.com/js/all.js"> </script>';
            if (Mage::helper('shopsocially')->isAutoplace()) {
                $rez.='	
				<script language="javascript" type="text/javascript">
				$$(\'.' . $anchorClass . '\').each(function(el){Element.insert(el,{before:\'<div id="ssFrame" style="float:left;"></div>\'});});
				</script>';
            }
            $rez.='	<script language="javascript" type="text/javascript">';
            $rez.='		ss_mi.init({';
            $rez.='		partner_id: \'' . Mage::helper('shopsocially')->getAccountId() . '\',';
            $rez.='		store_type: \'Magento\',';
            $rez.='		store_currency: \'' . Mage::app()->getStore()->getCurrentCurrencyCode() . '\',';
            $rez.='		order_id: \'' . $orderId . '\',';
            $rez.='		ssFrame_width: \'' . Mage::helper('shopsocially')->getWidth() . '\',';
            if (!Mage::helper('shopsocially')->isPopup()) {
                $rez.='		auto_popup: -1,';
            }
            if (Mage::helper('shopsocially')->isUseSandbox()) {
                $rez.='		sandbox: true,';
            }
            $rez.='		ssFrame_height: \'' . Mage::helper('shopsocially')->getHeight() . '\'';
            $rez.='		});';
            $rez.='	</script>';
            $rez.='<script language="javascript" type="text/javascript">';
            $rez.='ss_mi.add_products([';
            $itemNum = 1;
            $itemQty = count($order->getAllVisibleItems());
            foreach ($order->getAllVisibleItems() as $item) {
                $productId = $item->getProductId();
                $product = Mage::getModel('catalog/product')->load($productId);
                $categoryIds = $product->getCategoryIds();
                $productCategory = $this->fetchCategories($categoryIds);
                
                $rez.='        {prod_page_url: \'' . $product->getProductUrl() . '\', ';
                $rez.='        prod_img_url: \'' . $this->helper('catalog/image')->init($product, 'thumbnail') . '\',';
                //$rez.='        prod_img_url_https: \''.str_replace('http://','https://',$this->helper('catalog/image')->init($product, 'thumbnail')).'\',';				
                $rez.='        prod_title: \'' . $product->getName() . '\',';
                $rez.='        prod_currency: \'' . Mage::app()->getStore()->getCurrentCurrencyCode() . '\',';
                $rez.='        prod_price: \'' . sprintf("%.2f", $product->getPrice()) . '\',';
                $rez.='        prod_category: \'' . $productCategory . '\',';
                $rez.='        prod_id: \'' . $productId . '\'';
                $rez.= ( $itemQty == $itemNum++ ? '        }' : '        },');
            }
            $rez.='        ]);';
            $rez.='</script>';

            $rez.='<script language="javascript" type="text/javascript">';
            $rez.='	ss_mi.load_ssFrame({div_id: \'ssFrame\'});';
            $rez.='</script>';
        }
        return $rez;
    }

    protected function getAnchorClass() {
        return Mage::helper('shopsocially')->getAnchorClass();
    }
    
    private function fetchCategories($categories){
        
        $productCategory = '';
        if(count($categories) > 0){
            foreach($categories as $categoryId) {
                $catResource = Mage::getModel('catalog/category')->load($categoryId);
                $catNamesArray[] = $catResource->getName();
            }
            $productCategory = implode(',', $catNamesArray);
        }
        return $productCategory;
    }

}
?>