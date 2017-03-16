<?php

class PayScrow_PayScrow_TokenController extends Mage_Core_Controller_Front_Action
{
    public function totalAction()
    {
        echo Mage::helper('payScrow/paymentHelper')->getAmount();
    }
    
    public function logAction()
    {
        $post = $this->getRequest()->getPost();
        if (array_key_exists('error', $post) && array_key_exists('apierror', $post['error'])) {
            Mage::helper('payScrow/loggingHelper')->log(
                "Token creation failed for the following reason: " . $post['error']['apierror'], 
                print_r($post['error'], true)
            );
        } else {
            Mage::helper('payScrow/loggingHelper')->log(
                "Token creation failed for the following reason: Unkown reason."
            );
        }
    }
}