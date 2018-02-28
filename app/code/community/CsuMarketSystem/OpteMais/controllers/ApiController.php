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
class CsuMarketSystem_OpteMais_ApiController extends Mage_Core_Controller_Front_Action
{

    /** @var CsuMarketSystem_OpteMais_Model_Response */
    private $_responseModel = null;

    public function _construct()
    {
        parent::_construct();
        $this->_responseModel = Mage::getModel('optemais/response');
        $this->_responseModel->setStatusKey('status_code');
        $this->_responseModel->setMessageKey('message');
    }

    public function getResponseApi()
    {
        return $this->_responseModel;
    }

    public function preDispatch()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8', true);
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('auth_user'));
        if (!$auth->hasIdentity()) {
            $config = array(
                'accept_schemes' => 'basic',
                'realm'          => CsuMarketSystem_OpteMais_Helper_Data::AUTH_REALM,
                'nonce_timeout'  => 3600,
            );
            $adapter = new CsuMarketSystem_OpteMais_Model_Auth_Adapter_Http($config);
            $adapter->setRequest($this->getRequest());
            $adapter->setResponse($this->getResponse());
            $result = $auth->authenticate($adapter);
            if (!$result->isValid()) {
                $auth->clearIdentity();
                $this->getRequest()->setDispatched(true);
                $this->setFlag(
                    '',
                    Mage_Core_Controller_Front_Action::FLAG_NO_DISPATCH,
                    true
                );
                $this->_responseModel->setStatusValue(CsuMarketSystem_OpteMais_Helper_Data::STATUS_CODE_NOT_AUTHORIZED);
                $this->_responseModel->setMessageValue($this->__(CsuMarketSystem_OpteMais_Helper_Data::MESSAGE_NOT_AUTHORIZED));
                return $this->_setResponse();
            }
        }
        return true;
    }

    protected function _setResponse($responseData = array())
    {
        return $this->_responseModel->getResponseData($responseData, $this->getResponse());
    }

}