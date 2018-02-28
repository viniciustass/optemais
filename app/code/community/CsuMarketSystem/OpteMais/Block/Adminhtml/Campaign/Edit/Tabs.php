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
class CsuMarketSystem_OpteMais_Block_Adminhtml_Campaign_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('optemais_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('optemais')->__('Campanha'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'form', array(
                'label'   => Mage::helper('optemais')->__('Dados'),
                'alt'     => Mage::helper('optemais')->__('Dados'),
                'content' => $this->getLayout()->createBlock('optemais/adminhtml_campaign_edit_tab_form')->toHtml(),
            )
        );
        $this->addTab(
            'products', array(
                'label'   => Mage::helper('optemais')->__('Produtos da Campanha'),
                'title'   => Mage::helper('optemais')->__('Produtos da Campanha'),
                'content' => $this->getLayout()->createBlock('optemais/adminhtml_campaign_edit_tab_product_grid')->toHtml()
            )
        );
        return parent::_beforeToHtml();
    }

}