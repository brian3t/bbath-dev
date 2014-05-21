<?php
require_once 'jq-config.php';
// include the jqGrid Class
require_once ABSPATH."php/jqGrid.php";
// include the driver class
require_once ABSPATH."php/jqGridPdo.php";
// Connection to the server
$conn = new PDO(DB_DSN,DB_USER,DB_PASSWORD);
// Tell the db that we use utf-8
$conn->query("SET NAMES utf8");

// Create the jqGrid instance
$grid = new jqGridRender($conn);
// Write the SQL Query
$grid->SelectCommand = 'SELECT id, order_id, tracking_number, service_type, carrier FROM tracking_numbers where processed is null';
// Set the table to where you add the data
$grid->table = 'tracking_numbers';
$grid->setPrimaryKeyId = 'id';
// Set output format to json
$grid->dataType = 'json';
// Let the grid create the model
$grid->setColModel();
// Set the url from where we obtain the data
$grid->setUrl('grid.php');
$grid->addCol(array(
    "name"=>"Actions",
    "formatter"=>"actions",
    "editable"=>false,
    "sortable"=>false,
    "resizable"=>false,
    "fixed"=>true,
    "width"=>60,
    "formatoptions"=>array("keys"=>true)
    ), "first");
$grid->setColProperty('id', array("editable"=>false, "label"=>"ID", "width"=>120));
$grid->setColProperty('order_id', array("label"=>"Order Number", "width"=>200));
$grid->setColProperty('tracking_number', array("label"=>"Tracking Number", "width"=>500));
$grid->setColProperty('service_type', array("label"=>"Service Type", "width"=>500));
$grid->setColProperty('carrier', array("label"=>"Carrier", "width"=>500));

// Set some grid options
$grid->setGridOptions(array(
    "rowNum"=>50,
    "sortname"=>"id",
    "width"=>800,
    "height"=>300
));

// Enable navigator
$grid->navigator = true;
// Enable only deleting
$grid->setNavOptions('navigator', array("excel"=>true,"add"=>true,"edit"=>false,"del"=>false,"view"=>false, "search"=>true), 'editor', array("width"=>500, "height"=>500));
$grid->setNavOptions('add', array("width"=>500, "dataheight"=>"100%"));
// Enjoy
$grid->renderGrid('#grid','#pager',true, null, null, true,true);
$conn = null;
?>
