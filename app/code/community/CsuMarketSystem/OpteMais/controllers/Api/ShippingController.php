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

class CsuMarketSystem_OpteMais_Api_ShippingController extends CsuMarketSystem_OpteMais_ApiController
{

    public function getRatesAction()
    {
        /** @var CsuMarketSystem_OpteMais_Model_Shipping $shipping */
        $shipping = Mage::getModel('optemais/shipping');
        $response = $shipping->getShippingRates($this->getRequest()->getParams());
        $this->getResponseApi()->setMessageKey('Mensagem');
        $this->getResponseApi()->setStatusKey('Status');
        if ($response['status']) {
            $rates = $response['data'];
            $this->getResponseApi()->setStatusValue(true);
            $this->getResponseApi()->setMessageValue(null);
            return $this->_setResponse($rates);
        } else {
            if (isset($response['invalid_params'])) {
                $invalidParams = implode(",", $response['invalid_params']);
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_INVALID, $invalidParams));
                return $this->_setResponse();
            } else {
                $this->getResponseApi()->setStatusValue(false);
                $this->getResponseApi()->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_ERROR, $response['message']));
                return $this->_setResponse();
            }
        }
    }

}