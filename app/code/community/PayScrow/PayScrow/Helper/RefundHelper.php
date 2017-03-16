<?php




class PayScrow_PayScrow_Helper_RefundHelper extends Mage_Core_Helper_Abstract
{


    private function validateRefund($refund)
    {
        //Logs errorfeedback in case of any other response than ok
        if (isset($refund['data']['response_code']) && $refund['data']['response_code'] !== 20000) {
            Mage::helper('payScrow/loggingHelper')->log("An Error occured: " . $refund['data']['response_code'], var_export($refund, true));
            return false;
        }

        //Logs feedback in case of an unset id
        if (!isset($refund['id']) && !isset($refund['data']['id'])) {
            Mage::helper('payScrow/loggingHelper')->log("No Refund created.", var_export($refund, true));
            return false;
        } else { //Logs success feedback for debugging purposes
            Mage::helper('payScrow/loggingHelper')->log("Refund created.", $refund['id'], var_export($refund, true));
        }

        return true;
    }


    public function createRefund($creditmemo, $payment)
    {
        //Gather Data
        try {
            $refundsObject = new Services_PayScrow_Refunds(
                Mage::helper('payScrow/optionHelper')->getPrivateKey(),
                Mage::helper('payScrow')->getApiUrl()
            );
        } catch (Exception $ex) {
            Mage::helper('payScrow/loggingHelper')->log("No Refund created due to illegal parameters.", $ex->getMessage());
            return false;
        }

        //Create Refund
        $params = array(
            'transactionId' => $payment->getAdditionalInformation('payScrowTransactionId'),
            'source' => Mage::helper('payScrow')->getSourceString(),
            'params' => array('amount' => (int) Mage::helper("payScrow/paymentHelper")->getAmount($creditmemo))
        );
        
        Mage::helper('payScrow/loggingHelper')->log("Try to refund.", var_export($params, true));
        
        try {
            $refund = $refundsObject->create($params);
        } catch (Exception $ex) {
            Mage::helper('payScrow/loggingHelper')->log("No Refund created.", $ex->getMessage(), var_export($params, true));
            return false;
        }
        
        //Validate Refund and return feedback
        return $this->validateRefund($refund);
    }
    
    public function creditmemo(Mage_Sales_Model_Order $order, $refundId)
    {        
        if ($order->canCreditmemo()) {

            $service = Mage::getModel('sales/service_order', $order);
            $creditmemo = $service->prepareCreditmemo();
            
            $creditmemo->setOfflineRequested(true);
            
            $creditmemo->register();
            
            Mage::getModel('core/resource_transaction')
               ->addObject($creditmemo)
               ->addObject($creditmemo->getOrder())
               ->save();
            
            $creditmemo->setTransactionId($refundId);
            
            $creditmemo->save();
        }
    }
}