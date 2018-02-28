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
class CsuMarketSystem_OpteMais_Model_Shipping
{

    public function getShippingRates($params)
    {
        $zipCode = isset($params['CepEntrega']) ? preg_replace('/[^0-9]/', '', $params['CepEntrega']) : false;
        $campaignId = isset($params['IdCampanha']) ? $params['IdCampanha'] : null;
        $items = isset($params['ProdutoFrete']) ? $params['ProdutoFrete'] : array();
        $response = array();
        $response['status'] = false;
        if ($zipCode && $items) {
            $freightDiscount = 0;
            if ($campaignId) {
                /** @var CsuMarketSystem_OpteMais_Model_Campaign $campaign */
                $campaign = Mage::getModel('optemais/campaign')->load($campaignId);
                if ($campaign->isValid()) {
                    $freightDiscount = $campaign->getFreightDiscount();
                }
            }
            $cartInfo = array();
            /** @var $estimateRate CsuMarketSystem_OpteMais_Model_Estimate_Rate */
            $estimateRate = Mage::getSingleton('optemais/estimate_rate');
            $configDeliveryTimeVariable = Mage::getStoreConfig('optemais/config_shipping/delivery_time_variable');
            $configDeliveryTimeDefault = Mage::getStoreConfig('optemais/config_shipping/delivery_time_default');
            $configPriceType = Mage::getStoreConfig('optemais/config_shipping/price_type');
            $allowedShippingMethods = Mage::getStoreConfig('optemais/config_shipping/allowed_shipping_methods') != 'all'
                ? explode(',',Mage::getStoreConfig('optemais/config_shipping/allowed_shipping_methods'))
                : false;
            try {
                $itemsRate = array();
                $biggestRateDeliveryTime = 0;
                foreach ($items as $item) {
                    /** @var $product Mage_Catalog_Model_Product */
                    $product = Mage::getModel('catalog/product');
                    $product->load($product->getIdBySku($item['Sku']));
                    if ($product) {
                        $cInfo = array(
                            'product' => $product->getId(),
                            'qty'     => $item['Quantidade'],
                        );
                        $cartInfo[] = $cInfo;
                        $product->setAddToCartInfo($cInfo);
                        $estimateRate->setProduct($product);
                        $addressInfo = array(
                            'cart'       => 0,
                            'country_id' => "BR",
                            'postcode'   => $zipCode
                        );
                        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                        $estimateRate->setAddressInfo($addressInfo);
                        if (!empty($cartInfo) || !empty($addressInfo)) {
                            try {
                                $result = $estimateRate->estimate();
                                $price = -1;
                                if (is_array($result)) {
                                    foreach ($result as $rates) {
                                        foreach ($rates as $rate) {
                                            if($allowedShippingMethods) {
                                                if(!in_array($rate->getCarrier(),$allowedShippingMethods)) continue;
                                            }
                                            $deliveryTime = Mage::registry($rate->getCode() .'_' . $configDeliveryTimeVariable)
                                                ? Mage::registry($rate->getCode() .'_' . $configDeliveryTimeVariable)
                                                : $configDeliveryTimeDefault;
                                            $skip = true;
                                            $isBiggestPriceConfig = ($configPriceType == CsuMarketSystem_OpteMais_Model_Source_Shipping_Pricetype::BIGGEST_PRICE);
                                            if ($isBiggestPriceConfig && $price < $rate->getPrice()) {
                                                $price = $rate->getPrice();
                                                $skip = false;
                                            } elseif (($price > $rate->getPrice() || $price == -1 || $rate->getPrice() == 0) && (!$isBiggestPriceConfig)) {
                                                $price = $rate->getPrice();
                                                $skip = false;
                                            }
                                            if (!$skip) {
                                                $finalPrice = $freightDiscount > 0 && $price > 0
                                                    ? $price - ($price * ($freightDiscount / 100))
                                                    : $price;
                                                $stockQty = 0;
                                                if($stockItem->getIsInStock()) {
                                                    $stockQty = intval($stockItem->getQty());
                                                    if (Mage::getStoreConfigFlag('optemais/config_product/discount_min_qty')) {
                                                        $stockQty -= intval($stockItem->getMinQty());
                                                    }
                                                }
                                                $itemsRate['Sku'] = $product->getSku();
                                                $itemsRate['ValorFrete'] = $finalPrice;
                                                $itemsRate['QuantidadeEstoque'] = $stockQty;
                                                $itemsRate['FlagDisponivel'] = $stockQty > 0;
                                                $itemsRate['TempoEntrega'] = intval($deliveryTime);
                                                $itemsRate['Mensagem'] = $rate->getErrorMessage();
                                                if ($biggestRateDeliveryTime < $deliveryTime) {
                                                    $biggestRateDeliveryTime = $deliveryTime;
                                                }
                                            }
                                        }
                                    }
                                }
                                $response['status'] = true;
                            } catch (Exception $e) {
                                $response['message'] = Mage::helper('optemais')->__($e->getMessage());
                                Mage::logException($e);
                                return $response;
                            }
                        }
                    }
                    unset($cartInfo);
                    $estimateRate->resetQuote();
                }

                if (!$itemsRate) {
                    throw new Exception(Mage::helper('optemais')->__('Sem resultados para os parâmetros informados'));
                }

                $response['data'] = array();
                $response['data']['ValorTotalFrete'] = $itemsRate['ValorFrete'];
                $response['data']['TempoEntrega'] = intval($biggestRateDeliveryTime);
                $response['data']['ItensFrete'] = $itemsRate;
                $response['data']['Status'] = true;
                $response['data']['Mensagem'] = '';
            } catch (Exception $e) {
                $response['message'] = Mage::helper('optemais')->__($e->getMessage());
                $response['status'] = false;
                Mage::logException($e);
            }
        } else {
            if (!$zipCode) {
                $response['invalid_params'][] = 'CEP';
            }
            if (!$items) {
                $response['invalid_params'][] = 'Produtos';
            }
        }
        return $response;
    }

}
