<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/BundleAdminhtmlCatalogProductEditTabAttributesExtend.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ hwwjqhZqcZkwager('64b169cb12defe3222ad70346616213a'); ?><?php
/**
* @copyright  Copyright (c) 2011 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_BundleAdminhtmlCatalogProductEditTabAttributesExtend extends Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Attributes_Extend
{
    /**
     *
     * @return Aitoc_Aitpermissions_Helper_Access
     */
    public function getAccessHelper()
    {
        return Mage::helper('aitpermissions/access');
    }
    
    public function checkFieldDisable()
    {        
        $result = parent::checkFieldDisable();
		
		//Fix bug #0028283
		$superGlobalAttribute = array('sku','weight');
		
		// Fix bug #0028254
        $currentProduct = Mage::registry('current_product');
        $bAllow = !$currentProduct || !$currentProduct->getId() || !$currentProduct->getSku();
        // End fix #0028254
        
        if ( $bAllow && $this->getElement() && $this->getElement()->getEntityAttribute() && in_array($this->getElement()->getEntityAttribute()->getAttributeCode(),$superGlobalAttribute))
        {
            return $result;
        }
        // End fix
		
        if ($this->getElement() && $this->getElement()->getEntityAttribute() && $this->getElement()->getEntityAttribute()->isScopeGlobal())        
        {            
            if (!$this->getAccessHelper()->isAllowManageEntity('attribute'))
            {                
                $this->getElement()->setDisabled(true);
                $this->getElement()->setReadonly(true);
                $afterHtml = $this->getElement()->getAfterElementHtml();
                if (false !== strpos($afterHtml, 'type="checkbox"'))
                {
                    $afterHtml = str_replace('type="checkbox"', 'type="checkbox" disabled="disabled"', $afterHtml);
                    $this->getElement()->setAfterElementHtml($afterHtml);
                }
            }            
        }
        
        return $result;
    }
} } 