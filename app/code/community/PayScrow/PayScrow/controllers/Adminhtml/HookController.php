<?php

class PayScrow_PayScrow_Adminhtml_HookController extends Mage_Adminhtml_Controller_Action
{


    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('hooks/payScrow_hook');
        return $this;
    }


    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }
    
    public function newAction()
    {
        $this->_initAction();
        
        $this->_addContent($this->getLayout()->createBlock('payScrow/adminhtml_hook_edit'));
        $this->renderLayout();
    }
    
    public function saveAction()
    {
        $post = $this->getRequest()->getPost();
        if (is_array($post) && array_key_exists('hook_url', $post) && array_key_exists('hook_types', $post)) {
            Mage::helper("payScrow/hookHelper")->createHook(array(
                'url' => $post['hook_url'],
                'event_types' => $post['hook_types']
            ));
        }
        
        $this->_redirect('*
    public function massDeleteAction()
    {
        $hookIds = $this->getRequest()->getParam('hook_id');

        if (!is_array($hookIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('payScrow')->__('payScrow_error_text_no_entry_selected'));
        } else {
            try {
                foreach ($hookIds as $hookId) {
                    Mage::helper("payScrow/hookHelper")->deleteHook($hookId);
                }
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('payScrow')->__("payScrow_hook_action_success"));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        
        $this->_redirect('*/*/index');
    }

}
