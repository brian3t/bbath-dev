<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/CatalogModelResourceEavMysql4CategoryTree.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ UqqrIpEIRygqPDaW('de40c2bed4d65161465e1ee1b429cf9d'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Model_Rewrite_CatalogModelResourceEavMysql4CategoryTree extends Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree
{
    protected function _updateAnchorProductCount(&$data)
    {
        foreach ($data as $key => $row) {
        	if (isset($row['is_anchor']))
        	{
	            if (0 === (int)$row['is_anchor']) {
	                $data[$key]['product_count'] = $row['self_product_count'];
	            }
        	}
        }
    }
} } 