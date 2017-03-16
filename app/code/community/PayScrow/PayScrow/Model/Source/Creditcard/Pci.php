<?php


class PayScrow_PayScrow_Model_Source_Creditcard_Pci
{

    public function toOptionArray()
    {
        $pciMode = array(
            array(
                'label' => Mage::helper('core')->__('PayFrame (min. PCI SAQ A)'),
                'value' => 'SAQ A'
            ),
            array(
                'label' => Mage::helper('core')->__('direct integration (min. PCI SAQ A-EP)'),
                'value' => 'SAQ A-EP'
            )
        );

        return $pciMode;
    }
}