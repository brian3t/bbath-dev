<?php
/**
 * Collection.php
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */
class Tegdesign_Emailcollector_Model_Mysql4_Emails_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {   
        $this->_init('tegdesign_emailcollector/emails');
    }   
}