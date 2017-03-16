<?php



class PayScrow_PayScrow_Helper_HookHelper extends Mage_Core_Helper_Abstract
{

    private $_hooks;
    
    private function _initHooks()
    { 
        $this->_hooks = new Services_PayScrow_Webhooks(
            trim(Mage::helper('payScrow/optionHelper')->getPrivateKey()),
            Mage::helper('payScrow')->getApiUrl()
        );
        
        return $this;
    }
    
    public function createHook(array $params)
    {
        $this->_initHooks();
        
        $result = $this->_hooks->create($params);
    }
    
    public function getAllHooks()
    {
        $this->_initHooks();
        
        return $this->_hooks->get();
    }
    
    public function deleteHook($id)
    {
        $this->_initHooks();
        
        $this->_hooks->delete($id);
    }
}