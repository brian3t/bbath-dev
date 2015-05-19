<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminhtmlCatalogFormRendererAttributeUrlkey.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ hwwjqhZqcZkwager('37cf3ccafbf5c751db8c4b438a8df12a'); ?><?php
/**
* @copyright  Copyright (c) 2011 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminhtmlCatalogFormRendererAttributeUrlkey extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Attribute_Urlkey
{
    /**
     *
     * @return Aitoc_Aitpermissions_Helper_Access
     */
    public function getAccessHelper()
    {
        return Mage::helper('aitpermissions/access');
    }
    
    public function getElementHtml()
    {
        $result = parent::getElementHtml();
        $element = $this->getElement();
        if ($element && $element->getEntityAttribute() && $element->getEntityAttribute()->isScopeGlobal())
        {
            if (!$this->getAccessHelper()->isAllowManageEntity('attribute'))
            {
                $result = str_replace('type="text"', ' disabled="disabled" type="text"', $result);
            }
        }
        
        return $result;
    }
} } 