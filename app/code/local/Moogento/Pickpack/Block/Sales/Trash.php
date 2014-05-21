<?php
class Moogento_Pickpack_Block_Sales_Trash extends Mage_Adminhtml_Block_Widget_Grid_Container
{

	public function __construct()
	{
		$this->_controller = 'sales_order';
		$this->_headerText = Mage::helper('sales')->__('Trash Orders');
		parent::__construct();
		$this->_removeButton('add');
	}


	protected function _prepareLayout()
	{
		$this->setChild( 'grid',
		$this->getLayout()->createBlock('pickpack/sales_trash_grid','sales_trash.grid')->setSaveParametersInSession(true) );		
	}
}