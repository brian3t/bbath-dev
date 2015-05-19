<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminReportReviewCustomerGrid.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ hwwjqhZqcZkwager('d884eb7cfc70661c89214313b6371960'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminReportReviewCustomerGrid extends Mage_Adminhtml_Block_Report_Review_Customer_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('reports/review_customer_collection')->joinCustomers();

        if (!Mage::getStoreConfig('admin/general/showallcustomers'))
        {
            if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
            {
                $AllowedWebsites = Mage::helper('aitpermissions')->getAllowedWebsites();

                if ($AllowedWebsites)
                {
                    $collection->getSelect()->joinInner(
                        array('_table_customer' => Mage::getSingleton('core/resource')->getTableName('customer_entity')), 
                        ' _table_customer.entity_id = detail.customer_id ', 
                        array()
                        );
                    $collection->addFieldToFilter('website_id', array('in' => $AllowedWebsites));
                }
            }
        }
        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
} } 