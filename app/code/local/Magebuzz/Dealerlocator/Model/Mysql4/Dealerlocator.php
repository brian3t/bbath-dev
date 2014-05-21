<?php

class Magebuzz_Dealerlocator_Model_Mysql4_Dealerlocator extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the dealerlocator_id refers to the key field in your database table.
        $this->_init('dealerlocator/dealerlocator', 'dealerlocator_id');
    }
}