<?php




class PayScrow_PayScrow_Helper_OptionHelper extends Mage_Core_Helper_Abstract
{


    public function getPublicKey()
    {
        return trim($this->_getGeneralOption("public_key"));
    }


    public function getPrivateKey()
    {
        return trim($this->_getGeneralOption("private_key"));
    }


    public function isLogging()
    {
        return $this->_getGeneralOption("logging_active");
    }


    public function isFastCheckoutEnabled()
    {
        return $this->_getGeneralOption("fc_active");
    }


    public function isInDebugMode()
    {
        return $this->_getGeneralOption("debugging_active");
    }


    public function isShowingLabels()
    {
        return $this->_getGeneralOption("show_label");
    }
    

    public function isBaseCurrency()
    {
        return $this->_getGeneralOption("base_currency");
    }
    

    public function getTokenSelector()
    {
        return $this->_getGeneralOption("token_creation_identifier_id");
    }


    private function _getBackendOption($choice, $optionName)
    {
        $value = Mage::getStoreConfig('payment/' . $choice . '/' . $optionName, Mage::app()->getStore()->getStoreId());

        return $value;
    }


    private function _getGeneralOption($optionName)
    {
        return $this->_getBackendOption("payScrow", $optionName);
    }


    public function isPreAuthorizing()
    {
        return $this->_getBackendOption("payScrow_creditcard", "preAuth_active");
    }


    public function getPrenotificationDays()
    {
        return $this->_getBackendOption("payScrow_directdebit", "prenotification");
    }


    public function getPci()
    {
        return $this->_getBackendOption("payScrow_creditcard", "pci");
    }
}
