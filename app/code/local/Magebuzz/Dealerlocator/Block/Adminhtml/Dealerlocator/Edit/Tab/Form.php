<?php

class Magebuzz_Dealerlocator_Block_Adminhtml_Dealerlocator_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('dealerlocator_form', array('legend'=>Mage::helper('dealerlocator')->__('Dealer information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('dealerlocator')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
	  
	  $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('dealerlocator')->__('Email'),
          'name'      => 'email',
      ));
	  
	  $fieldset->addField('website', 'text', array(
          'label'     => Mage::helper('dealerlocator')->__('Website'),
          'name'      => 'website',
      ));
	  
	  $fieldset->addField('phone', 'text', array(
          'label'     => Mage::helper('dealerlocator')->__('Phone'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'phone',
      ));
	  
	  $fieldset->addField('postal_code', 'text', array(
          'label'     => Mage::helper('dealerlocator')->__('Postal Code'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'postal_code',
      ));
	  
	  $fieldset->addField('address', 'text', array(
          'label'     => Mage::helper('dealerlocator')->__('Address'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'address',
		  'after_element_html' => '<small>Leave 2 fields below empty if you do NOT know exact values.</small>',
      ));
      
      $fieldset->addField('country', 'text', array(
          'label'     => Mage::helper('dealerlocator')->__('Country'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'country',
      ));
	  
	  $fieldset->addField('longitude', 'text', array(
          'label'     => Mage::helper('dealerlocator')->__('Longitude'),
          'name'      => 'longitude',
      ));
	  
	  $fieldset->addField('latitude', 'text', array(
          'label'     => Mage::helper('dealerlocator')->__('Latitude'),
          'name'      => 'latitude',
      ));
	  
	  $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('dealerlocator')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('dealerlocator')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('dealerlocator')->__('Disabled'),
              ),
          ),
      ));
	  
	  $fieldset->addField('note', 'textarea', array(
          'label'     => Mage::helper('dealerlocator')->__('Description/Details'),
          'name'      => 'note',
          'after_element_html' => '<small>Instructions to this store</small>',
        ));
     
      if ( Mage::getSingleton('adminhtml/session')->getDealerlocatorData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getDealerlocatorData());
          Mage::getSingleton('adminhtml/session')->setDealerlocatorData(null);
      } elseif ( Mage::registry('dealerlocator_data') ) {
          $form->setValues(Mage::registry('dealerlocator_data')->getData());
      }
      return parent::_prepareForm();
  }
}