<?php

if (!function_exists('json_decode')) {
    throw new Exception("Please install the PHP JSON extension");
}

if (!function_exists('curl_init')) {
    throw new Exception("Please install the PHP cURL extension");
}


class Services_PayScrow_Apiclient_Curl implements Services_PayScrow_Apiclient_Interface
{

    
    private $_apiKey = null;
    private $_responseArray = null;

    
    private $_apiUrl = '/';

    const USER_AGENT = 'PayScrow-php/0.0.2';

    public static $lastRawResponse;
    public static $lastRawCurlOptions;

    
    public function __construct($apiKey, $apiEndpoint)
    {
        $this->_apiKey = $apiKey;
        $this->_apiUrl = $apiEndpoint;
    }

    
    public function request($action, $params = array(), $method = 'POST')
    {
        if (!is_array($params))
            $params = array();

        try {
            $this->_responseArray = $this->_requestApi($action, $params, $method);
            $httpStatusCode = $this->_responseArray['header']['status'];
            if ($httpStatusCode != 200) {
                $errorMessage = 'Client returned HTTP status code ' . $httpStatusCode;
                if (isset($this->_responseArray['body']['error'])) {
                    $errorMessage = $this->_responseArray['body']['error'];
                }
                $responseCode = '';
                if (isset($this->_responseArray['body']['response_code'])) {
                    $responseCode = $this->_responseArray['body']['response_code'];
                }
                if ($responseCode === '' && isset($this->_responseArray['body']['data']['response_code'])) {
                    $responseCode = $this->_responseArray['body']['data']['response_code'];
                }

                return array("data" => array(
                    "error" => $errorMessage,
                    "response_code" => $responseCode,
                    "http_status_code" => $httpStatusCode
                ));
            }

            return $this->_responseArray['body'];
        } catch (Exception $e) {
            return array("data" => array("error" => $e->getMessage()));
        }
    }

    
    protected function _requestApi($action = '', $params = array(), $method = 'POST')
    {
        $curlOpts = array(
            CURLOPT_URL => $this->_apiUrl . $action,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO => Mage::getBaseDir() . '/lib/Services/PayScrow/Apiclient/payScrow.crt',
        );

        if (Services_PayScrow_Apiclient_Interface::HTTP_GET === $method) {
            if (0 !== count($params)) {
                $curlOpts[CURLOPT_URL] .= false === strpos($curlOpts[CURLOPT_URL], '?') ? '?' : '&';
                $curlOpts[CURLOPT_URL] .= http_build_query($params, null, '&');
            }
        } else {
            $curlOpts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
        }

        if ($this->_apiKey) {
            $curlOpts[CURLOPT_USERPWD] = $this->_apiKey . ':';
        }

        $curl = curl_init();
        curl_setopt_array($curl, $curlOpts);
        $responseBody = curl_exec($curl);
        self::$lastRawCurlOptions = $curlOpts;
        self::$lastRawResponse = $responseBody;
        $responseInfo = curl_getinfo($curl);
        if ($responseBody === false) {
            $responseBody = array('error' => curl_error($curl));
        }
        curl_close($curl);

        if ('application/json' === $responseInfo['content_type']) {
            $responseBody = json_decode($responseBody, true);
        }

        return array(
            'header' => array(
                'status' => $responseInfo['http_code'],
                'reason' => null,
            ),
            'body' => $responseBody
        );
    }

    
    public function getResponse()
    {
        return $this->_responseArray;
    }

}