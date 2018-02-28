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
abstract class CsuMarketSystem_OpteMais_Model_Abstract extends Mage_Core_Model_Abstract
{

    protected function _beforeSave()
    {
        $this->setData('updated_at', Mage::getSingleton('core/date')->gmtDate());
        if (!$this->getId()) {
            $this->setData('created_at', Mage::getSingleton('core/date')->gmtDate());
        }
        return parent::_beforeSave();
    }

}