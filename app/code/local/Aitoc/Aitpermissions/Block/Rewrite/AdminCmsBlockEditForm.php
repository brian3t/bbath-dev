<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCmsBlockEditForm.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ hwwjqhZqcZkwager('c209b3867428f2aa5efab15029a9ea67'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminCmsBlockEditForm extends Mage_Adminhtml_Block_Cms_Block_Edit_Form
{
    protected function _prepareForm()
    {
        parent::_prepareForm();
        
        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            $allowDelete = true;
            // if page is not assigned to any store views but permitted, will allow to delete and disable it
            
            $blockModel = Mage::registry('cms_block');
            
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            if ($blockModel->getStoreId() && is_array($blockModel->getStoreId()))
            {
                foreach ($blockModel->getStoreId() as $blockStoreId)
                {
                    if (!in_array($blockStoreId, $AllowedStoreviews))
                    {
                        $allowDelete = false;
                        break 1;
                    }
                }
            }
            
            if (!$allowDelete)
            {
                $fieldset = $this->getForm()->getElement('base_fieldset');
                $fieldset->removeField('is_active');
            }
        }
        return $this;
    }
} } 