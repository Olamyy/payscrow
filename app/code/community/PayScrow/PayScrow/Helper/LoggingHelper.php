<?php




class PayScrow_PayScrow_Helper_LoggingHelper extends Mage_Core_Helper_Abstract implements Services_PayScrow_LoggingInterface
{


    public function log($merchantInfo, $devInfo = null, $devInfoAdditional = null)
    {
        Mage::getModel('payScrow/log')->log($merchantInfo, $devInfo, $devInfoAdditional);
    }


    public function getEntries()
    {
        $collection = Mage::getModel('payScrow/log')->getCollection();
        return $collection;
    }

}