<?php

namespace CustomerParadigm\OrderComments\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // Get module table
        $tableName = $setup->getTable('sales_order_status_history');

        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($tableName) == true)
        {
            // Declare data
            $columns = [
            'order_from' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'nullable' => false,
            'comment' => 'Order from either customer or admin',
            ],
            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition)
            {
                $connection->addColumn($tableName, $name, $definition);
            }
        }

        $setup->endSetup();
    }
}