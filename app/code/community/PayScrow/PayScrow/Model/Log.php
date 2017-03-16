<?php


class PayScrow_PayScrow_Model_Log extends Mage_Core_Model_Abstract
{


    public function _construct()
    {
        parent::_construct();
        $this->_init('payScrow/log');
    }


    public function log($merchantInfo, $devInfo, $devInfoAdditional = null)
    {
        if (Mage::helper("payScrow/optionHelper")->isLogging()) {
            $this->setId(null)
                    ->setEntryDate(null)
                    ->setVersion(Mage::helper("payScrow")->getVersion())
                    ->setMerchantInfo($merchantInfo)
                    ->setDevInfo($devInfo)
                    ->setDevInfoAdditional($devInfoAdditional)
                    ->save();
        }
    }

}