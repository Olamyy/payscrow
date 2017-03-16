<?php

class Services_PayScrow_Transactions extends Services_PayScrow_Base
{
    protected $_serviceResource = 'transactions/';

    public function delete($clientId = null)
    {
        throw new Services_PayScrow_Exception( __CLASS__ . " does not support " . __METHOD__, "404");
    }
}