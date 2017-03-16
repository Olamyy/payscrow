<?php


class PayScrow_PayScrow_Model_Fastcheckout extends Mage_Core_Model_Abstract
{


    public function _construct()
    {
        parent::_construct();
        $this->_init('payScrow/fastcheckout');
    }


    public function getPaymentId($userId, $code)
    {
        $collection = Mage::getModel('payScrow/fastcheckout')->getCollection();
        $collection->addFilter('user_id', $userId);
        $obj = $collection->getFirstItem();
        if ($code === "payScrow_creditcard") {
            return $obj->getCcPaymentId();
        }

        if ($code === "payScrow_directdebit") {
            return $obj->getElvPaymentId();
        }
    }


    public function saveFcData($paymentMethodCode, $userId, $clientId, $paymentId)
    {
        $logger = Mage::helper("payScrow/loggingHelper");
        $collection = Mage::getModel('payScrow/fastcheckout')->getCollection();
        $collection->addFilter('user_id', $userId);
        $customerExists = $collection->count();

        if ($customerExists == 1) {
            $obj = $collection->getFirstItem();

            $obj->setClientId($clientId)->save();
            
            if ($paymentMethodCode === 'payScrow_creditcard') {
                $logger->log("Saving Fast Checkout Data", "Customer data already exists. Saving CC only Data.");
                $obj->setCcPaymentId($paymentId)
                        ->save();
            }

            if ($paymentMethodCode === 'payScrow_directdebit') {
                $logger->log("Saving Fast Checkout Data", "Customer data already exists. Saving ELV only Data.");
                $obj->setElvPaymentId($paymentId)
                        ->save();
            }
            return true;
        }

        //Insert into db
        if ($paymentMethodCode === 'payScrow_creditcard') {
            $logger->log("Saving Fast Checkout Data", "Customer data saved with CC data");
            $this->setId(null)
                    ->setUserId($userId)
                    ->setClientId($clientId)
                    ->setCcPaymentId($paymentId)
                    ->save();
            return true;
        }

        if ($paymentMethodCode === 'payScrow_directdebit') {
            $logger->log("Saving Fast Checkout Data", "Customer data saved with ELV data");
            $this->setId(null)
                    ->setUserId($userId)
                    ->setClientId($clientId)
                    ->setElvPaymentId($paymentId)
                    ->save();
            return true;
        }

        return false;
    }


    public function hasFcData($userId, $code)
    {
        $collection = Mage::getModel('payScrow/fastcheckout')->getCollection();
        $collection->addFilter('user_id', $userId);

        if ($code === "payScrow_creditcard") {
            $obj = $collection->getFirstItem();
            if ($obj->getCcPaymentId() != null) {
                return true;
            }
        }

        if ($code === "payScrow_directdebit") {
            $obj = $collection->getFirstItem();
            if ($obj->getElvPaymentId() != null) {
                return true;
            }
        }
        return false;
    }

}