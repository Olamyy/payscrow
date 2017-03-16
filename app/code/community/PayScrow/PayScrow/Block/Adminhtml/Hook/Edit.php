<?php

class PayScrow_PayScrow_Block_Adminhtml_Hook_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_hook';
        $this->_blockGroup = 'payScrow';
        $this->_updateButton('save', 'label', Mage::helper('payScrow')->__('save_hook'));
        $this->_removeButton('delete');
        $this->_removeButton('back');
    }

    public function getHeaderText()
    {
        return Mage::helper('payScrow')->__('Hook');
    }
}
