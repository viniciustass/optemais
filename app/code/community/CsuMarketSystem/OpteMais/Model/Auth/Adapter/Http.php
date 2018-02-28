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
class CsuMarketSystem_OpteMais_Model_Auth_Adapter_Http extends Zend_Auth_Adapter_Http
{

    protected function _basicAuth($header)
    {
        if (empty($header)) {
            throw new Zend_Auth_Adapter_Exception('The value of the client Authorization header is required');
        }
        $auth = substr($header, strlen('Basic '));
        $auth = base64_decode($auth);
        if (!$auth) {
            throw new Zend_Auth_Adapter_Exception('Unable to base64_decode Authorization header value');
        }
        if (!ctype_print($auth)) {
            return $this->_challengeClient();
        }
        $creds = array_filter(explode(':', $auth));
        if (count($creds) != 2) {
            return $this->_challengeClient();
        }
        $username = Mage::getStoreConfig('optemais/config/username');
        $password = Mage::getStoreConfig('optemais/config/password');
        if (($creds[0] == $username) && ($creds[1] == $password)) {
            $result = new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, '', array(Mage::helper('optemais')->__('Autorizado.')));
        } else {
            $result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, '', array(Mage::helper('optemais')->__('Falha na autenticacao.')));
            $this->_challengeClient();
        }
        return $result;
    }

}