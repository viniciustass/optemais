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
class CsuMarketSystem_OpteMais_Model_Source_Shipping_Pricetype
{

    const LOWEST_PRICE = 'lowest_price';
    const BIGGEST_PRICE = 'biggest_price';

    public function toOptionArray()
    {
        return array(
            array('value' => self::LOWEST_PRICE, 'label' => Mage::helper('optemais')->__('Menor Preço')),
            array('value' => self::BIGGEST_PRICE, 'label' => Mage::helper('optemais')->__('Maior Preço')),
        );
    }

}
