<?php

    require_once(BP . str_replace('/', DS, '/app/etc/magictoolbox/core/load.php'));

    $tool = & $GLOBALS["magictoolbox"]["magicthumb"];

    function magictoolboxCheckPage($page) {
        $cls = array(
            'product' => 'Mage_Catalog_Block_Product_View_Media',
            'category' => 'Mage_Catalog_CategoryController',
            'search' => 'Mage_CatalogSearch_ResultController',
            'advancedsearch' => 'Mage_CatalogSearch_AdvancedController',
            'catalognew' => 'Thirty4_CatalogNew_IndexController'
        );
        $decls = get_declared_classes();
        $declared = in_array($cls[$page], $decls) || in_array(strtolower($cls[$page]), $decls);
        $tool = & $GLOBALS["magictoolbox"]["magicthumb"];
        if($page == 'search' || $page == 'advancedsearch' || $page == 'catalognew') {
            $page = 'category';
        }
        if($declared && !$tool->params->checkValue('use-effect-on-' . $page . '-page', 'No')) {
            return true;
        } else {
            return false;
        }
    }

    if(magictoolboxCheckPage('product') || magictoolboxCheckPage('category') || magictoolboxCheckPage('search') || magictoolboxCheckPage('advancedsearch') || magictoolboxCheckPage('catalognew')) {


        if(magictoolboxCheckPage('product') && $tool->params->checkValue('selector-position', array('left', 'right'))) {
            echo "\n<style type=\"text/css\">\n" .
                 "div.MagicToolboxWrapper {float: left; width: ".($tool->params->getValue('thumb-size'))."px;}\n" .
                 ".product-view .product-img-box .more-views li {clear: both;}\n" .
                 "div.MagicToolboxSelectorsContainer {float: left;}\n" .
                 "</style>\n";
        }


        echo $tool->headers($this->getSkinUrl('js'), $this->getSkinUrl('css'));

        echo '<script type="text/javascript" src="' . $this->getSkinUrl('js') . '/magictoolbox_utils.js"></script>';
        if(magictoolboxCheckPage('product')) {
            $f = 'function(){MagicToolbox_findOption(\'' . strtolower(preg_replace('/\s*,\s*/is', ',', $tool->params->getValue('option-associated-with-images'))) . '\');}';
            echo '<script type="text/javascript">


                    var MagicToolbox_click = \'' . strtolower($tool->params->getValue('swap-image')) . '\';



                    $mjs(window).je1(\'load\', ' . $f . ');


                </script>';
        }

    }

?>
