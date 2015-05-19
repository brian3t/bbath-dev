<?php
require_once('../app/Mage.php');
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));


$installer = new Mage_Sales_Model_Mysql4_Setup;
$installer->startSetup();

$table = $installer->getConnection()
->newTable($installer->getTable('tegdesign_emailcollector/emails'))
->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'auto_increment' => true, // added this because of an issue with Magento 1.5 - might not need it
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
    ), 'id')
->addColumn('date_collected', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'date_collected')
->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, '155', array(), 'email')
->addColumn('firstname', Varien_Db_Ddl_Table::TYPE_VARCHAR, '155', array(), 'firstname')
->addColumn('lastname', Varien_Db_Ddl_Table::TYPE_VARCHAR, '155', array(), 'lastname')
->addColumn('coupon', Varien_Db_Ddl_Table::TYPE_VARCHAR, '75', array(), 'coupon')
->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable'  => true), 'store_id')
->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable'  => true), 'website_id')
->addColumn('extra', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(), 'extra');

$installer->getConnection()->createTable($table);
$installer->endSetup();
