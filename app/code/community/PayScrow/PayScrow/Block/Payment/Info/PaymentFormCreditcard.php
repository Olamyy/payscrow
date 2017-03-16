<?php

class PayScrow_PayScrow_Block_Payment_Info_PaymentFormCreditcard extends Mage_Payment_Block_Info
{


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('payScrow/payment/info/creditcard.phtml');
    }
    

    public function toPdf()
    {
        $this->setTemplate('payScrow/payment/info/creditcard_pdf.phtml');
        return $this->toHtml();
    }
    

    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);

        $data = $this->getInfo()->getAdditionalInformation();
        $data['imgUrl'] = Mage::helper('payScrow')->getImagePath() . "icon_payScrow.png";
        

        return $transport->setData(array_merge($data, $transport->getData()));
    }

}
