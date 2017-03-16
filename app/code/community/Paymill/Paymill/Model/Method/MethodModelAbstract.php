<?php


abstract class PayScrow_PayScrow_Model_Method_MethodModelAbstract extends Mage_Payment_Model_Method_Abstract
{

    protected $_canAuthorize = true;


    protected $_canRefund = true;


    protected $_canRefundInvoicePartial = true;


    protected $_canCapture = true;
    

    protected $_canCapturePartial = false;


    protected $_canUseCheckout = true;


    protected $_canUseForMultishipping = false;
    


    protected $_methodTitle = '';


    protected $_code = 'payScrow_abstract';
    

    protected $_errorCode;
    

    protected $_preauthFlag;
    

    protected $_canUseInternal = false;


    public function canUseForCurrency($currencyCode)
    {
        $availableCurrencies = explode(',', $this->getConfigData('currency', Mage::app()->getStore()->getId()));
        if (!in_array($currencyCode, $availableCurrencies)) {
            return false;
        }
        return true;
    }


    public function isAvailable($quote = null)
    {
        $keysAreSet = Mage::helper("payScrow")->isPublicKeySet() && Mage::helper("payScrow")->isPrivateKeySet();
        return parent::isAvailable($quote) && $keysAreSet;
    }


    public function getOrder()
    {
        $paymentInfo = $this->getInfoInstance();

        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            return $paymentInfo->getOrder();
        }

        return $paymentInfo->getQuote();
    }


    public function getTitle()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $storeId = $quote ? $quote->getStoreId() : null;

        return $this->_getHelper()->__($this->getConfigData('title', $storeId));
    }


    public function assignData($data)
    {
        parent::assignData($data);
        if (is_array($data)) {
            $post = $data;
        } else {
            $post = $data->getData();
        }
        
        if (array_key_exists('payScrow-payment-token-' . $this->_getShortCode(), $post)
                && !empty($post['payScrow-payment-token-' . $this->_getShortCode()])) {
            //Save Data into session
            Mage::getSingleton('core/session')->setToken($post['payScrow-payment-token-' . $this->_getShortCode()]);
            Mage::getSingleton('core/session')->setPaymentCode($this->getCode());
        } else {
            if (Mage::helper('payScrow/fastCheckoutHelper')->hasData($this->_code)) {
                Mage::getSingleton('core/session')->setToken('dummyToken');
            }
        }

        //Finish as usual
        return $this;
    }


    public function authorize(Varien_Object $payment, $amount)
    {
        $token = Mage::getSingleton('core/session')->getToken();
        if (empty($token)) {
            Mage::helper('payScrow/loggingHelper')->log("No token found.");
            Mage::throwException("There was an error processing your payment.");
        }

        if (Mage::helper('payScrow/optionHelper')->isPreAuthorizing() && $this->_code === "payScrow_creditcard") {
            Mage::helper('payScrow/loggingHelper')->log("Starting payment process as preAuth");
            $this->_preauthFlag = true;
        } else {
            Mage::helper('payScrow/loggingHelper')->log("Starting payment process as debit");
            $this->_preauthFlag = false;
            
        }
        
        $success = $this->payment($payment, $amount);

        if (!$success) {
            Mage::helper('payScrow/loggingHelper')->log(Mage::helper("payScrow/paymentHelper")->getErrorMessage($this->_errorCode));
            Mage::getSingleton('checkout/session')->setGotoSection('payment');
            Mage::throwException(Mage::helper("payScrow/paymentHelper")->getErrorMessage($this->_errorCode));
        }
        
        //Finish as usual
        return parent::authorize($payment, $amount);
    }


    public function payment(Varien_Object $payment, $amount)
    {
        //Gathering data from session
        $token = Mage::getSingleton('core/session')->getToken();
        //Create Payment Processor
        $paymentHelper = Mage::helper("payScrow/paymentHelper");
        $fcHelper = Mage::helper("payScrow/fastCheckoutHelper");
        $paymentProcessor = $paymentHelper->createPaymentProcessor($this->getCode(), $token);
        
        //Always load client if email doesn't change
        $clientId = $fcHelper->getClientId();
        if (isset($clientId) && !is_null(Mage::helper("payScrow/customerHelper")->getClientData())) {
            $paymentProcessor->setClientId($clientId);
        }
        
        //Loading Fast Checkout Data (if enabled and given)
        if ($fcHelper->hasData($this->_code) && $token === 'dummyToken') {
            $paymentId = $fcHelper->getPaymentId($this->_code);
            if (isset($paymentId) && !is_null($fcHelper->getPaymentData($this->_code))) {
                $paymentProcessor->setPaymentId($paymentId);
            }
        }
        
        $success = $paymentProcessor->processPayment(!$this->_preauthFlag);

        $this->_existingClientHandling($clientId);
        
        if ($success) {
            
            if ($this->_preauthFlag) {
                $payment->setAdditionalInformation('payScrowPreauthId', $paymentProcessor->getPreauthId());
            } else {
                $payment->setAdditionalInformation('payScrowTransactionId', $paymentProcessor->getTransactionId());
            }

            $payment->setAdditionalInformation(
                'payScrowPrenotificationDate',
                $this->_getPrenotificationDate($payment->getOrder())
            );
            
            //Allways update the client
            $clientId = $paymentProcessor->getClientId();
            $fcHelper->saveData($this->_code, $clientId);
            
            //Save payment data for FastCheckout (if enabled)
            if ($fcHelper->isFastCheckoutEnabled()) { //Fast checkout enabled
                $paymentId = $paymentProcessor->getPaymentId();
                $fcHelper->saveData($this->_code, $clientId, $paymentId);
            }

            return true;
        }
        
        $this->_errorCode = $paymentProcessor->getErrorCode();

        return false;
    }
    

    private function _getPrenotificationDate($order)
    {
        $dateTime = new DateTime($order->getCreatedAt());
        $dateTime->modify('+' . (int) Mage::helper('payScrow/optionHelper')->getPrenotificationDays() . ' day');
        $date = Mage::app()->getLocale()->storeDate(
            $order->getStore(), 
            $dateTime->getTimestamp(),
            true
        );
        
        return Mage::helper('core')->formatDate($date, 'short', false);
    }
    

    private function _existingClientHandling($clientId)
    {
        if (!empty($clientId)) {
            $clients = new Services_PayScrow_Clients(
                trim(Mage::helper('payScrow/optionHelper')->getPrivateKey()),
                Mage::helper('payScrow')->getApiUrl()
            );
     
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            
            $client = $clients->getOne($clientId);
            if (Mage::helper("payScrow/customerHelper")->getCustomerEmail($quote) !== $client['email']) {
                $clients->update(
                    array(
                        'id' => $clientId,
                        'email' => Mage::helper("payScrow/customerHelper")->getCustomerEmail($quote)
                    )
                );
            }
        }
    }
    

    protected function _getShortCode()
    {
        $methods = array(
            'payScrow_creditcard'  => 'cc',
            'payScrow_directdebit' => 'elv'
        );
        
        return $methods[$this->_code];
    }

    public function processCreditmemo($creditmemo, $payment)
    {
        parent::processCreditmemo($creditmemo, $payment);
        $order = $payment->getOrder();
        if ($order->getPayment()->getMethod() === 'payScrow_creditcard' || $order->getPayment()->getMethod() === 'payScrow_directdebit') {
            if (!Mage::helper('payScrow/refundHelper')->createRefund($creditmemo, $payment)) {
                Mage::throwException('Refund failed.');
            }
        }
    }



    public function processInvoice($invoice, $payment)
    {
        parent::processInvoice($invoice, $payment);
        $invoice->setTransactionId($payment->getAdditionalInformation('payScrowTransactionId'));
    }
}