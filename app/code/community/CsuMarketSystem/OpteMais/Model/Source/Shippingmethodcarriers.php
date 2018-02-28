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
class CsuMarketSystem_OpteMais_Model_Source_Shippingmethodcarriers
{

    public function toOptionArray()
    {
        $carriers = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $shippingMethods = array(array('value'=>'all','label'=>''));
        foreach ($carriers as $carriersCode => $carriersModel) {
            if (!$carriersModel->getAllowedMethods()) {
                continue;
            }
            $carrierTitle = Mage::getStoreConfig('carriers/'.$carriersCode.'/title');
            $shippingMethods[] = array
            (
                'value' => $carriersCode,
                'label' => $carrierTitle,
            );
        }
        return $shippingMethods;
    }

}