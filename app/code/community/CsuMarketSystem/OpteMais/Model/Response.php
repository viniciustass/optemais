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
class CsuMarketSystem_OpteMais_Model_Response
{

    protected $_status;
    protected $_statusKey;
    protected $_message;
    protected $_messageKey;

    public function setStatusKey($status)
    {
        $this->_statusKey = $status;
    }

    public function setStatusValue($status)
    {
        $this->_status = $status;
    }

    public function setMessageKey($message)
    {
        $this->_messageKey = $message;
    }

    public function setMessageValue($message)
    {
        $this->_message = $message;
    }

    public function getResponseData($responseData, $httpResponse)
    {
        $response = array(
            $this->_statusKey  => $this->_status,
            $this->_messageKey => $this->_message,
        );
        if ($responseData && is_array($responseData)) {
            $response += $responseData;
        }
        $httpResponse->setBody(json_encode($response));
        $statusCode = is_bool($this->_status) ? 200 : $this->_status;
        return $httpResponse->setHttpResponseCode($statusCode);
    }


}
