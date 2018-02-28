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
class CsuMarketSystem_OpteMais_Model_Product
{
    const MODE_FULL = 'full';
    const MODE_PARTIAL = 'partial';
    const IMAGE_FULL = 'Grande';
    const IMAGE_SMALL = 'Media';
    const IMAGE_THUMBNAIL = 'Pequena';

    /** @var Mage_Catalog_Model_Resource_Category_Collection */
    protected $_categoryCollection = null;

    protected $_productCollection = null;
    protected $_parentCategories = array();
    protected $_productParentChildIs = array();
    protected $_totalRecords = 0;

    public function getProducts($mode = self::MODE_FULL, $fromHours = 0, $currentPage = 0)
    {
        $collection = $this->_getProductCollection($currentPage);
        $items = array();
        $descriptionAttribute = Mage::getStoreConfig('optemais/config_attribute/attribute_product_description');
        $brandAttribute = Mage::getStoreConfig('optemais/config_attribute/attribute_product_brand');
        $colorAttribute = Mage::getStoreConfig('optemais/config_attribute/attribute_product_color');
        $sizeAttribute = Mage::getStoreConfig('optemais/config_attribute/attribute_product_size');
        $voltageAttribute = Mage::getStoreConfig('optemais/config_attribute/attribute_product_voltage');
        $eanAttribute = Mage::getStoreConfig('optemais/config_attribute/attribute_product_ean');
        $refIdAttribute = Mage::getStoreConfig('optemais/config_attribute/attribute_product_refid');
        $useParentImages = Mage::getStoreConfig('optemais/config_product/use_parent_images');
        $fromHours = strtotime("-$fromHours hours");
        /** @var $p Mage_Catalog_Model_Product */
        foreach ($collection as $p) {
            $categories = $this->_getParentCategories($p);
            // Não é aceito produtos sem categorias
            if (!$categories) {
                continue;
            }
            $categoryIds = implode("/", array_keys($categories));
            $skus = array();
            if ($p->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                if (!$p->getSku()) {
                    continue;
                }
                $childProducts = isset($this->_productParentChildIs[$p->getId()]) ? $this->_productParentChildIs[$p->getId()] : array();
                if ($childProducts) {
                    $hasChangedParent = strtotime($p->getUpdatedAt()) >= $fromHours;
                    $hasChangedChild = false;
                    /** @var $child Mage_Catalog_Model_Product */
                    foreach ($childProducts as $childId) {
                        $child = Mage::getModel('catalog/product')->load($childId);
                        if ($mode == self::MODE_PARTIAL && (strtotime($child->getUpdatedAt()) >= $fromHours)) {
                            $hasChangedChild = true;
                        }
                        $skuSpecification = $this->_getSkuSpecifications($child);
                        if (!$skuSpecification || !$child->getSku()) {
                            continue;
                        }
                        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($child);
                        $stockQty = 0;
                        if($stockItem->getIsInStock()) {
                            $stockQty = intval($stockItem->getQty());
                            if (Mage::getStoreConfigFlag('optemais/config_product/discount_min_qty')) {
                                $stockQty -= intval($stockItem->getMinQty());
                            }
                        }
                        if ($stockQty < 0) {
                            $stockQty = 0;
                        }
                        $useParentPrice = Mage::getStoreConfigFlag('optemais/config_product/use_parent_price');
                        $price = $useParentPrice ? $p->getPrice() : $child->getPrice();
                        $finalPrice = $useParentPrice ? $p->getFinalPrice() : $child->getFinalPrice();
                        $skus[] = array(
                            'CodSku'           => $child->getSku(),
                            'PrecoDe'          => number_format((float)$price, 2, '.', ''),
                            'PrecoPor'         => number_format((float)$finalPrice, 2, '.', ''),
                            'Estoque'          => intval($stockQty),
                            'Ativo'            => $child->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED ? true : false,
                            'Cor'              => $child->getData($colorAttribute) && $colorAttribute ? $child->getAttributeText($colorAttribute) : '',
                            'Tamanho'          => $child->getData($sizeAttribute) && $sizeAttribute ? $child->getAttributeText($sizeAttribute) : '',
                            'Voltagem'         => $child->getData($voltageAttribute) && $voltageAttribute ? $child->getAttributeText($voltageAttribute) : '',
                            'IdsAlternativo'   => array(
                                'Ean'   => $child->getData($eanAttribute) && $eanAttribute ? $child->getData($eanAttribute) : '',
                                'RefId' => $child->getData($refIdAttribute) && $refIdAttribute ? $child->getData($refIdAttribute) : '',
                            ),
                            'EspecificacaoSku' => $skuSpecification,
                            'Imagens'          => $this->_getImages($child, $p, $useParentImages)
                        );
                    }
                    if ($mode == self::MODE_PARTIAL && !$hasChangedChild && !$hasChangedParent) {
                        continue;
                    }
                }
            } else {
                if ($mode == self::MODE_PARTIAL && (strtotime($p->getUpdatedAt()) < $fromHours)) {
                    continue;
                }
                $skuSpecification = $this->_getSkuSpecifications($p);
                if (!$p->getSku()) {
                    continue;
                }
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($p);
                $stockQty = 0;
                if($stockItem->getIsInStock()) {
                    $stockQty = intval($stockItem->getQty());
                    if (Mage::getStoreConfigFlag('optemais/config_product/discount_min_qty')) {
                        $stockQty -= intval($stockItem->getMinQty());
                    }
                }
                if ($stockQty < 0) {
                    $stockQty = 0;
                }
                $skus[] = array(
                    'CodSku'           => $p->getSku(),
                    'PrecoDe'          => number_format((float)$p->getPrice(), 2, '.', ''),
                    'PrecoPor'         => number_format((float)$p->getFinalPrice(), 2, '.', ''),
                    'Estoque'          => intval($stockQty),
                    'Ativo'            => $p->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED ? true : false,
                    'Cor'              => $p->getData($colorAttribute) && $colorAttribute ? $p->getAttributeText($colorAttribute) : '',
                    'Tamanho'          => $p->getData($sizeAttribute) && $sizeAttribute ? $p->getAttributeText($sizeAttribute) : '',
                    'Voltagem'         => $p->getData($voltageAttribute) && $voltageAttribute ? $p->getAttributeText($voltageAttribute) : '',
                    'IdsAlternativo'   => array(
                        'Ean'   => $p->getData($eanAttribute) && $eanAttribute ? $p->getData($eanAttribute) : '',
                        'RefId' => $p->getData($refIdAttribute) && $refIdAttribute ? $p->getData($refIdAttribute) : '',
                    ),
                    'EspecificacaoSku' => $skuSpecification,
                    'Imagens'          => $this->_getImages($p)
                );
            }
            $items[] = array(
                'CodProduto'              => $p->getId(),
                'DescricaoDetalhaProduto' => $p->getData($descriptionAttribute),
                'NomeProduto'             => $p->getName(),
                'IdMarca'                 => $p->getData($brandAttribute),
                'NomeMarca'               => $p->getData($brandAttribute) && $brandAttribute ? $p->getAttributeText($brandAttribute) : '',
                'IdsCategorias'           => $categoryIds,
                'Categorias'              => $categories,
                'Sku'                     => $skus,
                'EspecificacaoProduto'    => $this->_getProductSpecifications($p),
            );
        }
        $result = array(
            'data' => array()
        );
        if ($currentPage) {
            $pageSize = Mage::getStoreConfig('optemais/config_product/items_per_page');
            $pagesCount = ceil($this->_totalRecords / $pageSize);
            $result['data']['current_page'] = $currentPage;
            $result['data']['pages_count'] = $pagesCount <= 0 ? 1 : $pagesCount;
        }
        $result['data']['rows_count'] = count($items);
        $result['data']['rows'] = $items;
        return $result;
    }

    public function getAvailabilityProducts($currentPage = 0)
    {
        $items = array();
        $collection = $this->_getProductCollection($currentPage, true);
        /** @var $p Mage_Catalog_Model_Product */
        foreach ($collection as $p) {
            if ($p->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $childProducts = isset($this->_productParentChildIs[$p->getId()]) ? $this->_productParentChildIs[$p->getId()] : array();
                if ($childProducts) {
                    /** @var $child Mage_Catalog_Model_Product */
                    foreach ($childProducts as $childId) {
                        $child = Mage::getModel('catalog/product')->load($childId);
                        $items[] = $this->_getAvailabilityDataProducts($child);
                    }
                }
            } else {
                $items[] = $this->_getAvailabilityDataProducts($p);
            }
        }
        $result = array(
            'data' => array()
        );
        if ($currentPage) {
            $pageSize = Mage::getStoreConfig('optemais/config_product/items_per_page');
            $pagesCount = ceil($collection->getSize() / $pageSize);
            $result['data']['current_page'] = $currentPage;
            $result['data']['pages_count'] = $pagesCount <= 0 ? 1 : $pagesCount;
        }
        $result['data']['rows_count'] = count($items);
        $result['data']['rows'] = $items;
        return $result;
    }

    private function _getAvailabilityDataProducts($product)
    {
        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $stockQty = 0;
        if($stockItem->getIsInStock()) {
            $stockQty = intval($stockItem->getQty());
            if (Mage::getStoreConfigFlag('optemais/config_product/discount_min_qty')) {
                $stockQty -= intval($stockItem->getMinQty());
            }
        }
        if ($stockQty < 0) {
            $stockQty = 0;
        }
        $campaignItems = Mage::getModel('optemais/campaign_item')->getItemsByParams(
            array(
                'product_id' => $product->getId(),
            )
        );
        $campaigns = array();
        if ($campaignItems) {
            foreach ($campaignItems as $campaignItem) {
                /** @var CsuMarketSystem_OpteMais_Model_Campaign $campaign */
                $campaign = Mage::getModel('optemais/campaign')->load($campaignItem['campaign_id']);
                if ($campaign->isValid()) {
                    $campaigns[] = array(
                        'IdCampanha'        => $campaignItem['campaign_id'],
                        'PrecoCampanha'     => number_format((float)$campaignItem['price'], 2, '.', ''),
                        'EstoqueCampanha'   => intval($campaignItem['stock_qty']),
                        'ProdutoDisponivel' => $campaignItem['stock_qty'] > 0,
                    );
                }
            }
        }
        return array(
            'CodSku'            => $product->getSku(),
            'CodProduto'        => $product->getId(),
            'PrecoDe'           => number_format((float)$product->getPrice(), 2, '.', ''),
            'PrecoPor'          => number_format((float)$product->getFinalPrice(), 2, '.', ''),
            'Estoque'           => intval($stockQty),
            'ProdutoDisponivel' => $stockQty > 0,
            'Campanha'          => $campaigns,
        );
    }

    /**
     * @param $currentPage integer
     * @param $onlyStock   boolean
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    private function _getProductCollection($currentPage = 0, $onlyStock = false)
    {
        if (is_null($this->_productCollection)) {
            $this->_prepareProductCollection($currentPage, $onlyStock);
        }
        return $this->_productCollection;
    }

    private function _prepareProductCollection($currentPage = 0, $onlyStock = false)
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->joinField(
            'category_id', 'catalog/category_product', 'category_id',
            'product_id = entity_id', null, 'inner'
        );
        if (!$onlyStock) {
            $collection->addPriceData();
        }
        $collection->addAttributeToSelect('*')->addCategoryIds();
        $collection->addAttributeToFilter(
            'type_id', array('in' => array(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE))
        );
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        if ($this->_getProductChildren()) {
            $collection->addAttributeToFilter('entity_id', array('nin' => $this->_getProductChildren()));
        }
        if (Mage::getStoreConfigFlag('optemais/config_product/ignore_invisible')) {
            $collection->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        }
        $this->_totalRecords = $collection->getSize();
        $collection->getSelect()->group('e.entity_id');
        $pageSize = Mage::getStoreConfig('optemais/config_product/items_per_page');
        if ($currentPage && $pageSize) {
            $collection->getSelect()->limitPage($currentPage, $pageSize);
        }
        $this->_productCollection = $collection;
    }

    private function _getProductChildren()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToFilter('type_id', array('in' => array(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)));
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $children = array();
        foreach ($collection as $p) {
            $childIds = $p->getTypeInstance(true)->getChildrenIds($p->getId(), false);
            $this->_productParentChildIs[$p->getId()] = current($childIds);
            if (is_array($childIds)) {
                $children += current($childIds);
            }
        }
        unset($collection);
        return $children;
    }

    private function _getParentId($childId)
    {
        $this->_getProductChildren();
        foreach ($this->_productParentChildIs as $parentId => $childIds) {
            if (in_array($childId, $childIds)) {
                return $parentId;
            }
        }
        return false;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Category_Collection|Mage_Catalog_Model_Resource_Category_Flat_Collection
     */
    private function _getCategoryCollection()
    {
        if (is_null($this->_categoryCollection)) {
            $this->_prepareCategoryCollection();
        }
        return $this->_categoryCollection;
    }

    private function _prepareCategoryCollection()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            $oldStore = Mage::app()->getStore()->getId();
            Mage::app()->setCurrentStore(0);
        }
        $this->_categoryCollection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addIsActiveFilter()
            ->addNameToResult();
        if (!Mage::app()->isSingleStoreMode()) {
            Mage::app()->setCurrentStore($oldStore);
        } else {
            if (Mage::helper('catalog/category_flat')->isEnabled()) {
                $this->_categoryCollection->addStoreFilter();
            }
        }
    }

    private function _getParentCategories(Mage_Catalog_Model_Product $product)
    {
        $tree = array();
        $categoryCollection = $this->_getCategoryCollection();
        if (!$categoryCollection) {
            return array();
        }
        $categoryIds = $product->getCategoryIds();
        $rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
        if ($categoryIds) {
            foreach ($categoryIds as $categoryId) {
                if ($categoryId == $rootCategoryId) {
                    continue;
                }
                /** @var Mage_Catalog_Model_Category $category */
                if ($category = $categoryCollection->getItemById($categoryId)) {
                    $path = array();
                    $pathInStore = $category->getPathInStore();
                    $pathIds = array_reverse(explode(',', $pathInStore));
                    $categories = $category->getParentCategories();
                    // Para o caso de categorias pais desativadas
                    if (count($pathIds) != count($categories)) {
                        continue;
                    }
                    foreach ($pathIds as $id) {
                        if ($id == $rootCategoryId) {
                            continue;
                        }
                        if (isset($categories[$id]) && $categories[$id]->getName()) {
                            $path[$id] = $categories[$id]->getName();
                        }
                    }
                    $tree[] = $path;
                }
            }
        }
        $max = 0;
        foreach ($tree as $key => $value) {
            if (count($value) > $max) {
                $max = $key;
            }
        }
        return count($tree) > 0 ? $tree[$max] : $tree;
    }


    private function _getImages(Mage_Catalog_Model_Product $product, $parentProduct = null, $useParentImages = false)
    {
        /** @var Mage_Catalog_Model_Product $product */
        if (!$product->getMediaGallery('images')) {
            $product->load('media_gallery');
        }
        $images = $product->getMediaGallery('images');
        if (!$images && is_object($parentProduct) && $useParentImages) {
            $product = $parentProduct;
            $product->load('media_gallery');
            $images = $product->getMediaGallery('images');
        }
        $items = array();
        foreach ($images as $image) {
            if ($image['disabled'] == "1") {
                continue;
            }
            $items[] = array(
                'ImagemUrl'     => $product->getMediaConfig()->getMediaUrl($image['file']),
                'ImagemNome'    => basename($image['file']),
                'ImagemTamanho' => self::IMAGE_THUMBNAIL,
                'ImagemId'      => isset($image['value_id']) ? $image['value_id'] : null,
                'Ordem'         => $image['position']
            );
            $items[] = array(
                'ImagemUrl'     => $product->getMediaConfig()->getMediaUrl($image['file']),
                'ImagemNome'    => basename($image['file']),
                'ImagemTamanho' => self::IMAGE_SMALL,
                'ImagemId'      => isset($image['value_id']) ? $image['value_id'] : null,
                'Ordem'         => $image['position']
            );
            $items[] = array(
                'ImagemUrl'     => $product->getMediaConfig()->getMediaUrl($image['file']),
                'ImagemNome'    => basename($image['file']),
                'ImagemTamanho' => self::IMAGE_FULL,
                'ImagemId'      => isset($image['value_id']) ? $image['value_id'] : null,
                'Ordem'         => $image['position']
            );
        }
        return $items;
    }

    private function _getProductSpecifications(Mage_Catalog_Model_Product $product)
    {
        $items = array();
        if ($attributes = Mage::getStoreConfig('optemais/config_attribute/product_specifications')) {
            $attributes = explode(",", $attributes);
            foreach ($attributes as $attributeCode) {
                $inputType = $product->getResource()
                    ->getAttribute($attributeCode)->getFrontend()->getInputType();
                if (in_array($inputType, array('select', 'multiselect'))) {
                    $attributeValue = $product->getResource()
                        ->getAttribute($attributeCode)->getSource()
                        ->getOptionText($product->getData($attributeCode));
                } else {
                    $attributeValue = $product->getResource()
                        ->getAttribute($attributeCode)->getFrontend()
                        ->getValue($product);
                }
                if($attributeValue === false) continue;
                $attributeLabel = $product->getResource()->getAttribute($attributeCode)->getFrontend()->getLabel();
                $attributeId = $product->getResource()->getAttribute($attributeCode)->getId();
                if(!is_array($attributeValue)) $attributeValue = array($attributeValue);
                $items[] = array(
                    'IdEspecificacaoProduto'    => $attributeId,
                    'NomeEspecificacaoProduto'  => $attributeLabel,
                    'ValorEspecificacaoProduto' => $attributeValue,
                );
            }
        }
        return $items;
    }

    private function _getSkuSpecifications(Mage_Catalog_Model_Product $product)
    {
        $items = array();
        $attributes = array();
        if ($colorAttribute = Mage::getStoreConfig('optemais/config_attribute/attribute_product_color')) {
            $attributes[] = $colorAttribute;
        }
        if ($sizeAttribute = Mage::getStoreConfig('optemais/config_attribute/attribute_product_size')) {
            $attributes[] = $sizeAttribute;
        }
        if ($voltageAttribute = Mage::getStoreConfig('optemais/config_attribute/attribute_product_voltage')) {
            $attributes[] = $voltageAttribute;
        }
        foreach ($attributes as $attributeCode) {
            if (!$product->getData($attributeCode)) {
                continue;
            }
            $attributeValue = $product->getResource()->getAttribute($attributeCode)->getFrontend()->getValue($product);
            $attributeLabel = $product->getResource()->getAttribute($attributeCode)->getFrontend()->getLabel();
            $attributeId = $product->getResource()->getAttribute($attributeCode)->getId();
            if ($attributeValue && $product->getResource()->getAttribute($attributeCode)->getIsConfigurable()) {
                $items[] = array(
                    'IdEspecificacaoSku'    => $attributeId,
                    'NomeEspecificacaoSku'  => $attributeLabel,
                    'ValorEspecificacaoSku' => $attributeValue,
                );
            }
        }
        return $items;
    }

    public function getProductBySku($sku)
    {
        try {
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product');
            $product->load($product->getIdBySku($sku));
            if ($product->getId()) {
                $price = $product->getPrice();
                $finalPrice = $product->getFinalPrice();
                if (Mage::getStoreConfigFlag('optemais/config_product/use_parent_price')) {
                    if ($parentId = $this->_getParentId($product->getId())) {
                        /** @var Mage_Catalog_Model_Product $parent */
                        $parent = Mage::getModel('catalog/product')->load($parentId);
                        if ($parent->getId()) {
                            $price = $parent->getPrice();
                            $finalPrice = $parent->getFinalPrice();
                        }
                    }
                }
                /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                $stockQty = 0;
                if($stockItem->getIsInStock()) {
                    $stockQty = intval($stockItem->getQty());
                    if (Mage::getStoreConfigFlag('optemais/config_product/discount_min_qty')) {
                        $stockQty -= intval($stockItem->getMinQty());
                    }
                }
                if ($stockQty < 0) {
                    $stockQty = 0;
                }
                $data = array(
                    'Sku'               => $product->getSku(),
                    'PrecoDe'           => number_format((float)$price, 2, '.', ''),
                    'PrecoPor'          => number_format((float)$finalPrice, 2, '.', ''),
                    'QuantidadeEstoque' => $stockQty,
                    'FlagDisponivel'    => $stockQty > 0 && $product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
                    'Nome'              => $product->getName(),
                );
                return array('status' => true, 'not_found' => false, 'data' => $data);
            }
            return array('status' => false, 'not_found' => true, 'message' => Mage::helper('optemais')->__('Produto'));

        } catch (Exception $e) {
            return array('status' => false, 'not_found' => false, 'message' => $e->getMessage());
        }
    }

}