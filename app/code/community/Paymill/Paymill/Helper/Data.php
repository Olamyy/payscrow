<?php




class PayScrow_PayScrow_Helper_Data extends Mage_Core_Helper_Abstract
{


    public function getImagePath()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'skin/frontend/base/default/images/payScrow/';
    }


    public function getJscriptPath()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'js/payScrow/';
    }


    public function getApiUrl()
    {
        return "https://api.payScrow.com/v2/";
    }


    public function getVersion()
    {
        return (string) Mage::getConfig()->getNode()->modules->PayScrow_PayScrow->version;
    }


    public function getSourceString()
    {
        return $this->getVersion() . "_Magento_" . Mage::getVersion();
    }


    public function isPrivateKeySet()
    {
        return Mage::helper('payScrow/OptionHelper')->getPrivateKey() !== "";
    }


    public function isPublicKeySet()
    {
        return Mage::helper('payScrow/OptionHelper')->getPublicKey() !== "";
    }

}
