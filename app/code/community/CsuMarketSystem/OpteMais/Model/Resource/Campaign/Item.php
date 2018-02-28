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
class CsuMarketSystem_OpteMais_Model_Resource_Campaign_Item extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('optemais/campaign_item', 'entity_id');
    }

    public function getItemsByParams($params, $limit = null)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), '*');
        foreach ($params as $field => $value) {
            $select->where("$field = ?", $value);
        }
        if ($limit) {
            $select->limit($limit);
        }
        return $adapter->fetchAll($select);
    }
}