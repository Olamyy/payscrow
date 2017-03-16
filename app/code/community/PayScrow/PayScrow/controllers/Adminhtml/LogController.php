<?php

class PayScrow_PayScrow_Adminhtml_LogController extends Mage_Adminhtml_Controller_Action
{


    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('log/payScrow_log');
        return $this;
    }


    public function indexAction()
    {
        // Let's call our initAction method which will set some basic params for each action
        $this->_initAction()
                ->renderLayout();
    }
    

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('payScrow/log')->load($id);
        if ($model->getId()) {
            Mage::register('payScrow_log_entry', $model);
            $this->_initAction();
            $this->_addContent($this->getLayout()->createBlock('payScrow/adminhtml_log_view'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('payScrow')->__('Item does not exist'));
            $this->_redirect('*
    public function massDeleteAction()
    {
        $logIds = $this->getRequest()->getParam('log_id');

        if (!is_array($logIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('payScrow')->__('payScrow_error_text_no_entry_selected'));
        } else {
            try {
                foreach ($logIds as $logId) {
                    Mage::getModel('payScrow/log')->load($logId)->delete();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('payScrow')->__("payScrow_log_action_success"));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}
