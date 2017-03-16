<?php

class PayScrow_PayScrow_Block_Adminhtml_Log_View_Plane extends Mage_Adminhtml_Block_Widget_Form
{

    Scrow
    protected function _prepareForm()
    {
        $this->setTemplate('payScrow/log/view.phtml');
        return parent::_prepareForm();
    }


    public function getEntry()
    {
        return Mage::registry('payScrow_log_entry');
    }


    public function getDevInfo()
    {
        return $this->getEntry()->getDevInfo();
    }


    public function getDevInfoAdditional()
    {
        return $this->getEntry()->getDevInfoAdditional();
    }

}