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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (!$installer->tableExists('magedelight_storelocator')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('magedelight_storelocator'));
            $table->addColumn(
                'storelocator_id',
                Table::TYPE_INTEGER,
                null,
                [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                            ],
                'Stores ID'
            )
                    ->addColumn(
                        'storename',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => false,],
                        'Stores Name'
                    )
                    ->addColumn(
                        'url_key',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => false,],
                        'Stores Name'
                    )
                    ->addColumn(
                        'description',
                        Table::TYPE_TEXT,
                        '2M',
                        [],
                        'Store Description'
                    )
                    ->addColumn(
                        'website_url',
                        Table::TYPE_TEXT,
                        250,
                        [],
                        'Store Website Url'
                    )
                    ->addColumn(
                        'facebook_url',
                        Table::TYPE_TEXT,
                        250,
                        [],
                        'Store Facebook Url'
                    )
                    ->addColumn(
                        'twitter_url',
                        Table::TYPE_TEXT,
                        250,
                        [],
                        'Store Twitter Url'
                    )
                    ->addColumn(
                        'address',
                        Table::TYPE_TEXT,
                        250,
                        [],
                        'Address'
                    )
                    ->addColumn(
                        'city',
                        Table::TYPE_TEXT,
                        20,
                        [],
                        'Stores City'
                    )
                    ->addColumn(
                        'state',
                        Table::TYPE_TEXT,
                        20,
                        [],
                        'Stores State'
                    )
                    ->addColumn(
                        'country',
                        Table::TYPE_TEXT,
                        2,
                        [],
                        'Stores Country'
                    )
                    ->addColumn(
                        'zipcode',
                        Table::TYPE_TEXT,
                        250,
                        [],
                        'Zipcode'
                    )
                    ->addColumn(
                        'longitude',
                        Table::TYPE_TEXT,
                        20,
                        [],
                        'Longitude'
                    )
                    ->addColumn(
                        'latitude',
                        Table::TYPE_TEXT,
                        20,
                        [],
                        'Latitude'
                    )
                    ->addColumn(
                        'phone_frontend_status',
                        Table::TYPE_INTEGER,
                        250,
                        [],
                        'phone frontend status'
                    )
                    ->addColumn(
                        'telephone',
                        Table::TYPE_TEXT,
                        250,
                        [],
                        'Telephone'
                    )
                    ->addColumn(
                        'storeimage',
                        Table::TYPE_TEXT,
                        '2M',
                        [],
                        'Store Image'
                    )
                    ->addColumn(
                        'storetime',
                        Table::TYPE_TEXT,
                        '2M',
                        [],
                        'Time Schedule'
                    )
                    ->addColumn(
                        'meta_title',
                        Table::TYPE_TEXT,
                        250,
                        [],
                        'Meta Title'
                    )
                    ->addColumn(
                        'meta_keywords',
                        Table::TYPE_TEXT,
                        '2M',
                        [],
                        'Meta Keywords'
                    )
                    ->addColumn(
                        'meta_description',
                        Table::TYPE_TEXT,
                        '2M',
                        [],
                        'Meta Description'
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
                    )
                    ->setComment('magedelight_storelocator');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('magedelight_storelocator'),
                $setup->getIdxName(
                    $installer->getTable('magedelight_storelocator'),
                    ['storename'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                [
                'storename',
                'country'
                    ],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }

        if (!$installer->tableExists('magedelight_store_holiday')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('magedelight_store_holiday'));
            $table->addColumn(
                'holiday_id',
                Table::TYPE_INTEGER,
                null,
                [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                            ],
                'Holiday ID'
            )
                    ->addColumn(
                        'holiday_name',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => false,],
                        'Holiday Name'
                    )
                    ->addColumn(
                        'holiday_applied_stores',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => false,],
                        'Holiday Applied Stores'
                    )->addColumn(
                        'holiday_date_from',
                        Table::TYPE_DATE,
                        null,
                        [],
                        'From Date'
                    )->addColumn(
                        'holiday_date_to',
                        Table::TYPE_DATE,
                        null,
                        [],
                        'TO Date'
                    )->addColumn(
                        'holiday_comment',
                        Table::TYPE_TEXT,
                        '2M',
                        ['nullable' => false,],
                        'Holiday Comment'
                    )->addColumn(
                        'is_repetitive',
                        Table::TYPE_INTEGER,
                        1,
                        ['nullable' => false,],
                        'Yearly Repetative'
                    )->addColumn(
                        'is_active',
                        Table::TYPE_INTEGER,
                        null,
                        [
                        'nullable' => false,
                        'default' => '1',
                        ],
                        'Is Active'
                    )
                    ->setComment('Store Holidays');
            $installer->getConnection()->createTable($table);
            $installer->getConnection()->addIndex(
                $installer->getTable('magedelight_store_holiday'),
                $setup->getIdxName($installer->getTable('magedelight_store_holiday'), ['holiday_id']),
                ['holiday_id']
            );
        }

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'pickup_store',
            [
            'type' => 'text',
            'nullable' => false,
            'comment' => 'Pickup Store Name',
                ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'pickup_date',
            [
            'type' => 'datetime',
            'nullable' => true,
            'comment' => 'Pickup Date',
                ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'pickup_store',
            [
            'type' => 'text',
            'nullable' => false,
            'comment' => 'Pickup Store Name',
                ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'pickup_date',
            [
            'type' => 'datetime',
            'nullable' => true,
            'comment' => 'Pickup Date',
                ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'pickup_store',
            [
            'type' => 'text',
            'nullable' => false,
            'comment' => 'Pickup Store Name',
                ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'pickup_date',
            [
            'type' => 'datetime',
            'nullable' => true,
            'comment' => 'Pickup Date',
                ]
        );
    }
}
