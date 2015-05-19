<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/CatalogProductStatus.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ ThhkUqPUQmBhWZrO('ca57d00eb93f7360d690b0d9dd89fcd7'); ?><?php
class Aitoc_Aitpermissions_Model_Rewrite_CatalogProductStatus extends Mage_Catalog_Model_Product_Status
{
    const STATUS_AWAITING   = 3;
    /**
     * Retrieve option array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        $return = array(
            self::STATUS_ENABLED    => Mage::helper('catalog')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('catalog')->__('Disabled'),
        );
        if (Mage::getStoreConfig('admin/su/enable'))
        {
             $return[self::STATUS_AWAITING]  = Mage::helper('catalog')->__('Awaiting approve');
        }
        return $return;
    }
    
    /**
     * Retrieve option array with empty value
     *
     * @return array
     */
    static public function getAllOptions()
    {
    //    var_dump(Mage::getModel('aitpermissions/approve')->isApproved(Mage::app()->getRequest()->getParam('id')));
        if ((Mage::app()->getRequest()->getControllerName() == 'catalog_product'
                && Mage::app()->getRequest()->getActionName() == 'new'
                && Mage::helper('aitpermissions')->isPermissionsEnabled() 
                && Mage::getStoreConfig('admin/su/enable'))
                ||
                (Mage::app()->getRequest()->getControllerName() == 'catalog_product'
                && Mage::app()->getRequest()->getActionName() == 'edit'
                && Mage::app()->getRequest()->getParam('id')
                && !Mage::getModel('aitpermissions/approve')->isApproved(Mage::app()->getRequest()->getParam('id'))
                && Mage::helper('aitpermissions')->isPermissionsEnabled() 
                && Mage::getStoreConfig('admin/su/enable'))
                )
        {
           $res = array(
            array(
                'value' => self::STATUS_AWAITING,
                'label' => Mage::helper('catalog')->__('Awaiting approve')
            )
           );
        }
        else
        {
            $res = array(
                array(
                    'value' => '',
                    'label' => Mage::helper('catalog')->__('-- Please Select --')
                )
            );
            foreach (self::getOptionArray() as $index => $value) {
                $res[] = array(
                   'value' => $index,
                   'label' => $value
                );
            }
            unset($res[self::STATUS_AWAITING]);
        }
        return $res;
    }
} } 