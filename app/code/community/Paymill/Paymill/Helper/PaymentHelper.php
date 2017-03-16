<?php




class PayScrow_PayScrow_Helper_PaymentHelper extends Mage_Core_Helper_Abstract
{


    protected $_responseCodes = array(
        '10001' => 'General undefined response.',
        '10002' => 'Still waiting on something.',
        '20000' => 'General success response.',
        '40000' => 'General problem with data.',
        '40001' => 'General problem with payment data.',
        '40100' => 'Problem with credit card data.',
        '40101' => 'Problem with cvv.',
        '40102' => 'Card expired or not yet valid.',
        '40103' => 'Limit exceeded.',
        '40104' => 'Card invalid.',
        '40105' => 'Expiry date not valid.',
        '40106' => 'Credit card brand required.',
        '40200' => 'Problem with bank account data.',
        '40201' => 'Bank account data combination mismatch.',
        '40202' => 'User authentication failed.',
        '40300' => 'Problem with 3d secure data.',
        '40301' => 'Currency / amount mismatch',
        '40400' => 'Problem with input data.',
        '40401' => 'Amount too low or zero.',
        '40402' => 'Usage field too long.',
        '40403' => 'Currency not allowed.',
        '50000' => 'General problem with backend.',
        '50001' => 'Country blacklisted.',
        '50100' => 'Technical error with credit card.',
        '50101' => 'Error limit exceeded.',
        '50102' => 'Card declined by authorization system.',
        '50103' => 'Manipulation or stolen card.',
        '50104' => 'Card restricted.',
        '50105' => 'Invalid card configuration data.',
        '50200' => 'Technical error with bank account.',
        '50201' => 'Card blacklisted.',
        '50300' => 'Technical error with 3D secure.',
        '50400' => 'Decline because of risk issues.',
        '50500' => 'General timeout.',
        '50501' => 'Timeout on side of the acquirer.',
        '50502' => 'Risk management transaction timeout.',
        '50600' => 'Duplicate transaction.'
    );
    

    public function getErrorMessage($code)
    {
        $message = 'General undefined response.';
        if (array_key_exists($code, $this->_responseCodes)) {
            $message = $this->__($this->_responseCodes[$code]);
        }
        
        return $message;
    }


    public function getAmount($object = null)
    {
        if (is_null($object)) {
            $object = Mage::getSingleton('checkout/session')->getQuote();
        }
        
        $amount = $object->getBaseGrandTotal();
        
        if (!Mage::helper('payScrow/optionHelper')->isBaseCurrency()) {
            $amount = $object->getGrandTotal();
        }
        
        return round($amount * 100);
    }


    public function getCurrency($object)
    {
        $currency = $object->getBaseCurrencyCode();
        if (!Mage::helper('payScrow/optionHelper')->isBaseCurrency()) {
            if ($object instanceof Mage_Sales_Model_Quote) {
                $currency = $object->getQuoteCurrencyCode();
            } else {
                $currency = $object->getOrderCurrencyCode();
            }
        }
        
        return $currency;
    }


    public function getDescription($object)
    {
        return $this->getOrderId($object) . ", " . Mage::helper("payScrow/customerHelper")->getCustomerEmail($object);
    }


    public function getPaymentType($code)
    {
        $type = null;
        
        //Creditcard
        if ($code === "payScrow_creditcard") {
            $type = "cc";
        }
        //Directdebit
        if ($code === "payScrow_directdebit") {
            $type = "elv";
        }

        return $type;
    }


    public function getOrderId($object)
    {
        $orderId = null;

        if ($object instanceof Mage_Sales_Model_Order) {
            $orderId = $object->getIncrementId();
        }

        if ($object instanceof Mage_Sales_Model_Quote) {
            $orderId = $object->getReservedOrderId();
        }


        return $orderId;
    }
    
    public function invoice(Mage_Sales_Model_Order $order, $transactionId, $mail)
    {
        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice();

            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            
            Mage::getModel('core/resource_transaction')
               ->addObject($invoice)
               ->addObject($invoice->getOrder())
               ->save();

            $invoice->setTransactionId($transactionId);

            $invoice->pay();
            
            $invoice->save();

            $invoice->sendEmail($mail, '');            
        } else {
            foreach ($order->getInvoiceCollection() as $invoice) {
                $invoice->pay()->save();
            }
        }
    }


    public function createPaymentProcessor($paymentCode, $token)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $params = array();
        $params['token'] = $token;
        $params['amount'] = (int) $this->getAmount();
        $params['currency'] = $this->getCurrency($quote);
        $params['payment'] = $this->getPaymentType($paymentCode); // The chosen payment (cc | elv)
        $params['name'] = Mage::helper("payScrow/customerHelper")->getCustomerName($quote);
        $params['email'] = Mage::helper("payScrow/customerHelper")->getCustomerEmail($quote);
        $params['description'] = substr($this->getDescription($quote), 0, 128);
        
        $paymentProcessor = new Services_PayScrow_PaymentProcessor(
            Mage::helper('payScrow/optionHelper')->getPrivateKey(),
            Mage::helper('payScrow')->getApiUrl(),
            null, 
            $params, 
            Mage::helper('payScrow/loggingHelper')
        );
        
        $paymentProcessor->setSource(Mage::helper('payScrow')->getSourceString());
        
        return $paymentProcessor;
    }

}
