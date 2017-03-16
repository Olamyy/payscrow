<?php


class PayScrow_PayScrow_Model_Method_MethodModelCreditcard extends PayScrow_PayScrow_Model_Method_MethodModelAbstract
{

    protected $_code = "payScrow_creditcard";


    protected $_formBlockType = 'payScrow/payment_form_paymentFormCreditcard';


    protected $_infoBlockType = 'payScrow/payment_info_paymentFormCreditcard';
    
    public function processInvoice($invoice, $payment)
    {
        $data = $payment->getAdditionalInformation();
        
        if (array_key_exists('payScrowPreauthId', $data) && !empty($data['payScrowPreauthId'])) {

            $params = array();
            $params['amount'] = (int) Mage::helper("payScrow/paymentHelper")->getAmount($invoice);
            $params['currency'] = Mage::helper("payScrow/paymentHelper")->getCurrency($invoice);
            $params['description'] = Mage::helper('payScrow/paymentHelper')->getDescription($payment->getOrder());
            $params['source'] = Mage::helper('payScrow')->getSourceString();

            $paymentProcessor = new Services_PayScrow_PaymentProcessor(
                Mage::helper('payScrow/optionHelper')->getPrivateKey(),
                Mage::helper('payScrow')->getApiUrl(),
                null, 
                $params, 
                Mage::helper('payScrow/loggingHelper')
            );
            
            $paymentProcessor->setPreauthId($data['payScrowPreauthId']);
            
            if (!$paymentProcessor->capture()) {
                Mage::throwException(Mage::helper("payScrow/paymentHelper")->getErrorMessage($paymentProcessor->getErrorCode()));
            }

            Mage::helper('payScrow/loggingHelper')->log("Capture created", var_export($paymentProcessor->getLastResponse(), true));

            $payment->setAdditionalInformation('payScrowTransactionId', $paymentProcessor->getTransactionId());
        }
        
        parent::processInvoice($invoice, $payment);
    }

}
