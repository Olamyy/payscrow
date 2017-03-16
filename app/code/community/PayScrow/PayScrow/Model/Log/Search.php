<?php


class PayScrow_PayScrow_Model_Log_Search extends Varien_Object
{


    public function load()
    {
        $arr = array();
        $searchText = $this->getQuery();
        $collection = Mage::getModel('payScrow/log')->getCollection()
                ->addFieldToFilter(
                    array('dev_info', 'dev_info_additional'), array(
                        array('like' => '%' . $searchText . '%'),
                        array('like' => '%' . $searchText . '%')
                    )
                )
                ->load();

        foreach ($collection as $model) {
            $arr[] = array(
                'id' => 'payScrow/search/' . $model->getId(),
                'type' => Mage::helper('adminhtml')->__('PayScrow Log Entry'),
                'name' => $model->getMerchantInfo(),
                'description' => $model->getEntryDate(),
                'url' => Mage::helper('adminhtml')->getUrl('payScrow/adminhtml_log/view', array('id' => $model->getId())),
            );
        }

        $this->setResults($arr);
        return $this;
    }

}