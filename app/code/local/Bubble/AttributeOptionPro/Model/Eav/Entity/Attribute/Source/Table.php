<?php
/**
 * @category    Bubble
 * @package     Bubble_AttributeOptionPro
 * @version     1.1.0
 * @copyright   Copyright (c) 2013 BubbleCode (http://shop.bubblecode.net)
 */
class Bubble_AttributeOptionPro_Model_Eav_Entity_Attribute_Source_Table
    extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getOptionImage($value)
    {
        $options = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setPositionOrder('asc')
            ->setAttributeFilter($this->getAttribute()->getId())
            ->setStoreFilter($this->getAttribute()->getStoreId())
            ->load()
            ->toArray();
        foreach ($options['items'] as $item) {
            if ($item['option_id'] == $value) {
                return $item['image'];
            }
        }
        return false;
    }

    public function getOptionAdditionalImage($value)
    {
        $options = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setPositionOrder('asc')
            ->setAttributeFilter($this->getAttribute()->getId())
            ->setStoreFilter($this->getAttribute()->getStoreId())
            ->load()
            ->toArray();
        foreach ($options['items'] as $item) {
            if ($item['option_id'] == $value) {
                return $item['additional_image'];
            }
        }
        return false;
    }
}