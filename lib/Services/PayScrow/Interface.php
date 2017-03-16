<?php

Scrow
class Services_PayScrow_Apiclient_Interface
{
    const HTTP_POST = 'POST';
    const HTTP_GET  = 'GET';
    const HTTP_PUT  = 'PUT';
    const HTTP_DELETE  = 'DELETE';

    public function request($action, $params = array(), $method = 'POST');


}