<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminhtmlCatalogProductEditTabInventory.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ hwwjqhZqcZkwager('2d63a09ace03fcbf32415ddaaa12f8e6'); ?><?php
/**
* @copyright  Copyright (c) 2011 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminhtmlCatalogProductEditTabInventory extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Inventory
{
    /**
     *
     * @return Aitoc_Aitpermissions_Helper_Access
     */
    public function getAccessHelper()
    {
        return Mage::helper('aitpermissions/access');
    }
    
    protected function _toHtml()
    {
        $result = parent::_toHtml();
        $js = '';
        if (!$this->getAccessHelper()->isAllowManageEntity('attribute'))
        {
            $js = '<input id="aitpermissions_inventory_manage_stock_default" name="product[stock_data][use_config_manage_stock]" type="hidden" value="1" />';
            $js .= '
<script type="text/javascript">
//<![CDATA[
if (Prototype.Browser.IE)
{
    if (window.addEventListener)
    {
        window.addEventListener("load", disableInventoryInputs, false);
    }
    else
    {
        window.attachEvent("onload", disableInventoryInputs);
    }
}
else
{
    document.observe("dom:loaded", disableInventoryInputs);
}

function disableInventoryInputs()
{
    var elements = $("table_cataloginventory").select(\'input[type="checkbox"],input[type="text"],select\');
    if (elements.size)
    {
        elements.each(function(el) {
           el.disabled = true;
        });
    }
	
	if(typeof($("inventory_use_config_manage_stock")) != "undefined");
	{
	    if($("inventory_use_config_manage_stock").checked)
		{
		    $("aitpermissions_inventory_manage_stock_default").value = 1;
		}
		else
		{
		    $("aitpermissions_inventory_manage_stock_default").value = 0;
		}	    		
	}
}
//]]>
</script>
';
  }
        return $result . $js;
    }
} } 