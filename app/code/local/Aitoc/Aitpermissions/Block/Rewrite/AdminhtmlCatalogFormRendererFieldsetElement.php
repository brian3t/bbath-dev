<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminhtmlCatalogFormRendererFieldsetElement.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ UqqrIpEIRygqPDaW('3fb8b38f4c5e9a95462f1c7d826f82cc'); ?><?php
/**
* @copyright  Copyright (c) 2011 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminhtmlCatalogFormRendererFieldsetElement extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element
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
        
        // Fix bug #0028102
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
    
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = parent::render($element);
        if ($this->getElement() && $this->getElement()->getEntityAttribute() && $this->getElement()->getEntityAttribute()->isScopeGlobal())        
        {
            if (!$this->getAccessHelper()->isAllowManageEntity('attribute') && ('msrp' == $this->getElement()->getHtmlId()))
            {
                 $html .= '
<script type="text/javascript">
//<![CDATA[
if (Prototype.Browser.IE)
{
    if (window.addEventListener)
    {
        window.addEventListener("load", aitpermissions_disable_msrp, false);
    }
    else
    {
        window.attachEvent("onload", aitpermissions_disable_msrp);
    }
}
else
{
    document.observe("dom:loaded", aitpermissions_disable_msrp);
}

function aitpermissions_disable_msrp()
{
    ["click", "focus", "change"].each(function(evt){        
        var msrp = $("msrp");        
        if (msrp && !msrp.disabled)
        {        
            Event.observe(msrp, evt, function(el) {                
                el.disabled = true;
            }.curry(msrp));
        }
    }); 
}
//]]>
</script>
';
            }
            if (!$this->getAccessHelper()->isAllowManageEntity('attribute'))
            {
                $html = str_replace('<script type="text/javascript">toggleValueElements(', '<script type="text/javascript">//toggleValueElements(', $html);
            }
        }
        
        return $html;
    }
} } 