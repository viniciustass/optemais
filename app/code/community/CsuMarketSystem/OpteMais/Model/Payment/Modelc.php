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
class CsuMarketSystem_OpteMais_Model_Payment_Modelc extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'optemais_modelc';
    protected $_infoBlockType = 'optemais/payment_info_modelc';
    protected $_canUseCheckout = false;

    public function isAvailable($quote = null)
    {
        return Mage::registry('order_created_by_optemais');
    }
}