<?php
/**
 * Coupons.php
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */
class Tegdesign_Emailcollector_Model_System_Config_Source_Staticblocks extends Mage_Core_Model_Abstract
{
	protected $_options;
	
    public function toOptionArray()
    {

        if (!$this->_options) {
	        
            $this->_options = Mage::getResourceModel('cms/block_collection')
                ->load()
                ->toOptionArray();
            $this->_options[0] = 'None'; 
        }
        return $this->_options;
    }

}