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
class CsuMarketSystem_OpteMais_Model_Source_Allattributes
{
    /**
     * Retrieves attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = Mage::getModel('catalog/product')->getResource()
            ->loadAllAttributes()
            ->getAttributesByCode();

        $result = array();
        $result[] = array(
            'value' => '',
            'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        );
        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            if ($attribute->getId()) {
                $result[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontend()->getLabel(),
                );
            }
        }
        return $result;
    }

}