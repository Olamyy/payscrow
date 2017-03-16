<?php

class PayScrow_PayScrow_Block_Payment_Info_PaymentFormDirectdebit extends Mage_Payment_Block_Info
{


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('payScrow/payment/info/directdebit.phtml');
    }
    

    public function toPdf()
    {
        $this->setTemplate('payScrow/payment/info/directdebit_pdf.phtml');
        return $this->toHtml();
    }
    

    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);

        $data = array();
        $data['payScrowTransactionId'] = $this->getInfo()->getAdditionalInformation('payScrowTransactionId');
        $data['payScrowPrenotificationDate'] = $this->getInfo()->getAdditionalInformation('payScrowPrenotificationDate');
        $data['imgUrl'] = Mage::helper('payScrow')->getImagePath() . "icon_payScrow.png";

        return $transport->setData(array_merge($data, $transport->getData()));
    }

}
