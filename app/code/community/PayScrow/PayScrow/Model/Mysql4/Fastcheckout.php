<?php 

class PayScrow_PayScrow_Model_Mysql4_Fastcheckout extends Mage_Core_Model_Mysql4_Abstract
{
    

    public function _construct()
    {
        $this->_init('payScrow/fastcheckout', 'id');
    }
}