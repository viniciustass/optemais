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
class CsuMarketSystem_OpteMais_Model_Campaign extends CsuMarketSystem_OpteMais_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('optemais/campaign');
    }

    protected function _beforeSave()
    {
        foreach ($this->getData() as $key => $value) {
            if (in_array($key, array('from_date', 'to_date')) && $value) {
                $value = Mage::app()->getLocale()->date(
                    $value,
                    Varien_Date::DATE_INTERNAL_FORMAT,
                    null,
                    false
                );
                $this->setData($key, $value);
            } elseif($key == 'payment_methods') {
                $serialized = serialize($value);
                $this->setData($key,$serialized);
            }
        }
        return parent::_beforeSave();
    }

    protected function _afterLoad() {
        foreach ($this->getData() as $key => $value) {
            if($key == 'payment_methods') {
                $unserialized = unserialize($value);
                $this->setData($key,$unserialized);
            }
        }
        return parent::_afterLoad();
    }

    public function isValid()
    {
        if ($this->getId()) {
            if (!$this->getIsActive()) {
                return false;
            }
            if (!$this->getFromDate() && !$this->getToDate()) {
                return true;
            } elseif ($this->getFromDate() && !$this->getToDate()) {
                return strtotime("today") >= strtotime($this->getFromDate());
            } elseif ($this->getFromDate() && $this->getToDate()) {
                return strtotime("today") >= strtotime($this->getFromDate()) && strtotime("today") <= strtotime($this->getToDate());
            }
        }
        return false;
    }

    public function getToOptions($onlyActive = false)
    {
        $items = array();
        $items[null] = Mage::helper('optemais')->__('-- Escolha uma Campanha --');
        foreach ($this->getCollection() as $item) {
            if ($onlyActive && !$item->getIsActive()) {
                continue;
            }
            $items[$item->getEntityId()] = $item->getName();
        }
        return $items;
    }

    public function addCampaign(Mage_Core_Controller_Request_Http $request)
    {
        $campaign = $this;
        $campaign->setId(null);
        $campaign->setName($request->getParam('name'));
        $campaign->setDescription($request->getParam('description'));
        $campaign->setIsActive($request->getParam('is_active'));
        $campaign->setPaymentMethods($request->getParam('payment_methods'));
        $campaign->setFromDate($request->getParam('from_date'));
        $campaign->setToDate($request->getParam('to_date'));
        $campaign->save();
        if ($campaign->getId()) {
            $this->addItemToCampaign($request, $campaign->getId());
            return true;
        }
        return false;
    }

    public function validateProducts(Mage_Core_Controller_Request_Http $request, $campaignId)
    {
        $products = explode(',', $request->getParam('products'));
        /** @var CsuMarketSystem_OpteMais_Model_Resource_Campaign_Item_Collection $campaignCollection */
        $campaignCollection = Mage::getModel('optemais/campaign_item')->getCollection();
        $campaignCollection->addFieldToFilter('product_id',array('in'=>$products));
        $campaignCollection->addFieldToFilter('campaign_id',$campaignId);
        return !$campaignCollection->getSize();
    }

    public function addItemToCampaign(Mage_Core_Controller_Request_Http $request, $campaignId)
    {
        $products = explode(',', $request->getParam('products'));
        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
            ->addIdFilter($products)
            ->joinField(
                'qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        $campaignItem = Mage::getModel('optemais/campaign_item');
        /** @var Mage_Catalog_Model_Product $product */
        foreach ($productCollection as $product) {
            $campaignItem->setId(null);
            $campaignItem->setCampaignId($campaignId);
            $campaignItem->setProductId($product->getId());
            $campaignItem->setPrice($this->_applyAdjust($product->getPrice(), $request->getParam('price')));
            $campaignItem->setStockQty($this->_applyAdjust($product->getQty(), $request->getParam('stock_qty')));
            $campaignItem->save();
        }
        return true;
    }

    private function _applyAdjust($value, $adjust)
    {
        if (strpos($adjust, '%')) {
            $adjust = str_replace(array(',', '%'), array('.', ''), $adjust);
            $percent = $value * (1 + $adjust / 100);
            if (strpos($adjust, '-')) {
                $value -= $percent;
            } else {
                $value += $percent;
            }
        } else {
            $adjust = str_replace(',', '.', $adjust);
            $value += $adjust;
        }
        return $value;
    }

}