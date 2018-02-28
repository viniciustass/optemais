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
require_once(Mage::getModuleDir('controllers', 'CsuMarketSystem_OpteMais') . DS . 'ApiController.php');

class CsuMarketSystem_OpteMais_Api_ProductController extends CsuMarketSystem_OpteMais_ApiController
{

    public function listAllAction()
    {
        /** @var CsuMarketSystem_OpteMais_Model_Product $product */
        $product = Mage::getModel('optemais/product');
        $currentPage = $this->getRequest()->getParam('page',0);
        $this->getResponseApi()->setStatusValue(CsuMarketSystem_OpteMais_Helper_Data::STATUS_CODE_SUCCESS);
        $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::STATUS_CODE_SUCCESS));
        return $this->_setResponse($product->getProducts(CsuMarketSystem_OpteMais_Model_Product::MODE_FULL,0,$currentPage));
    }

    public function listPartialAction()
    {
        $fromHours = $this->getRequest()->getParam('from_hours');
        if (!$fromHours) {
            $this->getResponseApi()->setStatusValue(CsuMarketSystem_OpteMais_Helper_Data::STATUS_CODE_INVALID);
            $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_INVALID, $this->__('Parametro [from_hours] invalido')));
            return $this->_setResponse();
        }
        /** @var CsuMarketSystem_OpteMais_Model_Product $product */
        $product = Mage::getModel('optemais/product');
        $this->getResponseApi()->setStatusValue(CsuMarketSystem_OpteMais_Helper_Data::STATUS_CODE_SUCCESS);
        $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_SUCCESS));
        return $this->_setResponse($product->getProducts(CsuMarketSystem_OpteMais_Model_Product::MODE_PARTIAL, $fromHours));
    }

    public function listAvailabilityAction()
    {
        $currentPage = $this->getRequest()->getParam('page',0);
        /** @var CsuMarketSystem_OpteMais_Model_Product $product */
        $product = Mage::getModel('optemais/product');
        $this->getResponseApi()->setStatusValue(CsuMarketSystem_OpteMais_Helper_Data::STATUS_CODE_SUCCESS);
        $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_SUCCESS));
        return $this->_setResponse($product->getAvailabilityProducts($currentPage));
    }

    public function getProductBySkuAction()
    {
        $sku = $this->getRequest()->getParam('sku');
        /** @var CsuMarketSystem_OpteMais_Model_Product $product */
        $product = Mage::getModel('optemais/product');
        $response = $product->getProductBySku($sku);
        $this->getResponseApi()->setMessageKey('Mensagem');
        $this->getResponseApi()->setStatusKey('Status');
        if ($response['status']) {
            $info = $response['data'];
            $this->getResponseApi()->setStatusValue(true);
            $this->getResponseApi()->setMessageValue(null);
            return $this->_setResponse($info);
        } else {
            if (isset($response['not_found'])) {
                $this->getResponseApi()->setStatusValue(CsuMarketSystem_OpteMais_Helper_Data::STATUS_CODE_NOT_FOUND);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_NOT_FOUND, $response['message']));
                return $this->_setResponse();
            } else {
                $this->getResponseApi()->setStatusValue(CsuMarketSystem_OpteMais_Helper_Data::STATUS_CODE_ERROR);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_ERROR, $response['message']));
                return $this->_setResponse();
            }
        }
    }

}