<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    CsuMarketSystem
 * @package     CsuMarketSystem_OpteMais
 * @copyright   Copyright (c) 2016 CSU MarketSystem [www.csu.com.br]
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsuMarketSystem_OpteMais_Block_Adminhtml_Campaign_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('campaign_form', array('legend' => Mage::helper('optemais')->__('Informação da Campanha')));

        $fieldset->addField(
            'name', 'text', array(
                'name'     => 'name',
                'label'    => Mage::helper('optemais')->__('Nome'),
                'title'    => Mage::helper('optemais')->__('Nome'),
                'required' => true,
            )
        );

        $fieldset->addField(
            'description', 'textarea', array(
                'name'  => 'description',
                'label' => Mage::helper('optemais')->__('Descrição'),
                'title' => Mage::helper('optemais')->__('Descrição'),
                'style' => 'height: 100px;',
            )
        );

        $fieldset->addField(
            'freight_discount', 'text', array(
                'name'  => 'freight_discount',
                'label' => Mage::helper('optemais')->__('Desconto no Frete'),
                'title' => Mage::helper('optemais')->__('Desconto no Frete'),
                'note'  => Mage::helper('optemais')->__('<small>Percentual de desconto aplicado ao frete.</small>'),
            )
        );

        $fieldset->addField(
            'is_active', 'select', array(
                'label'    => Mage::helper('optemais')->__('Status'),
                'title'    => Mage::helper('optemais')->__('Status'),
                'name'     => 'is_active',
                'required' => true,
                'options'  => Mage::getModel('optemais/source_campaign_statuses')->getOptions()
            )
        );

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField(
            'from_date', 'date', array(
                'name'         => 'from_date',
                'label'        => Mage::helper('optemais')->__('Iniciar Em'),
                'title'        => Mage::helper('optemais')->__('Iniciar Em'),
                'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format'       => $dateFormatIso,
                'note'         => Mage::helper('optemais')->__('<small>Data inicial de validade para a Campanha.</small>'),

            )
        );
        $fieldset->addField(
            'to_date', 'date', array(
                'name'         => 'to_date',
                'label'        => Mage::helper('optemais')->__('Finalizar Em'),
                'title'        => Mage::helper('optemais')->__('Finalizar Em'),
                'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format'       => $dateFormatIso,
                'note'         => Mage::helper('optemais')->__('<small>Data final de validade para a Campanha.</small>'),
            )
        );

        $field = $fieldset->addField(
            'payment_methods', 'text', array(
                'name'     => 'payment_methods',
                'label'    => Mage::helper('optemais')->__('Métodos de Pagamento e Parcelamento'),
                'title'    => Mage::helper('optemais')->__('Métodos de Pagamento e Parcelamento'),
                'required' => true,
            )
        );
        $field->setRenderer($this->getLayout()->createBlock('optemais/adminhtml_paymentmethod'));

        if (Mage::registry('optemais_campaign')) {

            $form->setValues(Mage::registry('optemais_campaign')->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

}