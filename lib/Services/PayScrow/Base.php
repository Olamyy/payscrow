<?php


abstract class Services_PayScrow_Base
{
    
    protected $_serviceResource = null;

    
    protected $_httpClient;

    
    public function __construct($apiKey, $apiEndpoint)
    {
        $this->_httpClient = new Services_PayScrow_Apiclient_Curl($apiKey, $apiEndpoint);
    }

    
    public function get($filters = array(), $identifier = '')
    {
        $response = $this->_httpClient->request(
            $this->_serviceResource . $identifier,
            $filters,
            Services_PayScrow_Apiclient_Interface::HTTP_GET
        );

        return $response['data'];
    }

    
    public function getOne($identifier = null)
    {
        if (!$identifier) {
            return null;
        }

        $filters = array("count" => 1, 'offset' => 0);

        return $this->get($filters, $identifier);
    }

    
    public function delete($clientId = null)
    {
        $response =  $this->_httpClient->request(
            $this->_serviceResource . $clientId,
            array(),
            Services_PayScrow_Apiclient_Interface::HTTP_DELETE
        );

        return $response['data'];
    }

    
    public function create($itemData = array())
    {
        $response = $this->_httpClient->request(
            $this->_serviceResource,
            $itemData,
            Services_PayScrow_Apiclient_Interface::HTTP_POST
        );

        return $response['data'];
    }

    
    public function update(array $itemData = array())
    {
        if (!isset($itemData['id']) ) {
            return null;
        }

        $itemId = $itemData['id'];
        unset ($itemData['id']);

        $response = $this->_httpClient->request(
            $this->_serviceResource . $itemId,
            $itemData,
            Services_PayScrow_Apiclient_Interface::HTTP_PUT
        );

        return $response['data'];
    }

    
    public function getResponse(){
        return $this->_httpClient->getResponse();
    }
}