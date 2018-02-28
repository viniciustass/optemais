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
class CsuMarketSystem_OpteMais_Model_Observer
{

    public function addMassActionToProductGrid($observer)
    {
        if (!Mage::helper('optemais')->isActive()) {
            return $this;
        }
        /** @var $block Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract */
        $block = $observer->getBlock();
        $block->getMassactionBlock()->addItem(
            'optemais_add_to_campaign', array(
                'label' => $block->__('Adicionar à Campanha OPTe+'),
                'url'   => $block->getUrl('adminhtml/optemais_campaign/addItems'),
            )
        );
    }

    public function afterUpdateAttributes($observer)
    {
        $productIds = $observer->getProductIds();
        if (!$productIds) {
            $productIds = $observer->getProducts();
        }
        if (!$productIds) {
            return false;
        }
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->update(
                'catalog_product_entity',
                array('updated_at' => Mage::getSingleton('core/date')->gmtDate()),
                array('entity_id IN (?)' => $productIds)
            );

    }

}
