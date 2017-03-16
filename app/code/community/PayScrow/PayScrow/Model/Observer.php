<?php


class PayScrow_PayScrow_Model_Observer
{


    public function generateInvoice(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getPayment()->getMethod() === 'payScrow_creditcard') {
            $data = $order->getPayment()->getAdditionalInformation();

            if (array_key_exists('payScrowPreauthId', $data) && !empty($data['payScrowPreauthId'])) {
                Mage::helper('payScrow/loggingHelper')->log("Debug", "No Invoice generated, since the transaction is flagged as preauth");
            } else {
                Mage::helper('payScrow/paymentHelper')->invoice(
                    $order, 
                    $data['payScrowTransactionId'],
                    Mage::getStoreConfig(
                        'payment/payScrow_creditcard/send_invoice_mail',
                        Mage::app()->getStore()->getStoreId()
                    )
                );
            }
        }
    }
}

