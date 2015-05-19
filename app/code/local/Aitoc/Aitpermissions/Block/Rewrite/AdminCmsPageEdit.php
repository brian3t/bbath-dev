<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCmsPageEdit.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ UqqrIpEIRygqPDaW('7f2e10efd6a4e050cd2c11666244b92f'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminCmsPageEdit extends Mage_Adminhtml_Block_Cms_Page_Edit
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            $allowDelete = true;
            $pageModel = Mage::registry('cms_page');
            
            // if page is assigned to store views of allowed website only, will allow to delete it
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();

            if (is_array($pageModel->getStoreId()) && $pageModel->getStoreId()) 
            {
                foreach ($pageModel->getStoreId() as $pageStoreId)
                {
                    if (!in_array($pageStoreId, $AllowedStoreviews))
                    {
                        $allowDelete = false;
                        break 1;
                    }
                }
            }

            if (!$allowDelete)
            {
                $this->_removeButton('delete');
            }
        }
        
        return $this;
    }
} } 