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

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

if (!$installer->tableExists($installer->getTable('optemais/campaign'))) {
    $table = $installer->getConnection()->newTable(
        $installer->getTable('optemais/campaign')
    );
    $table
        ->addColumn(
            'entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array('unsigned' => true, 'nullable' => false, 'primary' => true, 'identity' => true),
            'Entity ID'
        )
        ->addColumn(
            'name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            array('nullable' => false),
            'Name'
        )
        ->addColumn(
            'description', Varien_Db_Ddl_Table::TYPE_TEXT, 64,
            array('nullable' => true),
            'Description'
        )
        ->addColumn(
            'freight_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '5,2', array(
                'nullable' => false,
                'default'  => '0.00',
            ), 'Freight Discount'
        )
        ->addColumn(
            'from_date', Varien_Db_Ddl_Table::TYPE_DATE, null,
            array(),
            'From Date'
        )
        ->addColumn(
            'to_date', Varien_Db_Ddl_Table::TYPE_DATE, null,
            array(),
            'To Date'
        )
        ->addColumn(
            'is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'nullable' => false,
                'default'  => '0',
            ), 'Is Active'
        )
        ->addColumn(
            'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
            array(),
            'Creation Time'
        )
        ->addColumn(
            'updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
            array(),
            'Update Time'
        );

    $installer->getConnection()->createTable($table);
}

if (!$installer->tableExists($installer->getTable('optemais/campaign_item'))) {
    $table = $installer->getConnection()->newTable(
        $installer->getTable('optemais/campaign_item')
    );
    $table
        ->addColumn(
            'entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array('unsigned' => true, 'nullable' => false, 'primary' => true, 'identity' => true),
            'Entity ID'
        )
        ->addColumn(
            'campaign_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array('unsigned' => true, 'nullable' => false),
            'Campaign ID'
        )
        ->addColumn(
            'product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array('unsigned' => true, 'nullable' => false),
            'Product ID'
        )
        ->addColumn(
            'price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                'nullable' => false,
                'default'  => '0.0000',
            ), 'Price'
        )
        ->addColumn(
            'stock_qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                'nullable' => false,
                'default'  => '0.0000',
            ), 'Stock Qty'
        )
        ->addColumn(
            'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
            array(),
            'Creation Time'
        )
        ->addColumn(
            'updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
            array(),
            'Update Time'
        )
        ->addIndex(
            $installer->getIdxName(
                'optemais/campaign_item',
                array('campaign_id', 'product_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('campaign_id', 'product_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $installer->getIdxName(
                'optemais/campaign_item',
                array('campaign_id')
            ),
            array('campaign_id')
        )
        ->addForeignKey(
            $installer->getFkName(
                'optemais/campaign_item',
                'campaign_id',
                'optemais/campaign',
                'entity_id'
            ),
            'campaign_id', $installer->getTable('optemais/campaign'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        );

    $installer->getConnection()->createTable($table);

}

$installer->endSetup();