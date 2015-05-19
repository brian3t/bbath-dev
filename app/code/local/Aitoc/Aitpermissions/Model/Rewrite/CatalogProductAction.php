<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/CatalogProductAction.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ UqqrIpEIRygqPDaW('608fc4b06eba65f9ebc6df9ffbfbf8aa'); ?><?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Product Mass Action processing model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Aitoc_Aitpermissions_Model_Rewrite_CatalogProductAction extends Mage_Catalog_Model_Product_Action
{
    /**
     * Update attribute values for entity list per store
     *
     * @param array $productIds
     * @param array $attrData
     * @param int $storeId
     * @return Mage_Catalog_Model_Product_Action
     */
    public function updateAttributes($productIds, $attrData, $storeId)
    {
        if(((Mage::app()->getRequest()->getControllerName() == 'catalog_product'
                && Mage::app()->getRequest()->getActionName() == 'massStatus'
                )
                ||(
                Mage::app()->getRequest()->getControllerName() == 'catalog_product_action_attribute'
                && Mage::app()->getRequest()->getActionName() == 'save'        
                ))
                && Mage::helper('aitpermissions')->isPermissionsEnabled() 
                && Mage::getStoreConfig('admin/su/enable')
                && isset($attrData['status']))
        {
            if ($attrData['status']==3)
            {
                Mage::throwException(Mage::helper('core')->__('This status can\'t use i mass action'));
                return $this;
            }
            $productCollection = Mage::getModel('catalog/product')->getCollection()
                    ->addIdFilter($productIds)
                    ->addAttributeToFilter('status',array('neq' => Aitoc_Aitpermissions_Model_Rewrite_CatalogProductStatus::STATUS_AWAITING));
            
            $productIds = array();
          //  $approveModel = Mage::getModel('aitpermissions/approve');
            
            foreach ($productCollection as $product)
            {
                $productIds[] = $product->getId();
           //     $approveModel->approve($product->getId(),$attrData['status']);
            }
        }
      
        foreach($productIds as $productId)
        {
           Mage::getModel('aitpermissions/approve')->approve($productId,$attrData['status']);
        }
        return parent::updateAttributes($productIds, $attrData, $storeId);
    }
} } 