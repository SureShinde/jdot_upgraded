<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Storepickup
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 *
 */

namespace Magedelight\Storepickup\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup->startSetup();

            $tableName = $setup->getTable('magedelight_storelocator');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $columns = [
                    'region_id' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => false,
                        'comment' => 'Region Id',
                    ],
                ];

                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }
            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $setup->startSetup();

            if (!$setup->tableExists('magedelight_store_tag')) {
                $table = $setup->getConnection()
                    ->newTable($setup->getTable('magedelight_store_tag'));
                $table->addColumn(
                    'tag_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                            ],
                    'Tag ID'
                )
                    ->addColumn(
                        'tag_name',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => false,],
                        'Tag Name'
                    )
                    ->addColumn(
                        'tag_description',
                        Table::TYPE_TEXT,
                        '2M',
                        ['nullable' => false,],
                        'Tag Description'
                    )
                    ->addColumn(
                        'tag_icon',
                        Table::TYPE_TEXT,
                        '2M',
                        [],
                        'Tag Icon'
                    )
                    ->addColumn(
                        'store_ids',
                        Table::TYPE_TEXT,
                        '2M',
                        [],
                        'Store Ids'
                    )
                    ->addColumn(
                        'is_active',
                        Table::TYPE_INTEGER,
                        null,
                        [
                        'nullable' => false,
                        'default' => '1',
                            ],
                        'Is Active'
                    )->addIndex(
                        $setup->getIdxName('magedelight_store_tag', ['tag_id']),
                        ['tag_id']
                    )
                    ->setComment('Manage Tags for Store');
                $setup->getConnection()->createTable($table);
            }


            $tableName = $setup->getTable('magedelight_storelocator');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $columns = [
                    'conditions_serialized' => [
                    'type' => Table::TYPE_TEXT,
                    'size' => null,
                    'options' => ['nullable' => false, 'default' => null],
                    'comment' => 'Conditions for where to display',
                    ],
                    'product_ids' => [
                    'type' => Table::TYPE_TEXT,
                    'size' => 2048,
                    'options' => ['nullable' => false, 'default' => null],
                    'comment' => 'product ids for where to display',
                    ],
                ];

                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }

            $setup->endSetup();
        }
        
        // version 1.0.4
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $setup->startSetup();
            
            //Store locator store view table
            if ($setup->getConnection()->isTableExists('magedelight_storelocator') == true) {
                $this->addStorefields($setup);
            }
           
            //Holiday store view table
            if ($setup->getConnection()->isTableExists('magedelight_store_holiday') == true) {
                $this->addholidayfields($setup);
            }
            
            //Tag store view table
            if ($setup->getConnection()->isTableExists('magedelight_store_tag') == true) {
                 $this->addtagfields($setup);
            }
            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            $setup->startSetup();
            $setup->getConnection()->addColumn(
            $setup->getTable('magedelight_storelocator'),
            'storeemail',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'size' => 255,
                    'nullable' => false,
                    'default' => '',
                    'afters' => 'storename',
                    'comment' => 'Store Email'
                ]
            );
            $setup->endSetup();            
           
        }
    }
    
    protected function addStorefields(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('magedelight_storelocator'),
            'store_parent_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment' => 'Storelocator Parent ID'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('magedelight_storelocator'),
            'store_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Store ID'
            ]
        );
        $setup->getConnection()->addIndex(
            $setup->getTable('magedelight_storelocator'),
            $setup->getConnection()->getIndexName('magedelight_storelocator', ['store_id']),
            ['store_id']
        );
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'magedelight_storelocator',
                'store_parent_id',
                'magedelight_storelocator',
                'storelocator_id'
            ),
            $setup->getTable('magedelight_storelocator'),
            'store_parent_id',
            $setup->getTable('magedelight_storelocator'),
            'storelocator_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'magedelight_storelocator',
                'store_id',
                'store',
                'store_id'
            ),
            $setup->getTable('magedelight_storelocator'),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        return $this;
    }
    protected function addholidayfields(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('magedelight_store_holiday'),
            'holiday_parent_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment' => 'Holiday Parent ID'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('magedelight_store_holiday'),
            'store_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Store ID'
            ]
        );

        $setup->getConnection()->addIndex(
            $setup->getTable('magedelight_store_holiday'),
            $setup->getConnection()->getIndexName(
                $setup->getTable('magedelight_store_holiday'),
                'store_id',
                'index'
            ),
            'store_id'
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'magedelight_store_holiday',
                'holiday_parent_id',
                'magedelight_store_holiday',
                'holiday_id'
            ),
            $setup->getTable('magedelight_store_holiday'),
            'holiday_parent_id',
            $setup->getTable('magedelight_store_holiday'),
            'holiday_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'magedelight_store_holiday',
                'store_id',
                'store',
                'store_id'
            ),
            $setup->getTable('magedelight_store_holiday'),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        return $this;
    }
    
    protected function addtagfields(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('magedelight_store_tag'),
            'tag_parent_id',
            [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Tag Parent ID'
                    ]
        );
                $setup->getConnection()->addColumn(
                    $setup->getTable('magedelight_store_tag'),
                    'store_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'unsigned' => true,
                        'nullable' => false,
                        'comment' => 'Store ID'
                    ]
                );
                $setup->getConnection()->addIndex(
                    $setup->getTable('magedelight_store_tag'),
                    $setup->getConnection()->getIndexName('magedelight_store_tag', ['store_id']),
                    ['store_id']
                );
                $setup->getConnection()->addForeignKey(
                    $setup->getFkName(
                        'magedelight_store_tag',
                        'tag_parent_id',
                        'magedelight_store_tag',
                        'tag_id'
                    ),
                    $setup->getTable('magedelight_store_tag'),
                    'tag_parent_id',
                    $setup->getTable('magedelight_store_tag'),
                    'tag_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                );
                $setup->getConnection()->addForeignKey(
                    $setup->getFkName(
                        'magedelight_store_tag',
                        'store_id',
                        'store',
                        'store_id'
                    ),
                    $setup->getTable('magedelight_store_tag'),
                    'store_id',
                    $setup->getTable('store'),
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                );
                return $this;
    }
}
