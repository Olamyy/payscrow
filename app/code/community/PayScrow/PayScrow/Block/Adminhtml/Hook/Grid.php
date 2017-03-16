<?php

class PayScrow_PayScrow_Block_Adminhtml_Hook_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected $_isFilterAllowed = true;


    protected $_isSortable = true;


    public function __construct()
    {
        parent::__construct();
        $this->setId('hook_grid');
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
        $this->setCollection($this->_getHookCollection());
        return parent::_prepareCollection();
    }


    protected function _getHookCollection()
    {
        $data = Mage::helper("payScrow/hookHelper")->getAllHooks();

        if ($data) {
            $collection = new Varien_Data_Collection();
            foreach ($data as $value) {
                $obj = new Varien_Object();
                $obj->addData(array(
                    'id' => $value['id'],
                    'target' => !array_key_exists('url', $value) ? $value['email'] : $value['url'],
                    'live' => $value['livemode'] ? 'live' : 'test',
                    'event_types' => implode(', ', $value['event_types'])
                ));

                $collection->addItem($obj);
            }

            return $collection;
        }

        return null;
    }


    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('payScrow')->__('payScrow_backend_hook_id'),
            'index' => 'id',
        ));

        $this->addColumn('event_types', array(
            'header' => Mage::helper('payScrow')->__('payScrow_backend_hook_event_types'),
            'index' => 'event_types',
        ));

        $this->addColumn('target', array(
            'header' => Mage::helper('payScrow')->__('payScrow_backend_hook_target'),
            'index' => 'target',
        ));

        $this->addColumn('live', array(
            'header' => Mage::helper('payScrow')->__('payScrow_backend_hook_live'),
            'index' => 'live',
        ));

        return parent::_prepareColumns();
    }


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('hook_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('payScrow')->__('payScrow_action_delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('payScrow')->__('payScrow_dialog_confirm'),
        ));

        return $this;
    }

}
