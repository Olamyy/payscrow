<?php

class Services_PayScrow_Refunds extends Services_PayScrow_Base
{
    protected $_serviceResource = 'refunds/';
    
    public function create($itemData = array())
    {
        $transactionId = $itemData['transactionId'];
        $params        = $itemData['params'];

        $result = $this->_httpClient->request(
            $this->_serviceResource . "$transactionId",
            $params,
            Services_PayScrow_Apiclient_Interface::HTTP_POST
        );
        return $result['data'];
    }
    
    public function delete($identifier = null)
    {
        throw new Services_PayScrow_Exception( __CLASS__ . " does not support " . __METHOD__, "404");
    }

    public function update(array $itemData = array())
    {
        throw new Services_PayScrow_Exception( __CLASS__ . " does not support " . __METHOD__, "404" );
    }
}