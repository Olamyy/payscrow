<?php

class PayScrow_PayScrow_Block_Adminhtml_Hook_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('payScrow')->__('hook_data')));

        $fieldset->addField('hook_url', 'text', array(
            'name'  => 'hook_url',
            'class' => 'required-entry',
            'label' => Mage::helper('payScrow')->__('hook_url'),
            'title' => Mage::helper('payScrow')->__('hook_url'),
            'required' => true,
            'value' => Mage::getUrl('payScrow/hook/execute', array('_secure' => true))
        ));
        
        $fieldset->addField('hook_types', 'multiselect', array(
            'label'    => Mage::helper('payScrow')->__('hook_types'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'hook_types',
            'values'   => Mage::getSingleton('payScrow/source_hooks')->toOptionArray(),
            'value' => array('refund.succeeded', 'transaction.succeeded', 'chargeback.executed')
        ));

        $form->setAction($this->getUrl('*/*/save'));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
