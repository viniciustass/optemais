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
class CsuMarketSystem_OpteMais_Model_Campaign_Item extends CsuMarketSystem_OpteMais_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('optemais/campaign_item');
    }

    protected function _beforeSave()
    {
        foreach ($this->getData() as $key => $value) {
            if (in_array($key, array('from_date', 'to_date')) && $value) {
                $value = Mage::app()->getLocale()->date(
                    $value,
                    Varien_Date::DATE_INTERNAL_FORMAT,
                    null,
                    false
                );
                $this->setData($key, $value);
            }
        }
        return parent::_beforeSave();
    }

    public function getItemByParams($params = array())
    {
        return $this->getResource()->getItemByParams($params);
    }

    public function getItemsByParams($params = array(), $limit = null)
    {
        return $this->getResource()->getItemsByParams($params, $limit);
    }

}