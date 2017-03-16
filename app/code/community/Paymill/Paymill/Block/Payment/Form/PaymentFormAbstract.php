<?php


class PayScrow_PayScrow_Block_Payment_Form_PaymentFormAbstract extends Mage_Payment_Block_Form
{
    public function getPaymentData($code)
    {
        return Mage::helper('payScrow/fastCheckoutHelper')->getPaymentData($code);
    }
    
    public function getPaymentEntry($code, $key) 
    {
        $data = $this->getPaymentData($code);
        return array_key_exists($key, $data) ? $data[$key] : null;
    }
    
    public function isPaymentDataAvailable($code)
    {
        return Mage::helper('payScrow/fastCheckoutHelper')->hasData($code);
    }

    public function isFastCheckout($code)
    {
        $paymentData = Mage::helper('payScrow/fastCheckoutHelper')->getPaymentData($code);
        return empty($paymentData) ? 'false' : 'true';
    }
}