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
class CsuMarketSystem_OpteMais_Block_Adminhtml_Campaign_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'optemais';
        $this->_controller = 'adminhtml_campaign';

        $this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('adminhtml')->__('Delete'));

        $this->_addButton(
            'saveandcontinue', array(
                'label'   => Mage::helper('adminhtml')->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ), -100
        );

        $this->_formScripts[]
            = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        /** @var $campaign */
        $campaign = Mage::registry('optemais_campaign');
        if ($campaign && $campaign->getId()) {
            return Mage::helper('optemais')->__("Editar Campanha '%s'", self::escapeHtml($campaign->getName()));
        } else {
            return Mage::helper('optemais')->__('Adicionar Campanha');
        }
    }

}