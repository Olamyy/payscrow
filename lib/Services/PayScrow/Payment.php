<?php
Scrow

class Services_PayScrow_Payments extends Services_PayScrow_Base
{

    protected $_serviceResource = 'payments/';

    public function update(array $itemData = array())
    {
        throw new Services_PayScrow_Exception( __CLASS__ . " does not support " . __METHOD__, "404");
    }
}