<?php
/**
 * EmailsController.php
 * Admin controller responsible for grid actions
 *
 * @category    Tegdesign
 * @package     Emailcollector
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 *
 */
class Tegdesign_Emailcollector_Adminhtml_EmailsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {   
        $this->_initAction()->renderLayout();

        // $this->loadLayout();
        // $this->getLayout()->createBlock('tegdesign_emailcollector/adminhtml_emails');
        // $this->renderLayout();
    }   
     
    public function exportCsvAction()
    {
        $fileName   = 'emails.csv';
        $grid       = $this->getLayout()->createBlock('tegdesign_emailcollector/adminhtml_emails_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsv());
    }

    public function exportExcelAction()
    {
        $fileName   = 'emails.xls';
        $grid       = $this->getLayout()->createBlock('tegdesign_emailcollector/adminhtml_emails_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
 
    public function messageAction()
    {
        $data = Mage::getModel('tegdesign_emailcollector/emails')->load($this->getRequest()->getParam('id'));
        echo $data->getContent();
    }

    public function deleteAction() {
    
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('tegdesign_emailcollector/emails');
				$model->setId($this->getRequest()->getParam('id'))->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Record deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() 
    {

        $ids = $this->getRequest()->getParam('emailcollector_emails');

        if(!is_array($ids)) {

            Mage::getSingleton('adminhtml/session')->addError($this->__('Nothing selected'));

        } else {

            try {

                foreach ($ids as $id) {
                    $model = Mage::getModel('tegdesign_emailcollector/emails')->load($id);
                    $model->delete();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Total of %d record(s) were successfully deleted', count($ids)));
            
            } catch (Exception $e) {

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            }
        }

        $this->_redirect('*/*/index');

    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('customer/tegdesign_emailcollector_emails')
            ->_title($this->__('Customers'))->_title($this->__('Email Collector Emails'))
            ->_addBreadcrumb($this->__('Customers'), $this->__('Customers'))
            ->_addBreadcrumb($this->__('Email Collector Emails'), $this->__('Email Collector Emails'));
         
        return $this;
    }
     
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/tegdesign_emailcollector_emails');
    }
}
