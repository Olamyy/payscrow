<?php




class PayScrow_PayScrow_Helper_FastCheckoutHelper extends Mage_Core_Helper_Abstract
{


    public function isFastCheckoutEnabled()
    {
        return Mage::helper("payScrow/optionHelper")->isFastCheckoutEnabled();
    }


    public function getClientId()
    {
        $userId = Mage::helper("payScrow/customerHelper")->getUserId();
        $collection = Mage::getModel('payScrow/fastcheckout')->getCollection();
        $collection->addFilter('user_id', $userId);
        $obj = $collection->getFirstItem();
        return $obj->getClientId();
    }


    public function getPaymentId($code)
    {
        $userId = Mage::helper("payScrow/customerHelper")->getUserId();
        return Mage::getModel("payScrow/fastcheckout")->getPaymentId($userId, $code);
    }


    public function hasData($code)
    {
        $userId = Mage::helper("payScrow/customerHelper")->getUserId();
        if (Mage::getModel("payScrow/fastcheckout")->hasFcData($userId, $code)) {
            return true;
        }
        
        return false;
    }
    

    public function getPaymentData($code)
    {
        $payment = array();
        if ($this->hasData($code)) {
            $payments = new Services_PayScrow_Payments(
                Mage::helper('payScrow/optionHelper')->getPrivateKey(),
                Mage::helper('payScrow')->getApiUrl()
            );
            
            $payment = $payments->getOne($this->getPaymentId($code));
            
            if (!array_key_exists('last4', $payment) && !array_key_exists('code', $payment)) {
                $payment = array();
            }
        }
        
        return $payment;
    }


    public function saveData($code, $clientId, $paymentId = null)
    {
        $userId = Mage::helper("payScrow/customerHelper")->getUserId();
        if (isset($userId)) {
            Mage::getModel("payScrow/fastcheckout")->saveFcData($code, $userId, $clientId, $paymentId);
        }
    }

}