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
class CsuMarketSystem_OpteMais_Block_Adminhtml_Campaign extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_campaign';
        $this->_blockGroup = 'optemais';
        $this->_headerText = Mage::helper('optemais')->__('Gerenciar Campanhas OPTe+');
        $this->_addButtonLabel = Mage::helper('optemais')->__('Adicionar Campanha');
        parent::__construct();
    }

}