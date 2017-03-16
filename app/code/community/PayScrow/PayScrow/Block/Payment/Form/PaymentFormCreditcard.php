<?php


class PayScrow_PayScrow_Block_Payment_Form_PaymentFormCreditcard extends PayScrow_PayScrow_Block_Payment_Form_PaymentFormAbstract
{


    private $creditCardLogosBrand = '';


    private $creditCardLogosDisplay = '';


    protected function _construct()
    {
        parent::_construct();

        $this->setPayScrowCcLogos();
        if(Mage::helper('payScrow/optionHelper')->getPci() === 'SAQ A-EP') {
            $this->setTemplate('payScrow/payment/form/creditcard.phtml');
        } else {
            $this->setTemplate('payScrow/payment/form/creditcard_form.phtml');
        }
    }


    public function getPayScrowCcMonths()
    {
        $months[0] = $this->__('Month');
        $months = array_merge($months, Mage::getSingleton('payment/config')->getMonths());

        return $months;
    }


    public function getPayScrowCcYears()
    {
        $years = Mage::getSingleton('payment/config')->getYears();
        $years = array(0 => $this->__('Year')) + $years;

        return $years;
    }
    
    public function getPaymentData($code)
    {
        $payment = parent::getPaymentData($code);
        
        $data = array();
        if (!empty($payment)) {
            $data['cc_number'] = '************' . $payment['last4'];
            $data['expire_year'] = $payment['expire_year'];
            $data['expire_month'] = $payment['expire_month'];
            $data['cvc'] = '***';
            $data['card_holder'] = $payment['card_holder'];
            $data['card_type'] = $payment['card_type'];
        }
        
        return $data;
    }

    private function setPayScrowCcLogos()
    {

        $cards = explode(',', Mage::getStoreConfig('payment/payScrow_creditcard/specificcreditcard'));
        $this->creditCardLogosDisplay = '';
        $this->creditCardLogosBrand = 'var payScrowCcBrands = new Array();';

        if(!empty($cards)) {
            foreach($cards as $card) {
                $this->creditCardLogosDisplay .= sprintf(
                    '<img style="display: inline" src="%s" alt="%s"/>',
                    $this->getSkinUrl('images/payScrow/icon_32x20_' . $card . '.png'),
                    $card
                );
                $this->creditCardLogosBrand .= sprintf(
                     "\n" . 'payScrowCcBrands.push("%s");',
                    $card
                );
            }
        }
    }

    public function getCreditCardLogosBrand() 
    {
        return $this->creditCardLogosBrand;
    }

    public function getCreditCardLogosDisplay() 
    {
        return $this->creditCardLogosDisplay;
    }
}
