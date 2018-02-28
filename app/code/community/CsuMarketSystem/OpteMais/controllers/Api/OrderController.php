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

class CsuMarketSystem_OpteMais_Api_OrderController extends CsuMarketSystem_OpteMais_ApiController
{

    public function createOrderAction()
    {
        $orderData = json_decode($this->getRequest()->getRawBody(), true);
        /** @var CsuMarketSystem_OpteMais_Model_Order $order */
        $order = Mage::getModel('optemais/order');
        $response = $order->createOrder($orderData);
        $this->getResponseApi()->setMessageKey('Mensagem');
        $this->getResponseApi()->setStatusKey('Status');
        if ($response['Status']) {
            $this->getResponseApi()->setStatusValue(true);
            $this->getResponseApi()->setMessageValue(null);
        } else {
            $this->getResponseApi()->setStatusValue(false);
            $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_ERROR, $response['Mensagem']));
        }
        return $this->_setResponse($response);
    }

    public function changeStatusAction()
    {
        /** @var CsuMarketSystem_OpteMais_Model_Order $order */
        $order = Mage::getModel('optemais/order');
        $response = $order->changeStatus($this->getRequest()->getParams());
        $this->getResponseApi()->setMessageKey('Mensagem');
        $this->getResponseApi()->setStatusKey('Status');
        $info = $response['data'];
        if ($response['status']) {
            $this->getResponseApi()->setStatusValue(true);
            $this->getResponseApi()->setMessageValue(null);
        } else {
            if (isset($response['invalid_params'])) {
                $invalidParams = implode(",", $response['invalid_params']);
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_INVALID, $invalidParams));
            } else {
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_ERROR, $response['message']));
            }
        }
        return $this->_setResponse($info);
    }

    public function getTrackingInfoAction()
    {
        /** @var CsuMarketSystem_OpteMais_Model_Order $order */
        $order = Mage::getModel('optemais/order');
        $response = $order->getTrackingInfo($this->getRequest()->getParams());
        $this->getResponseApi()->setMessageKey('Mensagem');
        $this->getResponseApi()->setStatusKey('Status');
        if ($response['status']) {
            $info = $response['data'];
            $this->getResponseApi()->setStatusValue(true);
            $this->getResponseApi()->setMessageValue(null);
            return $this->_setResponse($info);
        } else {
            if (isset($response['not_found']) && $response['not_found']) {
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_NOT_FOUND, $response['message']));
            } elseif (isset($response['invalid_params'])) {
                $invalidParams = implode(",", $response['invalid_params']);
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_INVALID, $invalidParams));
            } else {
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_ERROR, $response['message']));
            }
            return $this->_setResponse();
        }
    }

    public function getPaymentMethodsAction()
    {
        /** @var CsuMarketSystem_OpteMais_Model_Order $order */
        $order = Mage::getModel('optemais/order');
        $response = $order->getPaymentMethods($this->getRequest()->getParams('IdCampanha',null));
        $this->getResponseApi()->setMessageKey('Mensagem');
        $this->getResponseApi()->setStatusKey('Status');
        if ($response['status']) {
            $info = $response['data'];
            $this->getResponseApi()->setStatusValue(true);
            $this->getResponseApi()->setMessageValue(null);
            return $this->_setResponse($info);
        } else {
            if (isset($response['not_found']) && $response['not_found']) {
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_NOT_FOUND, $response['message']));
            } elseif (isset($response['invalid_params'])) {
                $invalidParams = implode(",", $response['invalid_params']);
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_INVALID, $invalidParams));
            } else {
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_ERROR, $response['message']));
            }
            return $this->_setResponse();
        }
    }

    public function getInstallmentsAction()
    {
        /** @var CsuMarketSystem_OpteMais_Model_Order $order */
        $order = Mage::getModel('optemais/order');
        $response = $order->getInstallments($this->getRequest()->getParams());
        $this->getResponseApi()->setMessageKey('Mensagem');
        $this->getResponseApi()->setStatusKey('Status');
        if ($response['status']) {
            $info = $response['data'];
            $this->getResponseApi()->setStatusValue(true);
            $this->getResponseApi()->setMessageValue(null);
            return $this->_setResponse($info);
        } else {
            if (isset($response['not_found']) && $response['not_found']) {
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_NOT_FOUND, $response['message']));
            } elseif (isset($response['invalid_params'])) {
                $invalidParams = implode(",", $response['invalid_params']);
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_INVALID, $invalidParams));
            } else {
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_ERROR, $response['message']));
            }
            return $this->_setResponse();
        }
    }

}