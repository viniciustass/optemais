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
class CsuMarketSystem_OpteMais_Block_Adminhtml_Campaign_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('optemais_campaign_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('optemais/campaign')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id', array(
                'header' => Mage::helper('optemais')->__('ID'),
                'align'  => 'center',
                'width'  => '30px',
                'index'  => 'entity_id',
            )
        );

        $this->addColumn(
            'nome', array(
                'header' => Mage::helper('optemais')->__('Nome'),
                'index'  => 'name',
            )
        );

        $this->addColumn(
            'is_active', array(
                'header'  => Mage::helper('optemais')->__('Status'),
                'align'   => 'left',
                'width'   => '80px',
                'index'   => 'is_active',
                'type'    => 'options',
                'options' => Mage::getModel('optemais/source_campaign_statuses')->getOptions()
            )
        );
        $this->addExportType('*/*/exportCsv', Mage::helper('optemais')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('optemais')->__('XML'));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('campaign');

        $this->getMassactionBlock()->addItem(
            'delete', array(
                'label'   => Mage::helper('optemais')->__('Excluir'),
                'url'     => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('optemais')->__('Você tem certeza?')
            )
        );

        $statuses = Mage::getModel('optemais/source_campaign_statuses')->getOptions();

        $this->getMassactionBlock()->addItem(
            'status', array(
                'label'      => Mage::helper('optemais')->__('Alterar Status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current' => true)),
                'additional' => array(
                    'visibility' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('optemais')->__('Status'),
                        'values' => $statuses
                    )
                )
            )
        );
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}