<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Dhllabel\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.7.8', '<')) {
            $this->addCustomerName($setup);
        }

        if (version_compare($context->getVersion(), '1.7.9', '<')) {
            $this->createAddressTable($setup);
            $this->createBoxesTable($setup);
        }

        if (version_compare($context->getVersion(), '1.8.0', '<')) {
            $this->addShipperId($setup);
        }

        if (version_compare($context->getVersion(), '1.8.2', '<')) {
            $this->addInvoiceType($setup);
        }

        $setup->endSetup();
    }

    public function addShipperId(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('dhllabel_address');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn(
                $tableName,
                'shipper_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'shipper_id'
                ]
            );
        }
    }

    public function addInvoiceType(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('dhllabel');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn(
                $tableName,
                'invoice_type',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 20,
                    'nullable' => false,
                    'default' => 'CMI',
                    'comment' => 'invoice_type'
                ]
            );
        }
    }

    public function createAddressTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('dhllabel_address');
        if ($setup->getConnection()->isTableExists($tableName) == false) {
            /**
             * Create table 'dhllabel_address'
             */
            $table = $setup->getConnection()->newTable(
                $tableName
            )->addColumn(
                'address_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Box ID'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'name'
            )->addColumn(
                'company',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'company'
            )->addColumn(
                'attention',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'attention'
            )->addColumn(
                'phone',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'phone'
            )->addColumn(
                'street_one',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'street_one'
            )->addColumn(
                'street_two',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'street_two'
            )->addColumn(
                'room',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'room'
            )->addColumn(
                'floor',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'floor'
            )->addColumn(
                'city',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'city'
            )->addColumn(
                'province_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'province_code'
            )->addColumn(
                'urbanization',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'urbanization'
            )->addColumn(
                'postal_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'postal_code'
            )->addColumn(
                'country',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'country'
            )->addColumn(
                'residential',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'residential'
            )->setComment(
                'DHL Addresses'
            );
            $setup->getConnection()->createTable($table);
        }
    }

    public function createBoxesTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('dhllabel_box');
        if ($setup->getConnection()->isTableExists($tableName) == false) {
            /**
             * Create table 'dhllabel_box'
             */
            $table = $setup->getConnection()->newTable(
                $tableName
            )->addColumn(
                'box_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Box ID'
            )->addColumn(
                'enable',
                Table::TYPE_INTEGER,
                2,
                ['nullable' => false, 'default' => 1],
                'enable'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'name'
            )->addColumn(
                'width',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'width'
            )->addColumn(
                'outer_width',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'outer_width'
            )->addColumn(
                'height',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'height'
            )->addColumn(
                'outer_height',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'outer_height'
            )->addColumn(
                'lengths',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'lengths'
            )->addColumn(
                'outer_lengths',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'outer_lengths'
            )->addColumn(
                'max_weight',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'max_weight'
            )->addColumn(
                'empty_weight',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'empty_weight'
            )->setComment(
                'DHL Boxes'
            );
            $setup->getConnection()->createTable($table);
        }
    }

    protected function addCustomerName(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('dhllabel');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'customer_name',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '255',
                    'default' => '',
                    'comment' => 'customer name'
                ]
            );
        }
    }
}
