<?php

class PayScrow_PayScrow_Helper_CustomerHelper extends Mage_Core_Helper_Abstract
{

    public function getCustomerName($object)
    {
        $custFirstName = $object->getBillingAddress()->getFirstname();
        $custLastName = $object->getBillingAddress()->getLastname();
        $custFullName = $custFirstName . " " . $custLastName;
        return $custFullName;
    }

    public function getCustomerEmail($object)
    {
        $email = $object->getCustomerEmail();

        if (empty($email)) {
            $email = $object->getBillingAddress()->getEmail();
        }

        return $email;
    }


    public function getClientData()
    {
        $clients = new Services_PayScrow_Clients(
            Mage::helper('payScrow/optionHelper')->getPrivateKey(),
            Mage::helper('payScrow')->getApiUrl()
        );

        $clientId = Mage::helper("payScrow/fastCheckoutHelper")->getClientId();

        $client = null;
        if (!empty($clientId)) {
            $client = $clients->getOne($clientId);
            if (!array_key_exists('email', $client)) {
                $client = null;
            }
        }

        return $client;
    }


    public function getUserId()
    {
        $result = null;
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $result = Mage::getSingleton('customer/session')->getId();
        }

        return $result;
    }

}