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
class CsuMarketSystem_OpteMais_Block_Adminhtml_Paymentmethod extends Mage_Adminhtml_Block_System_Config_Form_Field {
    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);
        $html = '<div id="payment_method_template" style="display:none">';
        $html .= $this->_getRowTemplateHtml();
        $html .= '</div>';
        $html .= '<ul id="payment_method_container">';
        if ($this->_getValue('payment_method_code')) {
            foreach ($this->_getValue('payment_method_code') as $i => $f) {
                if ($i) {
                    $html .= $this->_getRowTemplateHtml($i);
                }
            }
        }
        $html .= '</ul>';
        $html .= $this->_getAddRowButtonHtml('payment_method_container', 'payment_method_template', $this->__('Adicionar'));
        return $html;
    }

    protected function _getRowTemplateHtml($i = 0) {
        $html = '<li><fieldset>';
        $html .= '<label>' . $this->__('Método de Pagamento') . '</label>';
        $html .= '<select name="' . $this->getElement()->getName() . '[payment_method_code][]" ' . $this->_getDisabled() . '>';
        $html .= '<option value="">' . Mage::helper('adminhtml')->__('-- Please Select --') . '</option>';
        foreach ($this->_getPaymentMethods() as $id => $title) {
            $html .= '<option value="' . $id . '" '
                . $this->_getSelected('payment_method_code/' . $i, $id)
                . ' style="background:white;">' . $title . '</option>';
        }
        $html .= '</select>';
        $html .= '<p class="note">Método de Pagamento</p>';
        $html .= '<label>' . $this->__('Quantidade de Parcelas') . '</label>';
        $html .= '<select name="' . $this->getElement()->getName() . '[qty_installments][]" ' . $this->_getDisabled() . '>';
        foreach ($this->_getInstallments() as $item) {
            $html .= '<option value="' . $item['value'] . '" '
                . $this->_getSelected('qty_installments/' . $i, $item['value'])
                . ' style="background:white;">' . $item['label'] . '</option>';
        }
        $html .= '</select>';
        $html .= '<p class="note">Número maximo de parcelas permitida para o Método de Pagamento (Selecione somente quando houver parcelamento).</p>';


        $html .= '<label>' . $this->__('Juros aplicado às parcelas') . '</label>';
        $html .= '<input class="input-text" type="text" name="' . $this->getElement()->getName() . '[interest][]" value="' . $this->_getValue('interest/' . $i) . '" ' . $this->_getDisabled() . ' />';
        $html .= '<p class="note">Júros que será aplicado ao parcelamento (Preencha somente quando houver parcelamento e júros). Exemplo: 1.9%</p>';

        $html .= '<label>' . $this->__('Parâmetro esperado para receber informação de parcelamento') . '</label>';
        $html .= '<input class="input-text" type="text" name="' . $this->getElement()->getName() . '[param_installment][]" value="' . $this->_getValue('param_installment/' . $i) . '" ' . $this->_getDisabled() . ' />';
        $html .= '<p class="note">Nome do parâmetro esperado no Método de Pagamento para informação do parcelamento (varia de acordo com cada extensão de Método de Pagamento no Magento). Exemplo: cc_installlments</p>';


        $html .= $this->_getRemoveRowButtonHtml();
        $html .= '</fieldset></li>';

        return $html;
    }

    protected function _getDisabled() {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }

    protected function _getValue($key) {
        return $this->getElement()->getData('value/' . $key);
    }

    protected function _getSelected($key, $value) {
        return $this->getElement()->getData('value/' . $key) == $value ? 'selected="selected"' : '';
    }

    protected function _getAddRowButtonHtml($container, $template, $title = 'Adicionar') {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('add ' . $this->_getDisabled())
                ->setLabel($this->__($title))
                ->setOnClick("Element.insert($('" . $container . "'), {bottom: $('" . $template . "').innerHTML})")
                ->setDisabled($this->_getDisabled())
                ->toHtml();
        }
        return $this->_addRowButtonHtml[$container];
    }

    protected function _getRemoveRowButtonHtml($selector = 'li', $title = 'Remover') {
        if (!$this->_removeRowButtonHtml) {
            $this->_removeRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('delete v-middle ' . $this->_getDisabled())
                ->setLabel($this->__($title))
                ->setOnClick("Element.remove($(this).up('" . $selector . "'))")
                ->setDisabled($this->_getDisabled())
                ->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }

    /**
     * Retrieves payment methods
     *
     * @return array
     */
    public function _getPaymentMethods() {
        $payments = Mage::getSingleton('payment/config')->getAllMethods();
        $methods = array();
        foreach ($payments as $code => $model) {
            $title = Mage::getStoreConfig('payment/' . $code . '/title');
            $methods[$code] = $title . ' (' . $code . ')';
        }
        return $methods;
    }

    /**
     * Retrieves Yes/NO
     *
     * @return array
     */
    public function _getInstallments() {
        $installments = array();
        $installments[] = array('label'=>Mage::helper('optemais')->__('Nenhuma'),'value'=>0);
        for ($x = 1; $x <= 24; $x++) {
            $installments[] = array('label'=>$x . 'x','value'=>$x);
        }
        return $installments;
    }
}