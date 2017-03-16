<?php

class PayScrow_PayScrow_Block_Adminhtml_Log_View extends Mage_Adminhtml_Block_Widget_View_Container
{

    public function __construct()
    {
        $this->_objectId    = 'id';
        $this->_controller  = 'adminhtml_log';
        $this->_mode        = 'view';
        $this->_headerText  = Mage::helper('payScrow')->__('Log Entry');

        parent::__construct();

        $this->_removeButton('edit');
    }


    protected function _prepareLayout()
    {
        $this->setChild('plane', $this->getLayout()->createBlock('payScrow/' . $this->_controller . '_view_plane'));
    }

}