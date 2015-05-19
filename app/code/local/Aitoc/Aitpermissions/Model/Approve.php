<?php
/**
 * Product:     Advanced Permissions v2.3.11
 * Package:     Aitoc_Aitpermissions_2.3.11_2.0.3_370531
 * Purchase ID: IdE1U6HnoZ1uVsL5XI63QxIw6UiCg3yWRooWxDrbgD
 * Generated:   2012-09-23 04:03:35
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Approve.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ NoIMQcVQABDIdkmt('c37861d535517b4fc7293c5d4428affd'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Model_Approve extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('aitpermissions/approve', 'id');
    }
    public function isApproved($productId)
    {
        $collection = array();
        $collection = $this->getCollection()->loadByProductId($productId);
        foreach ($collection as $item)
        {
          return $item->getIsApproved();
        }
      
        return true;
        
    }
    public function approve($productId,$status=1)
    {
        if ($status == Aitoc_Aitpermissions_Model_Rewrite_CatalogProductStatus::STATUS_AWAITING)
        {
            $status = 0;
        }
        $collection = $this->getCollection()->loadByProductId($productId);
        if ($collection->getSize()>0)
        {
            foreach ($collection as $item)
            {
                    $item->setIsApproved($status)
                    ->save();
            }
        }
        else
        {
            $this->setProductId($productId)->setIsApproved($status)
                    ->save();
        }
     //   echo 13; exit;
        return true;
            
    }
  
} } 