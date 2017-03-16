<?php

class PayScrow_PayScrow_Block_Adminhtml_Log_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('log_view_tabs');
        $this->setDestElementId('log_view');
        $this->setTitle(Mage::helper('payScrow')->__('Log Information View'));
    }

}