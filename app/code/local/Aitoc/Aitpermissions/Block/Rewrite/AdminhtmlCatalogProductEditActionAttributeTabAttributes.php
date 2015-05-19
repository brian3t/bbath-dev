<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminhtmlCatalogProductEditActionAttributeTabAttributes.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ RwCyThWTNMeCOakV('c5c2f7ee96bdb004f5a14bf1e7623e63'); ?><?php
/**
* @copyright  Copyright (c) 2011 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminhtmlCatalogProductEditActionAttributeTabAttributes extends Mage_Adminhtml_Block_Catalog_Product_Edit_Action_Attribute_Tab_Attributes
{
    /**
     *
     * @return Aitoc_Aitpermissions_Helper_Access
     */
    public function getAccessHelper()
    {
        return Mage::helper('aitpermissions/access');
    }
    
    protected function _getAdditionalElementHtml($element)
    {
        $result = parent::_getAdditionalElementHtml($element);
        if ($element && $element->getEntityAttribute() && $element->getEntityAttribute()->isScopeGlobal())
        {
            if (!$this->getAccessHelper()->isAllowManageEntity('attribute'))
            {
                $result = str_replace('<input type="checkbox"', '<input type="checkbox" disabled="disabled"', $result);
            }
        }
        
        return $result;
    }
} } 