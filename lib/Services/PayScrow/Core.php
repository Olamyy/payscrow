<?php

Scrow
class Services_PayScrow_PaymentProcessor
{

    //Options: Variables needed to create PayScrow Lib Components
    private $_libBase;
    private $_privateKey;
    private $_apiUrl;
    //Objects: Objects used by the methods
    private $_transactionsObject;
    private $_preauthObject;
    private $_clientsObject;
    private $_paymentsObject;
    private $_logger;               //Only this object can be set using a set function.
    //Process Payment relevant
    private $_token;                //Token generated for the Transaction
    private $_amount;               //Current Amount
    private $_preAuthAmount;
    private $_currency;             //Currency (of both amounts)
    private $_name;                 //Customername
    private $_email;                //Customer Email Adress
    private $_description;
    private $_lastResponse;
    private $_transactionId;        //Transaction Id generated by the createTransaction function.
    private $_preauthId;
    //Fast Checkout Variables
    private $_clientId = null;
    private $_paymentId = null;
    //Source
    private $_source;
    private $_errorCode;
    public function __construct($privateKey = null, $apiUrl = null, $libBase = null, $params = null, Services_PayScrow_Logging $loggingClassInstance = null)
    {
        $this->setPrivateKey($privateKey);
        $this->setApiUrl($apiUrl);
        $this->setLibBase($libBase);
        $this->_token = $params['token'];
        $this->_amount = $params['amount'];
        $this->_currency = $params['currency'];
        $this->_name = $params['name'];
        $this->_email = $params['email'];
        $this->_description = $params['description'];
        $this->setLogger($loggingClassInstance);
    }

    private function _createClient()
    {
        $this->_initiatePhpWrapperClasses();
        if (isset($this->_clientId)) {
            $this->_log("Client using: " . $this->_clientId);
        } else {
            $client = $this->_clientsObject->create(
                array(
                    'email' => $this->_email,
                    'description' => $this->_description
                )
            );

            $this->_validateResult($client, 'Client');

            $this->_clientId = $client['id'];
        }
        return $this->_clientId;
    }

    private function _createPayment()
    {
        $this->_initiatePhpWrapperClasses();
        if (isset($this->_paymentId)) {
            $this->_log("Payment using: " . $this->_paymentId);
        } else {
            $payment = $this->_paymentsObject->create(
                array(
                    'token' => $this->_token,
                    'client' => $this->_clientId
                )
            );

            $this->_validateResult($payment, 'Payment');
            $this->_paymentId = $payment['id'];
        }
        return true;
    }

    private function _createTransaction()
    {
        $this->_initiatePhpWrapperClasses();
        $parameter = array(
            'amount' => $this->_amount,
            'currency' => $this->_currency,
            'description' => $this->_description,
            'preauthorization' => $this->_preauthId,
            'source' => $this->_source
        );
        $this->_preauthId != null ? $parameter['preauthorization'] = $this->_preauthId : $parameter['payment'] = $this->_paymentId;
        $transaction = $this->_transactionsObject->create($parameter);
        $this->_validateResult($transaction, 'Transaction');

        $this->_transactionId = $transaction['id'];
        return true;
    }

    private function _createPreauthorization()
    {
        $preAuth = $this->_preauthObject->create(
            array(
                'amount' => $this->_preAuthAmount,
                'currency' => $this->_currency,
                'description' => $this->_description,
                'payment' => $this->_paymentId,
                'client' => $this->_clientId,
            )
        );
        $this->_validateResult($preAuth, 'Preauthorization');
        $this->_preauthId = $preAuth['preauthorization']['id'];
        return true;
    }

    private function _initiatePhpWrapperClasses()
    {
        $this->_transactionsObject = new Services_PayScrow_Transactions($this->_privateKey, $this->_apiUrl);
        $this->_preauthObject = new Services_PayScrow_Preauthorizations($this->_privateKey, $this->_apiUrl);
        $this->_clientsObject = new Services_PayScrow_Clients($this->_privateKey, $this->_apiUrl);
        $this->_paymentsObject = new Services_PayScrow_Payments($this->_privateKey, $this->_apiUrl);
    }

    private function _log($message, $debugInfo = null)
    {
        if (isset($this->_logger)) {
            $this->_logger->log($message, $debugInfo);
        }
    }

    private function _validateParameter()
    {
        if ($this->_preAuthAmount == null) {
            $this->_preAuthAmount = $this->_amount;
        }

        $validation = true;
        $parameter = array(
            "token" => $this->_token,
            "amount" => $this->_amount,
            "currency" => $this->_currency,
            "name" => $this->_name,
            "email" => $this->_email,
            "description" => $this->_description);

        $arrayMask = array(
            "token" => 'string',
            "amount" => 'integer',
            "currency" => 'string',
            "name" => 'string',
            "email" => 'string',
            "description" => 'string');

        foreach ($arrayMask as $mask => $type) {
            if (is_null($parameter[$mask])) {
                $validation = false;
                $this->_log("The Parameter $mask is missing.", var_export($parameter, true));
            } else {
                switch ($type) {
                    case 'string':
                        if (!is_string($parameter[$mask])) {
                            $this->_log("The Parameter $mask is not a string.", var_export($parameter, true));
                            $validation = false;
                        }
                        break;
                    case 'integer':
                        if (!is_integer($parameter[$mask])) {
                            $this->_log("The Parameter $mask is not an integer.", var_export($parameter, true));
                            $validation = false;
                        }
                        break;
                }
            }

            if (!$validation) {
                break;
            }
        }
        return $validation;
    }

    private function _validateResult($PayScrowObject, $type)
    {
        $this->_lastResponse = $PayScrowObject;
        if (isset($PayScrowObject['data']['response_code']) && $PayScrowObject['data']['response_code'] !== 20000) {
            $this->_log("An Error occured: " . $PayScrowObject['data']['response_code'], var_export($PayScrowObject, true));
            if (empty($PayScrowObject['data']['response_code'])) {
                $PayScrowObject['data']['response_code'] = 0;
            }

            throw new Exception("Invalid Result Exception: Invalid ResponseCode", $PayScrowObject['data']['response_code']);
        }

        if (isset($PayScrowObject['response_code']) && $PayScrowObject['response_code'] !== 20000) {
            $this->_log("An Error occured: " . $PayScrowObject['response_code'], var_export($PayScrowObject, true));
            if (empty($PayScrowObject['response_code'])) {
                $PayScrowObject['response_code'] = 0;
            }

            throw new Exception("Invalid Result Exception: Invalid ResponseCode", (int)$PayScrowObject['response_code']);
        }

        if (!isset($PayScrowObject['id']) && !isset($PayScrowObject['data']['id'])) {
            $this->_log("No $type created.", var_export($PayScrowObject, true));
            throw new Exception("Invalid Result Exception: Invalid Id");
        } else {
            $this->_log("$type created.", isset($PayScrowObject['id']) ? $PayScrowObject['id'] : $PayScrowObject['data']['id']);
        }

        // check result
        if ($type == 'Transaction') {
            if (is_array($PayScrowObject) && array_key_exists('status', $PayScrowObject)) {
                if ($PayScrowObject['status'] == "closed") {
                    // transaction was successfully issued
                    return true;
                } elseif ($PayScrowObject['status'] == "open") {
                    // transaction was issued but status is open for any reason
                    $this->_log("Status is open.", var_export($PayScrowObject, true));
                    throw new Exception("Invalid Result Exception: Invalid Orderstate");
                } else {
                    // another error occured
                    $this->_log("Unknown error." . var_export($PayScrowObject, true));
                    throw new Exception("Invalid Result Exception: Unknown Error");
                }
            } else {
                // another error occured
                $this->_log("$type could not be issued.", var_export($PayScrowObject, true));
                throw new Exception("Invalid Result Exception: $type could not be issued.");
            }
        } else {
            return true;
        }
    }

    private function _processPreAuthCapture($captureNow)
    {
        $this->_createPreauthorization();
        if ($captureNow) {
            $this->_createTransaction();
        }
        return true;
    }

    final public function processPayment($captureNow = true)
    {
        $this->_initiatePhpWrapperClasses();
        if (!$this->_validateParameter()) {
            return false;
        }

        $this->_log('Process payment with following data', print_r($this->toArray(), true));

        try {

            $this->_createClient();
            $this->_log('Client API Response', print_r($this->_clientsObject->getResponse(), true));
            $this->_createPayment();
            $this->_log('Payment API Response', print_r($this->_paymentsObject->getResponse(), true));

            //creates a transaction if there is no difference between the amount
            if ($this->_preAuthAmount === $this->_amount && $captureNow) {
                $this->_createTransaction();
                $this->_log('Transaction API Response', print_r($this->getLastResponse(), true));
            } else {
                $this->_processPreAuthCapture($captureNow);
                $this->_log('Pre-Auth API Response', print_r($this->getLastResponse(), true));
            }

            return true;
        } catch (Exception $ex) {
            $this->_errorCode = $ex->getCode();
            // PayScrow wrapper threw an exception
            $this->_log("Exception thrown from PayScrow wrapper. Code: " . $ex->getCode() . " Message: " . $ex->getMessage(), print_r($this->_transactionsObject->getResponse(), true));
            return false;
        }
    }

    final public function capture()
    {
        $this->_initiatePhpWrapperClasses();
        if (!isset($this->_amount) || !isset($this->_currency) || !isset($this->_preauthId)) {
            return false;
        }
        return $this->_createTransaction();
    }

    public function createClient($email = null, $description = null){
        $result = false;
        $email = isset($email) ? $email : $this->_email;
        $description = isset($description) ? $description : $this->_description;
        if(!in_array(null, array($email, $description))){
            $this->_clientId = null;
            $this->_email = $email;
            $this->_description = $description;
            $result = $this->_createClient();
        }
        return $result;
    }

    public function createPayment($token = null, $client = null){
        $result = false;
        $token = isset($token) ? $token : $this->_token;
        $client = isset($client) ? $client : $this->_clientId;
        if(!in_array(null, array($token, $client))){
            $this->_paymentId = null;
            $this->_token = $token;
            $this->_clientId = $client;
            $result = $this->_createPayment();
        }
        return $result;
    }

    public function createTransaction($amount = null, $currency = null, $description = null, $preauthorisation = null, $source = null){
        $result = false;
        $amount = isset($amount) ? $amount : $this->_amount;
        $currency = isset($currency) ? $currency : $this->_currency;
        $description = isset($description) ? $description : $this->_description;
        $preauthorisation = isset($preauthorisation) ? $preauthorisation : $this->_preauthId;
        $source = isset($source) ? $source : $this->_source;
        if(!in_array(null, array($amount, $currency, $description))){
            $this->_transactionId = null;
            $this->_amount = $amount;
            $this->_currency = $currency;
            $this->_description = $description;
            $this->_preauthId = $preauthorisation;
            $this->_source = $source;
            $result = $this->_createTransaction();
        }
        return $result;
    }

    public function toArray()
    {
        return array(
            'apiurl' => $this->_apiUrl,
            'libbase' => $this->_libBase,
            'privatekey' => $this->_privateKey,
            'token' => $this->_token,
            'amount' => $this->_amount,
            'preauthamount' => $this->_preAuthAmount,
            'currency' => $this->_currency,
            'description' => $this->_description,
            'email' => $this->_email,
            'name' => $this->_name,
            'source' => $this->_source
        );
    }

    private function isIdValid($id, $object)
    {
        $result = $object->getOne($id);
        if (array_key_exists('id', $result)) {
            return $result['id'] === $id;
        } else {
            return false;
        }
    }

    public function getClientId()
    {
        return $this->_clientId;
    }

    public function getPaymentId()
    {
        return $this->_paymentId;
    }

    public function getTransactionId()
    {
        return $this->_transactionId;
    }

    public function getPreauthId()
    {
        return $this->_preauthId;
    }

    public function getLastResponse()
    {
        return $this->_lastResponse;
    }

    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    public function setClientId($clientId = null)
    {
        $this->_initiatePhpWrapperClasses();
        if ($this->isIdValid($clientId, $this->_clientsObject)) {
            $this->_clientId = $clientId;
        }
    }

    public function setPaymentId($paymentId = null)
    {
        $this->_initiatePhpWrapperClasses();
        if ($this->isIdValid($paymentId, $this->_paymentsObject)) {
            $this->_paymentId = $paymentId;
        }
    }

    public function setToken($token = null)
    {
        $this->_token = $token;
    }

    public function setPreAuthAmount($preAuthAmount = null)
    {
        $this->_preAuthAmount = $preAuthAmount;
    }

    public function setAmount($amount = null)
    {
        $this->_amount = $amount;
    }

    public function setCurrency($currency = null)
    {
        $this->_currency = $currency;
    }

    public function setName($name = null)
    {
        $this->_name = $name;
    }

    public function setEmail($email = null)
    {
        $this->_email = $email;
    }

    public function setDescription($description = null)
    {
        $this->_description = $description;
    }

    public function setApiUrl($apiUrl = null)
    {
        $this->_apiUrl = $apiUrl;
    }

    public function setLibBase($libBase = null)
    {
        $this->_libBase = $libBase == null ? dirname(__FILE__) . DIRECTORY_SEPARATOR : $libBase;
    }

    public function setLogger(Services_PayScrow_Logging $logger = null)
    {
        $this->_logger = $logger;
    }

    public function setPrivateKey($privateKey = null)
    {
        $this->_privateKey = $privateKey;
    }

    public function setSource($source)
    {
        $this->_source = $source;
    }

    public function setPreauthId($preauthId)
    {
        $this->_preauthId = $preauthId;
    }

}