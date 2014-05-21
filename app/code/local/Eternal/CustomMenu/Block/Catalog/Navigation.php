<?php

class Eternal_CustomMenu_Block_Catalog_Navigation extends Mage_Catalog_Block_Navigation
{
    const CUSTOM_BLOCK_TEMPLATE = "eternal_custommenu_%d";

    private $_productsCount = null;

    public function showHomeLink()
    {
        return Mage::getStoreConfig('eternal_custommenu/general/show_home_link');
    }
    
    public function showHomeIcon()
    {
        return Mage::getStoreConfig('eternal_custommenu/general/show_home_icon');
    }
    
    // get home icon
    public function getHomeIcon() 
    {
        $icon = Mage::getStoreConfig('eternal_custommenu/general/home_icon');
        if ($icon)
            return Mage::getBaseUrl('media') . 'eternal/custommenu/' . $icon;
        return Mage::getBaseUrl('media') . 'eternal/custommenu/icon-home.png';
    }
    
    // get custom block
    public function getCustomBlock() {
        $block_id = Mage::getStoreConfig('eternal_custommenu/custom/block');
        return $block_id;
    }
    
    // get custom links
    public function getCustomLinks() {
        $block_id = Mage::getStoreConfig('eternal_custommenu/custom/links');
        return $block_id;
    }
    
    // get custom mobile links
    public function getCustomMobileLinks() {
        $block_id = Mage::getStoreConfig('eternal_custommenu/custom/mobile_links');
        return $block_id;
    }

    public function drawCustomMenuMobileItem($category, $level = 0, $last = false)
    {
        if (!$category->getIsActive()) return '';
        $html = array();
        $id = $category->getId();
        // --- Sub Categories ---
        $activeChildren = $this->_getActiveChildren($category, $level);
        // --- class for active category ---
        $active = ''; if ($this->isCategoryActive($category)) $active = ' act';
        $hasSubMenu = count($activeChildren);
        $catUrl = $this->getCategoryUrl($category);
        $html[] = '<div id="menu-mobile-' . $id . '" class="menu-mobile level0' . $active . '">';
        $html[] = '<div class="parentMenu">';
        // --- Top Menu Item ---
        $html[] = '<a href="' . $catUrl .'">';
        $name = $this->escapeHtml($category->getName());
        if (Mage::getStoreConfig('eternal_custommenu/general/non_breaking_space')) {
            $name = str_replace(' ', '&nbsp;', $name);
        }
        $html[] = '<span>' . $name . '</span>';
        $html[] = '</a>';
        if ($hasSubMenu) {
            $html[] = '<span class="button" rel="submenu-mobile-' . $id . '" onclick="eternalSubMenuToggle(this, \'menu-mobile-' . $id . '\', \'submenu-mobile-' . $id . '\');">&nbsp;</span>';
        }
        $html[] = '</div>';
        // --- Add Popup block (hidden) ---
        if ($hasSubMenu) {
            // --- draw Sub Categories ---
            $html[] = '<div id="submenu-mobile-' . $id . '" rel="level' . $level . '" class="eternal-custom-menu-submenu" style="display: none;">';
            $html[] = $this->drawMobileMenuItem($activeChildren);
            $html[] = '<div class="clearBoth"></div>';
            $html[] = '</div>';
        }
        $html[] = '</div>';
        $html = implode("\n", $html);
        return $html;
    }

    public function drawCustomMenuItem($category, $level = 0, $last = false)
    {
        if (!$category->getIsActive()) return '';
        $html = array();
        $id = $category->getId();
        // --- Static Block ---
        $blockId = sprintf(self::CUSTOM_BLOCK_TEMPLATE, $id); // --- static block key
        #Mage::log($blockId);
        $collection = Mage::getModel('cms/block')->getCollection()
            ->addFieldToFilter('identifier', array(array('like' => $blockId . '_w%'), array('eq' => $blockId)))
            ->addFieldToFilter('is_active', 1);
        $blockId = $collection->getFirstItem()->getIdentifier();
        #Mage::log($blockId);
        $blockHtml = $this->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
        // --- Sub Categories ---
        $activeChildren = $this->_getActiveChildren($category, $level);
        // --- class for active category ---
        $active = ''; if ($this->isCategoryActive($category)) $active = ' act';
        // --- Popup functions for show ---
        $drawPopup = ($blockHtml || count($activeChildren));
        if ($drawPopup) {
            $html[] = '<div id="menu' . $id . '" class="menu' . $active . '" onmouseover="eternalShowMenuPopup(this, event, \'popup' . $id . '\');" onmouseout="eternalHideMenuPopup(this, event, \'popup' . $id . '\', \'menu' . $id . '\')">';
        } else {
            $html[] = '<div id="menu' . $id . '" class="menu' . $active . '">';
        }
        // --- Top Menu Item ---
        $html[] = '<div class="parentMenu">';
        if ($level == 0 && $drawPopup) {
            $html[] = '<a href="javascript:void(0);" rel="'.$this->getCategoryUrl($category).'">';
        } else {
            $html[] = '<a href="'.$this->getCategoryUrl($category).'">';
        }
        $name = $this->escapeHtml($category->getName());
        if (Mage::getStoreConfig('eternal_custommenu/general/non_breaking_space')) {
            $name = str_replace(' ', '&nbsp;', $name);
        }
        $html[] = '<span>' . $name . '</span>';
        $html[] = '</a>';
        $html[] = '</div>';
        $html[] = '</div>';
        // --- Add Popup block (hidden) ---
        if ($drawPopup) {
            // --- Popup function for hide ---
            $html[] = '<div id="popup' . $id . '" class="eternal-custom-menu-popup" onmouseout="eternalHideMenuPopup(this, event, \'popup' . $id . '\', \'menu' . $id . '\')" onmouseover="eternalPopupOver(this, event, \'popup' . $id . '\', \'menu' . $id . '\')">';
            // --- draw Sub Categories ---
            if (count($activeChildren)) {
                $columns = (int)Mage::getStoreConfig('eternal_custommenu/columns/count');
                $html[] = '<div class="block1">';
                $html[] = $this->drawColumns($activeChildren, $columns);
                $html[] = '<div class="clearBoth"></div>';
                $html[] = '</div>';
            }
            // --- draw Custom User Block ---
            if ($blockHtml) {
                $html[] = '<div id="' . $blockId . '" class="block2">';
                $html[] = $blockHtml;
                $html[] = '</div>';
            }
            $html[] = '</div>';
        }
        $html = implode("\n", $html);
        return $html;
    }
    
    public function drawCustomMenuBlock()
    {
        $blockIdsString = $this->getCustomBlock();
        $blockIds = explode(",", str_replace(" ", "", $blockIdsString));
        $block_html = '';
        foreach ($blockIds as $blockId) {
            if (!$blockId)
                continue;
        
        $html = array();
        $block = Mage::getModel('cms/block')->setStoreId(Mage::app()->getStore()->getId())->load($blockId);
            if (!$block) continue;
        
        $blockTitle = $block->getTitle();
        $blockContent = $this->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();        
        
        if (!$blockTitle || !$blockContent) continue;
        
        $html[] = '<div id="menu' . $blockId . '" class="menu' . $active . '" onmouseover="eternalShowMenuPopup(this, event, \'popup' . $blockId . '\');" onmouseout="eternalHideMenuPopup(this, event, \'popup' . $blockId . '\', \'menu' . $blockId . '\')">';
        $html[] = '<div class="parentMenu">';
        $html[] = '<a href="javascript:void(0);" rel="#">';
        if (Mage::getStoreConfig('eternal_custommenu/general/non_breaking_space')) {
            $blockTitle = str_replace(' ', '&nbsp;', $blockTitle);
        }
        $html[] = '<span>' . $blockTitle . '</span>';
        $html[] = '</a>';
        $html[] = '</div>';
        $html[] = '</div>';
        // --- Popup function for hide ---
        $html[] = '<div id="popup' . $blockId . '" class="eternal-custom-menu-popup" onmouseout="eternalHideMenuPopup(this, event, \'popup' . $blockId . '\', \'menu' . $blockId . '\')" onmouseover="eternalPopupOver(this, event, \'popup' . $blockId . '\', \'menu' . $blockId . '\')">';
        if ($blockContent) {
            $html[] = '<div id="' . $blockId . '" class="block2">';
            $html[] = $blockContent;
            $html[] = '</div>';
        }
        $html[] = '</div>';
        $html = implode("\n", $html);
            $block_html .= $html;
        }
        return $block_html;
    }
    
    public function drawCustomMenuLinks()
    {
        $blockId = $this->getCustomLinks();
        if (!$blockId) return;
        
        $html = array();
        $block = Mage::getModel('cms/block')->setStoreId(Mage::app()->getStore()->getId())->load($blockId);
        if (!$block) return;
        
        return $this->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
    }
    
    public function drawCustomMobileMenuLinks()
    {
        $blockId = $this->getCustomMobileLinks();
        if (!$blockId) return;
        
        $html = array();
        $block = Mage::getModel('cms/block')->setStoreId(Mage::app()->getStore()->getId())->load($blockId);
        if (!$block) return;
        
        return $this->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
    }

    public function drawCustomNarrowMenuItem($category, $level = 0, $last = false)
    {
        if (!$category->getIsActive()) return '';
        $html = array();
        $id = $category->getId();
        // --- Sub Categories ---
        $activeChildren = $this->_getActiveChildren($category, $level);
        // --- class for active category ---
        $active = ''; 
        if ($this->isCategoryActive($category)) $active = ' act';
        $hasChilds = count($activeChildren);
        if ($level == 0)
            $active .= ' level-top';
        if ($hasChilds) {
            $html[] = '<li class="level' . $level . " " . $active . ' parent">';
        } else {
            $html[] = '<li class="level' . $level . " " . $active . '">';
        }
        // --- Top Menu Item ---
        if ($level == 0) {
            $html[] = '<a class="level-top" href="'.$this->getCategoryUrl($category).'">';
        } else {
            $html[] = '<a href="'.$this->getCategoryUrl($category).'">';
        }
        $name = $this->escapeHtml($category->getName());
        if (Mage::getStoreConfig('eternal_custommenu/general/non_breaking_space')) {
            $name = str_replace(' ', '&nbsp;', $name);
        }
        $html[] = '<span>' . $name . '</span>';
        $html[] = '</a>';
        // --- Add child categories (hidden) ---
        if ($hasChilds) {
            $html[] = '<ul class="level'.$level.'">';
            foreach ($activeChildren as $child) {
                $html[] = $this->drawCustomNarrowMenuItem($child, $level + 1);
            }
            $html[] = '</ul>';
        }
        $html[] = '</li>';
        $html = implode("\n", $html);
        return $html;
    }

    public function drawMobileMenuItem($children, $level = 1)
    {
        $keyCurrent = $this->getCurrentCategory()->getId();
        $html = '';
        foreach ($children as $child) {
            if (is_object($child) && $child->getIsActive()) {
                // --- class for active category ---
                $active = '';
                $id = $child->getId();
                $activeChildren = $this->_getActiveChildren($child, $level);
                if ($this->isCategoryActive($child)) {
                    $active = ' actParent';
                    if ($id == $keyCurrent) $active = ' act';
                }
                $html.= '<div id="menu-mobile-' . $id . '" class="itemMenu level' . $level . $active . '">';
                // --- format category name ---
                $name = $this->escapeHtml($child->getName());
                if (Mage::getStoreConfig('eternal_custommenu/general/non_breaking_space')) $name = str_replace(' ', '&nbsp;', $name);
                $html.= '<div class="parentMenu">';
                $html.= '<a class="itemMenuName level' . $level . '" href="' . $this->getCategoryUrl($child) . '"><span>' . $name . '</span></a>';
                if (count($activeChildren) > 0) {
                    $html.= '<span class="button" rel="submenu-mobile-' . $id . '" onclick="eternalSubMenuToggle(this, \'menu-mobile-' . $id . '\', \'submenu-mobile-' . $id . '\');">&nbsp;</span>';
                }
                $html.= '</div>';
                if (count($activeChildren) > 0) {
                    $html.= '<div id="submenu-mobile-' . $id . '" rel="level' . $level . '" class="eternal-custom-menu-submenu level' . $level . '" style="display: none;">';
                    $html.= $this->drawMobileMenuItem($activeChildren, $level + 1);
                    $html.= '<div class="clearBoth"></div>';
                    $html.= '</div>';
                }
                $html.= '</div>';
            }
        }
        return $html;
    }

    public function drawMenuItem($children, $level = 1)
    {
        $html = '<div class="itemMenu level' . $level . '">';
        $keyCurrent = $this->getCurrentCategory()->getId();
        foreach ($children as $child)
        {
            if (is_object($child) && $child->getIsActive())
            {
                // --- class for active category ---
                $active = '';
                if ($this->isCategoryActive($child))
                {
                    $active = ' actParent';
                    if ($child->getId() == $keyCurrent) $active = ' act';
                }
                // --- format category name ---
                $name = $this->escapeHtml($child->getName());
                if (Mage::getStoreConfig('eternal_custommenu/general/non_breaking_space'))
                    $name = str_replace(' ', '&nbsp;', $name);
                $html.= '<a class="itemMenuName level' . $level . $active . '" href="' . $this->getCategoryUrl($child) . '"><span>' . $name . '</span></a>';
                $activeChildren = $this->_getActiveChildren($child, $level);
                if (count($activeChildren) > 0)
                {
                    $html.= '<div class="itemSubMenu level' . $level . '">';
                    $html.= $this->drawMenuItem($activeChildren, $level + 1);
                    $html.= '</div>';
                }
            }
        }
        $html.= '</div>';
        return $html;
    }

    public function drawColumns($children, $columns = 1)
    {
        $html = '';
        // --- explode by columns ---
        if ($columns < 1) $columns = 1;
        $chunks = $this->_explodeByColumns($children, $columns);
        // --- draw columns ---
        $lastColumnNumber = count($chunks);
        $i = 1;
        foreach ($chunks as $key => $value)
        {
            if (!count($value)) continue;
            $class = '';
            if ($i == 1) $class.= ' first';
            if ($i == $lastColumnNumber) $class.= ' last';
            if ($i % 2) $class.= ' odd'; else $class.= ' even';
            $html.= '<div class="column' . $class . '">';
            $html.= $this->drawMenuItem($value, 1);
            $html.= '</div>';
            $i++;
        }
        return $html;
    }

    protected function _getActiveChildren($parent, $level)
    {
        $activeChildren = array();
        // --- check level ---
        $maxLevel = (int)Mage::getStoreConfig('eternal_custommenu/general/max_level');
        if ($maxLevel > 0)
        {
            if ($level >= ($maxLevel - 1)) return $activeChildren;
        }
        // --- / check level ---
        if (Mage::helper('catalog/category_flat')->isEnabled())
        {
            $children = $parent->getChildrenNodes();
            $childrenCount = count($children);
        }
        else
        {
            $children = $parent->getChildren();
            $childrenCount = $children->count();
        }
        $hasChildren = $children && $childrenCount;
        if ($hasChildren)
        {
            foreach ($children as $child)
            {
                if ($this->_isCategoryDisplayed($child))
                {
                    array_push($activeChildren, $child);
                }
            }
        }
        return $activeChildren;
    }

    private function _isCategoryDisplayed(&$child)
    {
        if (!$child->getIsActive()) return false;
        // === check products count ===
        // --- get collection info ---
        if (!Mage::getStoreConfig('eternal_custommenu/general/display_empty_categories'))
        {
            $data = $this->_getProductsCountData();
            // --- check by id ---
            $id = $child->getId();
            #Mage::log($id); Mage::log($data);
            if (!isset($data[$id]) || !$data[$id]['product_count']) return false;
        }
        // === / check products count ===
        return true;
    }

    private function _getProductsCountData()
    {
        if (is_null($this->_productsCount))
        {
            $collection = Mage::getModel('catalog/category')->getCollection();
            $storeId = Mage::app()->getStore()->getId();
            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setProductStoreId($storeId)
                ->setLoadProductCount(true)
                ->setStoreId($storeId);
            $productsCount = array();
            foreach($collection as $cat)
            {
                $productsCount[$cat->getId()] = array(
                    'name' => $cat->getName(),
                    'product_count' => $cat->getProductCount(),
                );
            }
            #Mage::log($productsCount);
            $this->_productsCount = $productsCount;
        }
        return $this->_productsCount;
    }

    private function _explodeByColumns($target, $num)
    {
        if ((int)Mage::getStoreConfig('eternal_custommenu/columns/divided_horizontally')) {
            $target = self::_explodeArrayByColumnsHorisontal($target, $num);
        } else {
            $target = self::_explodeArrayByColumnsVertical($target, $num);
        }
        #return $target;
        if ((int)Mage::getStoreConfig('eternal_custommenu/columns/integrate') && count($target))
        {
            // --- combine consistently numerically small column ---
            // --- 1. calc length of each column ---
            $max = 0; $columnsLength = array();
            foreach ($target as $key => $child)
            {
                $count = 0;
                $this->_countChild($child, 1, $count);
                if ($max < $count) $max = $count;
                $columnsLength[$key] = $count;
            }
            // --- 2. merge small columns with next ---
            $xColumns = array(); $column = array(); $cnt = 0;
            $xColumnsLength = array(); $k = 0;
            foreach ($columnsLength as $key => $count)
            {
                $cnt+= $count;
                if ($cnt > $max && count($column))
                {
                    $xColumns[$k] = $column;
                    $xColumnsLength[$k] = $cnt - $count;
                    $k++; $column = array(); $cnt = $count;
                }
                $column = array_merge($column, $target[$key]);
            }
            $xColumns[$k] = $column;
            $xColumnsLength[$k] = $cnt - $count;
            // --- 3. integrate columns of one element ---
            $target = $xColumns; $xColumns = array(); $nextKey = -1;
            if ($max > 1 && count($target) > 1)
            {
                foreach($target as $key => $column)
                {
                    if ($key == $nextKey) continue;
                    if ($xColumnsLength[$key] == 1)
                    {
                        // --- merge with next column ---
                        $nextKey = $key + 1;
                        if (isset($target[$nextKey]) && count($target[$nextKey]))
                        {
                            $xColumns[] = array_merge($column, $target[$nextKey]);
                            continue;
                        }
                    }
                    $xColumns[] = $column;
                }
                $target = $xColumns;
            }
        }
        return $target;
    }

    private function _countChild($children, $level, &$count)
    {
        foreach ($children as $child)
        {
            if ($child->getIsActive())
            {
                $count++; $activeChildren = $this->_getActiveChildren($child, $level);
                if (count($activeChildren) > 0) $this->_countChild($activeChildren, $level + 1, $count);
            }
        }
    }

    private static function _explodeArrayByColumnsHorisontal($list, $num)
    {
        if ($num <= 0) return array($list);
        $partition = array();
        $partition = array_pad($partition, $num, array());
        $i = 0;
        foreach ($list as $key => $value) {
            $partition[$i][$key] = $value;
            if (++$i == $num) $i = 0;
        }
        return $partition;
    }

    private static function _explodeArrayByColumnsVertical($list, $num)
    {
        if ($num <= 0) return array($list);
        $listlen = count($list);
        $partlen = floor($listlen / $num);
        $partrem = $listlen % $num;
        $partition = array();
        $mark = 0;
        for ($column = 0; $column < $num; $column++) {
            $incr = ($column < $partrem) ? $partlen + 1 : $partlen;
            $partition[$column] = array_slice($list, $mark, $incr);
            $mark += $incr;
        }
        return $partition;
    }
    
    public  $_columnOutput;
    public  $_type;
    public  $_counter;

    /**
     * Render category to html
     *
     * @param Mage_Catalog_Model_Category $category
     * @param int Nesting level number
     * @param boolean Whether ot not this item is last, affects list item class
     * @param boolean Whether ot not this item is first, affects list item class
     * @param boolean Whether ot not this item is outermost, affects list item class
     * @param string Extra class of outermost list items
     * @param string If specified wraps children list in div with this class
     * @param boolean Whether ot not to add on* attributes to list item
     * @return string
     */
    public function _renderCategoryMenuItemHtml($category,
                                                $level = 0,
                                                $isLast = false,
                                                $isFirst = false,
                                                $isOutermost = false,
                                                $outermostItemClass = '',
                                                $childrenWrapClass = '',
                                                $noEventAttributes = false)
    {
        if (!$category->getIsActive()) {
            return '';
        }
        $html = array();

        // get all children
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $children = (array)$category->getChildrenNodes();
            $childrenCount = count($children);
        } else {
            $children = $category->getChildren();
            $childrenCount = $children->count();
        }
        $hasChildren = ($children && $childrenCount);

        // select active children
        $activeChildren = array();
        foreach ($children as $child) {
            if ($child->getIsActive()) {
                $activeChildren[] = $child;
            }
        }
        $activeChildrenCount = count($activeChildren);
        $hasActiveChildren = ($activeChildrenCount > 0);

        // prepare list item html classes
        $classes = array();
        //$classes[] = 'level' . $level;
        //$classes[] = 'nav-' . $this->_getItemPosition($level);
        if ($this->isCategoryActive($category)) {
            $classes[] = 'active';
        }
        $linkClass = '';
        /*
if ($isOutermost && $outermostItemClass) {
            $classes[] = $outermostItemClass;
            $linkClass = ' class="'.$outermostItemClass.'"';
        }
        if ($isFirst) {
            $classes[] = 'first';
        }
        if ($isLast) {
            $classes[] = 'last';
        }
*/
        if ($hasActiveChildren) {
            $classes[] = 'dropdown';
        }

        if($this->_type=='leftmenu')
        {
            if ($this->isCategoryActive($category)) {
                $classes[] = 'current';
            }
        }elseif($this->_type=='megamenu' && $level==1)
        {
            $classes[]='title';
        }

        // prepare list item attributes
        $attributes = array();
        if (count($classes) > 0) {
            $attributes['class'] = implode(' ', $classes);
        }
        if ($hasActiveChildren && !$noEventAttributes) {
            $attributes['onmouseover'] = 'toggleMenu(this,1)';
            $attributes['onmouseout'] = 'toggleMenu(this,0)';
        }

        if($this->_type=='megamenu' && ($level==0 or $level==1)) {
            $category_data=Mage::getModel('catalog/category')->load($category->getId());
        }

        // assemble list item with attributes

        $htmlLi = '<li';
        foreach ($attributes as $attrName => $attrValue) {
            $htmlLi .= ' ' . $attrName . '="' . str_replace('"', '\"', $attrValue) . '"';
        }
        $htmlLi .= '>';
        $html[] = $htmlLi;


        if($this->_type=='leftmenu')
        {
            $flag='closed';

            if ($this->isCategoryActive($category)) {
                $flag = 'opened';
            }
            $html[] = '<label class="tree-toggler nav-header '.$flag.'">
                                        <a href="'.$this->getCategoryUrl($category).'">'. $this->escapeHtml($category->getName()).'</a></label>';
        }else{
        	if ($hasActiveChildren) {
	        	$dropdown = 'class="dropdown-toggle" data-toggle="dropdown"';
        	}
            $html[] = '<a ' . $dropdown . ' href="'.$this->getCategoryUrl($category).'"'.$linkClass.'>';
            if($this->_type=='megamenu' && $level!='0')$html[] = $this->escapeHtml($category->getName());
            else $html[] = $this->escapeHtml($category->getName());
            $html[] = '</a>';
            if($this->_type=='megamenu' && $level=='1')
            {
                $label= $category_data->getBs_category_lable();
                if(!empty($label))$html[]='<span class="hot"> '.$label.' </span>';
            }

        }

        // render children
        $htmlChildren = '';
        $j = 0;

        if($this->_type=='megamenu')
        {
            foreach ($activeChildren as $child) {
                $itemHtml = $this->_renderCategoryMenuItemHtml(
                    $child,
                    ($level + 1),
                    ($j == $activeChildrenCount - 1),
                    ($j == 0),
                    false,
                    $outermostItemClass,
                    $childrenWrapClass,
                    $noEventAttributes
                );
                $j++;
                if ($level==0 && $this->_type=='megamenu')
                {
                    $this->_columnOutput[] = $itemHtml;

                }
                else
                {
                    $htmlChildren.=$itemHtml;
                }
            }
        }
        else
        {
            foreach ($activeChildren as $child) {
                $htmlChildren .= $this->_renderCategoryMenuItemHtml(
                    $child,
                    ($level + 1),
                    ($j == $activeChildrenCount - 1),
                    ($j == 0),
                    false,
                    $outermostItemClass,
                    $childrenWrapClass,
                    $noEventAttributes
                );
                $j++;

            }
        }


        if($this->_type=='megamenu' && $level==0)
        {


            $descriptionTop=$category_data->getBs_top_html();
            $descriptionTop = Mage::helper('cms')->getBlockTemplateProcessor()->filter($this->helper('catalog/output')->categoryAttribute($category, $descriptionTop, 'bs_top_html'));
            $descriptionBtm = $category_data->getBs_btm_html();
            $descriptionBtm = Mage::helper('cms')->getBlockTemplateProcessor()->filter($this->helper('catalog/output')->categoryAttribute($category, $descriptionBtm, 'bs_btm_html'));
            $description = Mage::getModel('catalog/category')->load($category->getId())->getMenutopdescription1();
            $description = Mage::helper('cms')->getBlockTemplateProcessor()->filter($this->helper('catalog/output')->categoryAttribute($category, $description, 'menutopdescription1'));
            $cols = $category_data->getBs_count_columns();
            if(empty($cols))$cols=6;
            if($cols>6)$cols=6;
            if($cols<1)$cols=1;
            if(!empty($description) && $cols>4)$cols=4;

            if($hasActiveChildren || !empty($descriptionTop) || !empty($descriptionBtm) || !empty($description))
            {
                $htmlChildren .= '<li><ul class="shadow">';
                if(!empty($descriptionTop)) $htmlChildren .='<li class="row_top"><span class="inside">'. $descriptionTop.'</span></li>';
                $htmlChildren .= '<li class="row_middle">
                                    <ul class="rows_outer">';


                $c=0;
                $s=0;


                foreach ($this->_columnOutput as $item)
                {

                    $c++;$s++;
                    if($c==1)
                    {
                        $htmlChildren .= '<li><ul class="menu_row">';//begin row
                    }


                    $htmlChildren .= '<li class="col"><ul>'.$item.'</ul></li>';

                    if($c==$cols or $s==count($this->_columnOutput))
                    {
                        $htmlChildren .= '</ul></li>'; //end row
                        $c=0;
                    }
                }

                $htmlChildren .= '          </ul>';

                /*custom block html*/
                if (!empty($description))
                {
                    $htmlChildren.='<div class="custom">'.$description.'</div>';
                }
                /*end block html*/
                $htmlChildren .= '</li>';
                if(!empty($descriptionBtm)) $htmlChildren .= '<li class="row_bot"><span class=" inside">'.$descriptionBtm.'</span></li>';
                $htmlChildren .= '</ul>';
                $this->_columnOutput=array();
            }


        }

        /* }*/

        if (!empty($htmlChildren)) {
            if ($childrenWrapClass) {
                $html[] = '<div class="' . $childrenWrapClass . '">';
            }


            if($this->_type=='mobile')
            {
                $this->_counter++;
                $html[] = '<a class="icon-collapse" href="#level'.$this->_counter.'" title="" data-toggle="collapse" ><i class="icon-down pull-right"></i></a>';
                $html[] = '<ul class="collapse in level' . $level . '" id="level'.$this->_counter.'">';
            }
            elseif($this->_type=='leftmenu')
            {
                $html[] = '<ul class="nav nav-list tree">';
            }elseif($this->_type=='megamenu' && $level==1)
            {
                $html[] = '</li>';
            }
            else{
                $html[] = '<ul class="dropdown-menu level' . $level . '">';
            }

            $html[] = $htmlChildren;

            if($this->_type=='megamenu' && $level==1)
            {
                $html[] = '';
            }else
            {
                $html[] = '</ul>';
            }

            if ($childrenWrapClass) {
                $html[] = '</div>';
            }
        }

        if(!($this->_type=='megamenu' && $level==1))$html[] = '</li>';

        $html = implode("\n", $html);
        return $html;
    }


    /**
     * Render categories menu in HTML
     *
     * @param int Level number for list item class to start from
     * @param string Extra class of outermost list items
     * @param string If specified wraps children list in div with this class
     * @return string
     */
    public function renderCategoriesMenuHtml($level = 0,
                                             $outermostItemClass = '',
                                             $childrenWrapClass = '',
                                             $type='simple')
    {

        $this->_type=$type;
        $this->_counter=1;

        $activeCategories = array();
        foreach ($this->getStoreCategories() as $child) {
            if ($child->getIsActive()) {
                $activeCategories[] = $child;
            }
        }
        $activeCategoriesCount = count($activeCategories);
        $hasActiveCategoriesCount = ($activeCategoriesCount > 0);

        if (!$hasActiveCategoriesCount) {
            return '';
        }

        $html = '';
        $j = 0;
        foreach ($activeCategories as $category) {
            $html .= $this->_renderCategoryMenuItemHtml(
                $category,
                $level,
                ($j == $activeCategoriesCount - 1),
                ($j == 0),
                true,
                $outermostItemClass,
                $childrenWrapClass,
                true
            );
            $j++;
        }

        return $html;
    }
}
