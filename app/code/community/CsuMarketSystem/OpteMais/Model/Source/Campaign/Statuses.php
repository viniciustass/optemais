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
class CsuMarketSystem_OpteMais_Model_Source_Campaign_Statuses
{

    const STATE_ACTIVE = 1;
    const STATE_INACTIVE = 0;

    public function getOptions()
    {
        return array(
            self::STATE_ACTIVE   => Mage::helper('optemais')->__('Ativo'),
            self::STATE_INACTIVE => Mage::helper('optemais')->__('Inativo'),
        );
    }

}
