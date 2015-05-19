<?php
/**
 * Emails.php
 * Grid container
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */
class Tegdesign_Emailcollector_Block_Adminhtml_Emails extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'tegdesign_emailcollector';
        $this->_controller = 'adminhtml_emails';
        $this->_headerText = $this->__('Email Collector Emails');

        parent::__construct();
        $this->removeButton('add');
    }
}