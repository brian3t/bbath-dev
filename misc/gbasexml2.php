<?php header("Content-type: text/html; charset=utf-8");
require_once '../app/Mage.php';

define('SAVE_FEED_LOCATION','export/googlebase.xml');
set_time_limit(0);    
  
Mage::app(0);

 
$handle = fopen(SAVE_FEED_LOCATION, 'w');

$heading = array('<?xml version="1.0"?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:c="http://base.google.com/cns/1.0">
<channel>');
$feed_line=implode("\t", $heading)."\r\n";
fwrite($handle, $feed_line);
      
$products = Mage::getModel('catalog/product')->getCollection();
$products->addAttributeToSelect('*');
$products->addAttributeToFilter('status', 1);//enabled

Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($products);


$prodIds=$products->getAllIds();
try{
	foreach($prodIds as $productId) {
		$product = Mage::getModel('catalog/product')->setStoreId('1');
		$product->load($productId);
	
		if($product->getFinalPrice() != '') {
			$price = $product->getFinalPrice();
		} else {
			$price = $product->getFinalPrice();
		}

		if($product->getTypeId() == 'simple' && $product->getResource()->getAttribute('upc')->getFrontend()->getValue($product) != '') {
		            
			$product_d1['start']= '  <item>';
			$feed_line = implode("\t", $product_d1)."\r\n";
			
			$product_d2['title']= '    <title>'.$product->getName().'</title>';
			$feed_line .= implode("\t", $product_d2)."\r\n";
			
			$product_d3['link']= '    <link>'.$product->getProductUrl().'</link>';
			$feed_line .= implode("\t", $product_d3)."\r\n";
			
			$product_d4['price']= '    <g:price>'.number_format($product->getFinalPrice(),2).'</g:price>';
			$feed_line .= implode("\t", $product_d4)."\r\n";
			
			$product_d5['sku']= '    <g:id>'.$product->getSku().'</g:id>';
			$feed_line .= implode("\t", $product_d5)."\r\n";
			
			$product_d6['image_link']='    <g:image_link>'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage().'</g:image_link>';
			$feed_line .= implode("\t", $product_d6)."\r\n";
			
			$product_d7['description']='    <g:description>'.substr(preg_replace( '/\s+/', ' ', strip_tags($product->getDescription())), 0, 9800).'</g:description>';
			$feed_line .= implode("\t", $product_d7)."\r\n";
			
			$product_d8['manufacturer']='    <g:brand>'.$product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product).'</g:brand>';
			$feed_line .= implode("\t", $product_d8)."\r\n";
			
			$product_d9['manufacturer']='    <g:adwords_grouping>'.$product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product).'</g:adwords_grouping>';
			$feed_line .= implode("\t", $product_d9)."\r\n";
			
			$product_d10['manufacturer']='    <g:manufacturer>'.$product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product).'</g:manufacturer>';
			$feed_line .= implode("\t", $product_d10)."\r\n";
			
			if($product->getResource()->getAttribute('mpn')) {
				$product_d12['mpn']='    <g:mpn>'.$product->getResource()->getAttribute('mpn')->getFrontend()->getValue($product).'</g:mpn>';
				$feed_line .= implode("\t", $product_d12)."\r\n";
			}
			
			$product_d13['condition']='    <g:condition>'.('New').'</g:condition>';
			$feed_line .= implode("\t", $product_d13)."\r\n";
			
			$product_d14['product_type']='    <g:gtin>' . $product->getResource()->getAttribute('upc')->getFrontend()->getValue($product) . '</g:gtin>';
			$feed_line .= implode("\t", $product_d14)."\r\n";
			
			$product_d15['product_type']='    <g:product_type>' . htmlentities($product->getResource()->getAttribute('product_type')->getFrontend()->getValue($product)) . '</g:product_type>';
			$feed_line .= implode("\t", $product_d15)."\r\n";
			
			$product_d16['condition']='    <g:quantity>'.('1').'</g:quantity>';
			$feed_line .= implode("\t", $product_d16)."\r\n";
			
			$product_d17['availability']='    <g:availability>in stock</g:availability>';
			$feed_line .= implode("\t", $product_d17)."\r\n";
			
			$product_d18['google_product_category']='    <g:google_product_category>' . htmlentities($product->getResource()->getAttribute('product_type')->getFrontend()->getValue($product)) . '</g:google_product_category>';
			$feed_line .= implode("\t", $product_d18)."\r\n";
			
			fwrite($handle, $feed_line);
			fflush($handle);		
			$heading = array('  </item>');
			$feed_line=implode("\t", $heading)."\r\n\n";
			fwrite($handle, $feed_line);
		}
            
	}
        
    $footer = array('</channel></rss>');
    $feed_line=implode("\t", $footer)."\r\n";
    fwrite($handle, $feed_line);
    fclose($handle); 
			
}

catch(Exception $e){
    die($e->getMessage());
}