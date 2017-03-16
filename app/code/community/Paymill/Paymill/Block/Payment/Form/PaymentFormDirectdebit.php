<?php

class PayScrow_PayScrow_Block_Payment_Form_PaymentFormDirectdebit extends PayScrow_PayScrow_Block_Payment_Form_PaymentFormAbstract
{


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('payScrow/payment/form/directdebit.phtml');
    }

    public function getPaymentEntryElv($code)
    {
        $data = $this->getPaymentData($code);
        $fastCheckoutData = array(null,null);
        if(isset($data['iban'])) {
            $fastCheckoutData[0] = $data['iban'];
            $fastCheckoutData[1] = $data['bic'];
        } elseif(isset($data['account'])) {
            $fastCheckoutData[0] = $data['account'];
            $fastCheckoutData[1] = $data['code'];
        }
        return $fastCheckoutData;
    }
}
