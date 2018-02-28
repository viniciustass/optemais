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
class CsuMarketSystem_OpteMais_Block_Adminhtml_Campaign_Item extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'optemais';
        $this->_controller = 'adminhtml_campaign_item';
        $this->_updateButton('save', 'label', Mage::helper('optemais')->__('Salvar'));
        $this->_removeButton('delete');
        $this->_removeButton('reset');
    }

    public function getHeaderText()
    {
        return Mage::helper('optemais')->__('Adicionar itens à Campanha');
    }

}