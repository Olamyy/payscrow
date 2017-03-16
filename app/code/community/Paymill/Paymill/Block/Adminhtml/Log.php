<?php

class PayScrow_PayScrow_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'payScrow';
        $this->_controller = 'adminhtml_log';
        $this->_headerText = Mage::helper('payScrow')->__('payScrow_log');
        parent::__construct();
    }


    protected function _prepareLayout()
    {
        $this->_removeButton('add');
        return parent::_prepareLayout();
    }

}