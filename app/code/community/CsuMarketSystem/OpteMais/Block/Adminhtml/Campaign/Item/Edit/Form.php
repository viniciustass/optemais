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
class CsuMarketSystem_OpteMais_Block_Adminhtml_Campaign_Item_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/confirmItems'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $fieldsetAction = $form->addFieldset('campaign_item_form_action', array('legend' => Mage::helper('optemais')->__('Ação')));

        $instructions = '<p><b>Para o ajuste dos valores, siga as recomendações abaixo:</b></p>';
        $instructions .= '<li><b>Incremento</b>: Para aumentar o valor dos produtos em relação ao valor original (como está no cadastro), utilize o sinal de + antes do valor. <b>[Exemplo: +10.00]</b>.</li>';
        $instructions .= '<li><b>Decremento</b>: Para reduzir o valor dos produtos em relação ao valor original, utilize o sinal de - antes do valor. <b>[Exemplo: -10.00]</b>.</li>';
        $instructions .= '<li><b>Percentual</b>: Para operações com percentual, utilize o sinal % no final do valor. <b>[Exemplo: +10%]</b>.</li>';
        $instructions .= '<li><b>Original</b>: Para manter os valores originais, ignorar os campos de ajustes (Preço e Estoque).</b></li>';

        $fieldsetAction->addField(
            'action', 'select', array(
                'name'               => 'action',
                'label'              => Mage::helper('optemais')->__('Adicionar em'),
                'title'              => Mage::helper('optemais')->__('Adicionar em'),
                'required'           => true,
                'value'              => '1',
                'onchange'           => "changeAction(this)",
                'values'             => Mage::getModel('optemais/source_campaign_actions')->getOptions(),
                'after_element_html' => '
                    <script type="text/javascript">
                        function changeAction(e) {
                            if (e.value == 2) {
                                $$(\'.new-campaign\').invoke(\'up\',\'tr\').invoke(\'hide\');
                                $(\'name\').removeClassName(\'required-entry\');
                                $(\'campaign\').addClassName(\'required-entry\');
                                $$(\'.existing-campaign\').invoke(\'up\',\'tr\').invoke(\'show\');
                            } else {
                                $$(\'.new-campaign\').invoke(\'up\',\'tr\').invoke(\'show\');
                                $$(\'.existing-campaign\').invoke(\'up\',\'tr\').invoke(\'hide\');
                                $(\'name\').addClassName(\'required-entry\');
                                $(\'campaign\').removeClassName(\'required-entry\');
                            }
                        }
                        document.observe("dom:loaded", function() {
                            $$(\'.existing-campaign\').invoke(\'up\',\'tr\').invoke(\'hide\');
                            $(\'campaign\').removeClassName(\'required-entry\');
                            $(\'campaign_item_form_values\').down().insert({before: \'' . $instructions . '\'});
                        });
                    </script>',
            )
        );

        $fieldset = $form->addFieldset('campaign_item_form_new', array('legend' => Mage::helper('optemais')->__('Dados da Campanha')));

        $fieldset->addField(
            'products', 'hidden', array(
                'name'  => 'products',
                'value' => implode(',', $this->getRequest()->getParam('product'))
            )
        );

        $fieldset->addField(
            'name', 'text', array(
                'name'     => 'name',
                'label'    => Mage::helper('optemais')->__('Nome'),
                'title'    => Mage::helper('optemais')->__('Nome'),
                'required' => true,
                'class'    => 'new-campaign'
            )
        );

        $fieldset->addField(
            'description', 'textarea', array(
                'name'  => 'description',
                'label' => Mage::helper('optemais')->__('Descrição'),
                'title' => Mage::helper('optemais')->__('Descrição'),
                'style' => 'height: 100px;',
                'class' => 'new-campaign'
            )
        );

        $fieldset->addField(
            'is_active', 'select', array(
                'label'    => Mage::helper('optemais')->__('Status'),
                'title'    => Mage::helper('optemais')->__('Status'),
                'name'     => 'is_active',
                'required' => true,
                'options'  => Mage::getModel('optemais/source_campaign_statuses')->getOptions(),
                'class'    => 'new-campaign',
                'value'    => CsuMarketSystem_OpteMais_Model_Source_Campaign_Statuses::STATE_ACTIVE
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
                'class'        => 'new-campaign',
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
                'class'        => 'new-campaign'
            )
        );

        $fieldset->addField(
            'campaign', 'select', array(
                'label'    => Mage::helper('optemais')->__('Campanha'),
                'title'    => Mage::helper('optemais')->__('Campanha'),
                'name'     => 'campaign',
                'required' => true,
                'options'  => Mage::getModel('optemais/campaign')->getToOptions(true),
                'class'    => 'existing-campaign',
                'note'     => Mage::helper('optemais')->__('<small>Somente campanhas ativas</small>'),
            )
        );

        $fieldsetValues = $form->addFieldset('campaign_item_form_values', array('legend' => Mage::helper('optemais')->__('Ajuste de Valores do Produto')));

        $fieldsetValues->addField(
            'price', 'text', array(
                'name'  => 'price',
                'label' => Mage::helper('optemais')->__('Preço'),
                'title' => Mage::helper('optemais')->__('Preço'),
                'style' => 'width: 90px;',
            )
        );

        $fieldsetValues->addField(
            'stock_qty', 'text', array(
                'name'  => 'stock_qty',
                'label' => Mage::helper('optemais')->__('Estoque'),
                'title' => Mage::helper('optemais')->__('Estoque'),
                'style' => 'width: 90px;',
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}