<?php

class PayScrow_PayScrow_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{


    protected $_isFilterAllowed = true;


    protected $_isSortable = true;


    public function __construct()
    {
        parent::__construct();
        $this->setId('log_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }


    protected function _isFilterAllowed()
    {
        return $this->_isFilterAllowed;
    }


    protected function _isSortable()
    {
        return $this->_isSortable;
    }


    public function getMassactionBlock()
    {
        return $this->getChild('massaction')->setErrorText(Mage::helper('payScrow')->__('payScrow_error_text_no_entry_selected'));
    }


    protected function _prepareCollection()
    {
        $collection = Mage::getModel('payScrow/log')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entry_date', array(
            'header' => Mage::helper('payScrow')->__('payScrow_backend_log_entry_date'),
            'index' => 'entry_date',
        ));
        $this->addColumn('version', array(
            'header' => Mage::helper('payScrow')->__('payScrow_backend_log_version'),
            'index' => 'version',
        ));
        $this->addColumn('merchant_info', array(
            'header' => Mage::helper('payScrow')->__('payScrow_backend_log_merchant_info'),
            'index' => 'merchant_info',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('log_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('payScrow')->__('payScrow_action_delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('payScrow')->__('payScrow_dialog_confirm'),
        ));

        return $this;
    }

}
