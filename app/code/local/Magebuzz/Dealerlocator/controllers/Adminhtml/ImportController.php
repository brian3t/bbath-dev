<?php

class Magebuzz_Dealerlocator_Adminhtml_ImportController extends Mage_Adminhtml_Controller_action
{
	public function indexAction() {
		$this->loadLayout()->renderLayout();
	}

	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			if(isset($_FILES['csv_file']['name']) && $_FILES['csv_file']['name'] != '') {
				try {
					$uploader = new Varien_File_Uploader('csv_file');
	           		$uploader->setAllowedExtensions(array('csv'));
					$uploader->setAllowRenameFiles(false);
					$uploader->setFilesDispersion(false);
					$path = Mage::getBaseDir('media').DS.'dealers'. DS ;
					$uploader->save($path, $_FILES['csv_file']['name'] );
					$filepath = $path.$_FILES['csv_file']['name'];
					$handler = new Varien_File_Csv();
					$importData = $handler->getData($filepath);
					$keys = array(
								'dealerlocator_id',
								'title',
								'email',
								'website',
								'phone',
								'postal_code',
								'address',
								'longitude',
								'latitude',
								'status',
								'note'
								);
					$count = count($importData);
					$model = Mage::getModel('dealerlocator/dealerlocator');
					while(--$count>0) {
						$currentData = $importData[$count];
						$data = array_combine($keys, $currentData);
						array_shift($data);
						$model->setData($data)->save();
					}
				} catch (Exception $e) {
					var_dump($e);
		        }
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('dealerlocator')->__('Successfully saved'));
			$this->_redirect('*/adminhtml_dealerlocator/index');
	  	}
	}
}