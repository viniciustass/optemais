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
class CsuMarketSystem_OpteMais_Model_Estimate_Rate
{
    /**
     * Customer object,
     * if customer isn't logged in it quals to false
     *
     * @var Mage_Customer_Model_Customer|boolean
     */
    protected $_customer = null;

    /**
     * Sales quote object to add products for shipping estimation
     *
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = null;

    /**
     * Product model
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_products = array();

    /**
     * Estimation result
     *
     * @var array
     */
    protected $_result = array();

    /**
     * Delivery address information
     *
     * @var array
     */
    protected $_addressInfo = null;

    /**
     * Product Parent Prices
     *
     * @var array
     */
    protected $_productParentPrices = array();


    /**
     * Set address info for estimation
     *
     * @param array $info
     *
     * @return $this
     */
    public function setAddressInfo($info)
    {
        $this->_addressInfo = $info;
        return $this;
    }

    /**
     * Retrieve address information
     *
     * @return array
     */
    public function getAddressInfo()
    {
        return $this->_addressInfo;
    }

    /**
     * Set a product for the estimation
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return $this
     */
    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        if(!$product->isSalable()) {
            Mage::throwException(
                Mage::helper('optemais')->__('Produto indisponível.')
            );
        }
        if ($product->isConfigurable()) {
            $cartInfo = $product->getAddToCartInfo();
            /** @var Mage_Catalog_Model_Product $_product */
            $_product = false;
            if (isset($cartInfo['related_product'])) {
                if ($cartInfo['related_product'] != '') {
                    $_product = Mage::getModel('catalog/product')->load($cartInfo['related_product']);
                } else {
                    $collection = $product->getTypeInstance()->getUsedProductCollection();
                    foreach ($collection as $_product) {
                        if ($_product->isSalable()) {
                            break;
                        }
                    }
                    $_product->load($_product->getId());
                }
            } else {
                $collection = $product->getTypeInstance()->getUsedProductCollection();
                foreach ($collection as $_product) {
                    if ($_product->isSalable()) {
                        break;
                    }
                }
                $_product->load($_product->getId());
            }
            if ($cartInfo && $_product) {
                $_product->setAddToCartInfo($cartInfo);
            }
        } else {
            $_product = $product;
        }
        $this->_products[] = $_product;
        return $this;
    }

    /**
     * Retrieve product for the estimation
     */
    public function getProducts()
    {
        return $this->_products;
    }

    /**
     * Retrieve shipping rate result
     *
     * @return array|null
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Retrieve list of shipping rates
     *
     * @return array
     */
    public function estimate()
    {

        $addressInfo = (array)$this->getAddressInfo();
        if (empty($addressInfo['cart'])) {
            $this->resetQuote();
        }

        if (!isset($addressInfo['country_id'])) {
            Mage::throwException(
                Mage::helper('optemais')->__('Por favor, especifique um país válido.')
            );
        }

        $shippingAddress = $this->getQuote()->getShippingAddress();

        $shippingAddress->setCountryId($addressInfo['country_id']);

        if (isset($addressInfo['region_id'])) {
            $shippingAddress->setRegionId($addressInfo['region_id']);
        }

        if (isset($addressInfo['postcode'])) {
            $shippingAddress->setPostcode($addressInfo['postcode']);
        }

        if (isset($addressInfo['region'])) {
            $shippingAddress->setRegion($addressInfo['region']);
        }

        if (isset($addressInfo['city'])) {
            $shippingAddress->setCity($addressInfo['city']);
        }

        $shippingAddress->setCollectShippingRates(true);

        if (isset($addressInfo['coupon_code'])) {
            $this->getQuote()->setCouponCode($addressInfo['coupon_code']);
        }

        $products = $this->getProducts();

        $this->getQuote()->removeAllItems();

        foreach ($products as $product) {
            $addToCartInfo = (array)$product->getAddToCartInfo();
            if (!($product instanceof Mage_Catalog_Model_Product) || !$product->getId()) {
                Mage::throwException(
                    Mage::helper('optemais')->__('Por favor, especifique um produto válido.')
                );
            }
            $request = new Varien_Object($addToCartInfo);
            if ($product->getStockItem()) {
                $minimumQty = $product->getStockItem()->getMinSaleQty();
                if ($minimumQty > 0 && $request->getQty() < $minimumQty) {
                    $request->setQty($minimumQty);
                }
            }
            $result = $this->getQuote()->addProduct($product, $request);


            if(Mage::getStoreConfigFlag('optemais/config_product/use_parent_price')) {
                if($parentId = $this->_getParentId($product->getId())) {
                    /** @var Mage_Catalog_Model_Product $productParent */
                    $productParent = Mage::getModel('catalog/product')->load($parentId);
                    $item = $this->getQuote()->getItemByProduct($product);
                    $item->setCustomPrice($productParent->getFinalPrice());
                    $item->setOriginalCustomPrice($productParent->getFinalPrice());
                    $item->getProduct()->setIsSuperMode(true);
                }
            }

            if (is_string($result)) {
                Mage::throwException($result);
            }
            Mage::dispatchEvent(
                'checkout_cart_product_add_after',
                array('quote_item' => $result, 'product' => $product)
            );
        }
        $this->getQuote()->collectTotals();
        $this->_result = $shippingAddress->getGroupedAllShippingRates();
        return $this->_result;
    }

    /**
     * Retrieve sales quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }


    /**
     * Reset quote object
     *
     * @return $this
     */
    public function resetQuote()
    {
        $this->getQuote()->removeAllAddresses();

        if ($this->getCustomer()) {
            $this->getQuote()->setCustomer($this->getCustomer());
        }

        return $this;
    }

    /**
     * Retrieve currently logged in customer,
     * if customer isn't logged it returns false
     *
     * @return Mage_Customer_Model_Customer|boolean
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            $customerSession = Mage::getSingleton('customer/session');
            if ($customerSession->isLoggedIn()) {
                $this->_customer = $customerSession->getCustomer();
            } else {
                $this->_customer = false;
            }
        }

        return $this->_customer;
    }

    /**
     * Retrieve parent product ID
     *
     * @return string|boolean
     */
    private function _getParentId($childId)
    {
        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
            ->getParentIdsByChild($childId);
        return is_array($parentIds) && isset($parentIds[0]) ? $parentIds[0] : false;
    }

}