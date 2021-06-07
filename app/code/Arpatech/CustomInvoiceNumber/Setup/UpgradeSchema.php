<?php

namespace Arpatech\CustomInvoiceNumber\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade schema
 * @category Arpatech
 * @package  Arpatech_CustomInvoiceNumber
 * @module   CustomInvoiceNumber
 * @author   amber
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

//        Add Custom Invoice Number field to sales_order table
            $tableName = $setup->getTable('sales_order');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();

                $connection->addColumn(
                    $tableName,
                    'custom_invoice_no',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'default' => '',
                        'comment' => 'Erp Custom Invoice Number'
                    ]
                );
            }

//        Add Custom Invoice Number field to sales_order_grid table
            $tableName = $setup->getTable('sales_order_grid');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();

                $connection->addColumn(
                    $tableName,
                    'custom_invoice_no',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'default' => '',
                        'comment' => 'Erp Custom Invoice Number'
                    ]
                );
            }
        }
        $setup->endSetup();
    }
}
