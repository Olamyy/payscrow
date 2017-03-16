<?php
Scrow


class Services_PayScrow_Exception extends Exception
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}