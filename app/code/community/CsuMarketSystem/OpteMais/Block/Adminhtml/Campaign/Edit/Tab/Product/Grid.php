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
class CsuMarketSystem_OpteMais_Block_Adminhtml_Campaign_Edit_Tab_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('optemais_campaign_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('optemais/campaign_item')->getCollection();
        $productName = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'name');
        $productSku = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'sku');
        $collection->getSelect()
            ->columns(
                array(
                    'product_name' => 'product_attribute_name.value',
                    'product_sku'  => 'product_attribute_sku.sku'
                )
            )
            ->join(
                array('product_attribute_name' => $productName->getBackendTable()),
                'main_table.product_id = product_attribute_name.entity_id',
                array()
            )
            ->join(
                array('product_attribute_sku' => $productSku->getBackendTable()),
                'main_table.product_id = product_attribute_sku.entity_id',
                array()
            )
            ->where("product_attribute_name.attribute_id = ?", $productName->getId())
            ->where("product_attribute_name.store_id = ?", Mage_Core_Model_App::ADMIN_STORE_ID)
            ->where("main_table.campaign_id = ?", Mage::registry('optemais_campaign')->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _getStore()
    {
        return Mage::app()->getStore();
    }


    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header' => Mage::helper('optemais')->__('ID'),
                'align'  => 'right',
                'index'  => 'entity_id',
            )
        );

        $this->addColumn(
            'product_sku', array(
                'header'       => Mage::helper('optemais')->__('SKU'),
                'align'        => 'left',
                'index'        => 'product_sku',
                'filter_index' => 'product_attribute_sku.sku'
            )
        );

        $this->addColumn(
            'product_name', array(
                'header'       => Mage::helper('optemais')->__('Nome'),
                'align'        => 'left',
                'index'        => 'product_name',
                'filter_index' => 'product_attribute_name.value'
            )
        );

        $store = $this->_getStore();
        $this->addColumn(
            'price', array(
                'header'        => Mage::helper('optemais')->__('Preço'),
                'type'          => 'price',
                'index'         => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),

            )
        );

        $this->addColumn(
            'stock_qty', array(
                'header' => Mage::helper('optemais')->__('Estoque'),
                'type'   => 'number',
                'index'  => 'stock_qty'
            )
        );


        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/productGrid', array('_current' => true));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');
        $this->getMassactionBlock()->addItem(
            'delete', array(
                'label'   => Mage::helper('optemais')->__('Excluir'),
                'url'     => $this->getUrl('*/*/massDeleteItem'),
                'confirm' => Mage::helper('optemais')->__('Você tem certeza?')
            )
        );
        return $this;
    }

}