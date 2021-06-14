<?php

namespace RLTSquare\SMS\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package RLTSquare\SMS\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    const SALES_ORDER_TABLE = 'sales_order';
    const COLUMN_NAME = 'ecs_phone_number_status';
    const PHONE_CODE = 'phone_code';
    const PHONE_CODE_COUNTER = 'phone_code_counter';

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install( //@codingStandardsIgnoreLine
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        /** @var SchemaSetupInterface $installer */
        $installer = $setup;

        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable(self::SALES_ORDER_TABLE),
            self::COLUMN_NAME,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => true,
                'comment' => 'Column for Phone Number Status'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable(self::SALES_ORDER_TABLE),
            self::PHONE_CODE,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => true,
                'comment' => 'Phone Number Code'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable(self::SALES_ORDER_TABLE),
            self::PHONE_CODE_COUNTER,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => true,
                'comment' => 'Phone Number Code Counter'
            ]
        );

        $installer->endSetup();
    }
}
