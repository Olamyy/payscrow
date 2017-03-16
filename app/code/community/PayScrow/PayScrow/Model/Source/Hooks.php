<?php



class PayScrow_PayScrow_Model_Source_Hooks
{

    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('payScrow')->__('refund.succeeded'),
                'value' => 'refund.succeeded'
            ), array(
                'label' => Mage::helper('payScrow')->__('transaction.succeeded'),
                'value' => 'transaction.succeeded'
            ), array(
                'label' => Mage::helper('payScrow')->__('chargeback.executed'),
                'value' => 'chargeback.executed'
            )
        );
    }
}