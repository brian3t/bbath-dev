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
class Magestudio_ShopSocially_Model_Email
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('shopsocially')->__('No')),
            array('value' => 1, 'label'=>Mage::helper('shopsocially')->__('Sharing Offer Image')),
            array('value' => 2, 'label'=>Mage::helper('shopsocially')->__('Sharing Offer Link'))			
        );
    }

}
