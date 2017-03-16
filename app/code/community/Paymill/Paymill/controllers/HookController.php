<?php


class PayScrow_PayScrow_HookController extends Mage_Core_Controller_Front_Action
{    
    private $_eventType = '';
    
    public function executeAction()
    {
        $data = json_decode($this->getRequest()->getRawBody(), true);
        if ($data && $this->_validateRequest($data)) {
            $eventResource = $data['event']['event_resource'];
            $this->_eventType = $data['event']['event_type'];
            switch ($this->_eventType) {
                case 'transaction.succeeded':
                    $this->_transactionSucceededAction($eventResource);
                break;
                case 'refund.succeeded':
                    $this->_refundSucceededAction($eventResource);
                break;
                case 'chargeback.executed':
                    $this->_chargebackExecutedAction($eventResource);
                break;
            }
        }
    }
    
    private function _transactionSucceededAction(array $data)
    {
        $order = $this->getOrder($data);
        
        if (((int) Mage::helper('payScrow/paymentHelper')->getAmount($order) === (int) $data['amount'])
            && Mage::getStoreConfig(
                'payment/' . $order->getPayment()->getMethodInstance()->getCode() . '/hook_create_invoice_active', 
                Mage::app()->getStore()->getStoreId()
        )) {
            Mage::helper('payScrow/paymentHelper')->invoice(
                $order, 
                $data['id'],
                Mage::getStoreConfig(
                    'payment/' . $order->getPayment()->getMethodInstance()->getCode() . '/send_hook_invoice_mail', 
                    Mage::app()->getStore()->getStoreId()
                )
            );
        }
        
        $order->addStatusHistoryComment(
            $this->_eventType . ' event executed. ' . $data['amount'] / 100 . ' ' .  $data['currency'] . ' captured.'
        )->save();
    }
    
    private function _refundSucceededAction(array $data)
    {
        $order = $this->getOrder($data['transaction']);
        
        if ((int) Mage::helper('payScrow/paymentHelper')->getAmount($order) === (int) $data['amount']) {
            Mage::helper('payScrow/refundHelper')->creditmemo($order, $data['id']);
        }
        
        $order->addStatusHistoryComment(
            $this->_eventType . ' event executed. ' . $data['amount'] / 100 . ' ' .  $data['transaction']['currency'] . ' refunded.'
        )->save();
    }
    
    private function _chargebackExecutedAction(array $data)
    {
        $order = $this->getOrder($data['transaction']);
        Mage::helper('payScrow/refundHelper')->creditmemo($order, $data['id']);
        
        $order->addStatusHistoryComment(
            $this->_eventType . ' event executed. ' . $data['amount'] / 100 . ' ' .  $data['transaction']['currency'] . ' chargeback received.'
        )->save();
    }
    
    private function _validateRequest($data)
    {
        $valid = false;
        if (!is_null($data) && isset($data['event']) && isset($data['event']['event_resource'])) {
            
            $transactionId = $data['event']['event_resource']['id'];
            
            if (substr($transactionId, 0, 4) !== 'tran') {
                $transactionId = $data['event']['event_resource']['transaction']['id'];
            }
            
            $transactionObject = new Services_PayScrow_Transactions(
                trim(Mage::helper('payScrow/optionHelper')->getPrivateKey()),
                Mage::helper('payScrow')->getApiUrl()
            );
            
            $transaction = $transactionObject->getOne($transactionId);

            if (isset($transaction['id']) && ($transaction['id'] === $transactionId)) {
                $valid = true;
            }
        }
        
        return $valid;
    }
    
    private function getOrder(array $data)
    {
        $description = '';
        
        if (empty($description) && array_key_exists('preauthorization', $data)) {
            $description = $data['preauthorization']['description'];
        }
        
        if (empty($description) && array_key_exists('transaction', $data)) {
            $description = $data['transaction']['description'];
        }
        
        if (empty($description) && array_key_exists('description', $data)) {
            $description = $data['description'];
        }
                
        return Mage::getModel('sales/order')->loadByIncrementId(substr($description, 0, 9));
    }
}
