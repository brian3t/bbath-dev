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
**/ 

class Magestudio_ShopSocially_Block_Email extends Mage_Core_Block_Template {

    /**
     * Prepate layout
     * @return object
     */
    protected function _toHtml(){			
		$rez='';
		if(Mage::helper('shopsocially')->isActive()){
			$site=(Mage::helper('shopsocially')->isUseSandbox()?'go.shopsocially.com':'shopsocially.com');
			if(Mage::helper('shopsocially')->getEmail()==1){																																	
				$rez.='<a target="_blank" href="http://'.$site.'/merchant/claim_discount?pid='.Mage::helper('shopsocially')->getAccountId().'"><span style="text-decoration: none;"><img border="0" src="http://'.$site.'/image/'.Mage::helper('shopsocially')->getAccountId().'/email" />&nbsp;</span></a>';
			}else if(Mage::helper('shopsocially')->getEmail()==2){
				$rez.='<a target="_blank" href="http://'.$site.'/merchant/claim_discount?pid='.Mage::helper('shopsocially')->getAccountId().'">'.Mage::helper('shopsocially')->__('Share your purchase with friends and make them smile').'</a>';		
			}
		}
		Mage::log($rez);
		return $rez;
    }	
}
