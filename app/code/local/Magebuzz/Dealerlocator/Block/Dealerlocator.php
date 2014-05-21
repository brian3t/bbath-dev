<?php
class Magebuzz_Dealerlocator_Block_Dealerlocator extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getDealerlocator()     
     { 
        if (!$this->hasData('dealerlocator')) {
            $this->setData('dealerlocator', Mage::registry('dealerlocator'));
        }
        return $this->getData('dealerlocator');
        
    }
}