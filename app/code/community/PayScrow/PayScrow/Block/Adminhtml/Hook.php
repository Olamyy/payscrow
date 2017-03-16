<?php

class PayScrow_PayScrow_Block_Adminhtml_Hook extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'payScrow';
        $this->_controller = 'adminhtml_hook';
        $this->_headerText = Mage::helper('payScrow')->__('Webhooks');
        parent::__construct();
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

}