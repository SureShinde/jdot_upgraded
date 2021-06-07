<?php

namespace Aalogics\Lcs\Setup;


use Magento\Framework\Setup\UpgradeSchemaInterface;

use Magento\Framework\Setup\ModuleContextInterface;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements  UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup,

        ModuleContextInterface $context){

        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {

// Get module table

            $tableName = $setup->getTable('sales_shipment_track');

// Check if the table already exists

            if ($setup->getConnection()->isTableExists($tableName) == true) {

// Declare data

                $columns = [

                    'awb' => [

                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,

                        'nullable' => true,

                        'comment' => 'Lcs Shipment Label Print Tabel',

                    ],

                ];

                $connection = $setup->getConnection();

                foreach ($columns as $name => $definition) {

                    $connection->addColumn($tableName, $name, $definition);

                }
            }
        }

        $setup->endSetup();

    }

}