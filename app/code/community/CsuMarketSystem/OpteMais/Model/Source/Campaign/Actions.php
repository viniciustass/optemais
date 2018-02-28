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
class CsuMarketSystem_OpteMais_Model_Source_Campaign_Actions
{

    const ACTION_NEW = 1;
    const ACTION_EXISTING = 2;

    public function getOptions()
    {
        return array(
            array('value' => self::ACTION_NEW, 'label' => Mage::helper('optemais')->__('Nova Campanha')),
            array('value' => self::ACTION_EXISTING, 'label' => Mage::helper('optemais')->__('Campanha Existente')),
        );
    }


}
