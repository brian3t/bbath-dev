<?php

class Magebuzz_Dealerlocator_Model_Dealerlocator extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('dealerlocator/dealerlocator');
    }
}