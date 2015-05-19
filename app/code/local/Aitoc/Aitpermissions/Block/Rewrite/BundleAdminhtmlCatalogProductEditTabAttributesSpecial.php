<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/BundleAdminhtmlCatalogProductEditTabAttributesSpecial.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ IppacokcTkMpEjZP('40059231c5d9a1608d538775e2d9598b'); ?><?php
/**
* @copyright  Copyright (c) 2011 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_BundleAdminhtmlCatalogProductEditTabAttributesSpecial extends Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Attributes_Special
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