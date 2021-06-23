<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\InstallSchemaInterface;

/**
 * Class InstallSchema
 * @package Magefan\OgTags\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $table = $setup->getConnection()->newTable(
            $setup->getTable('magefan_og_blog_category')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'category_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => false],
            'Category ID'
        )->addColumn(
            'magefan_og_title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false, 'primary' => false],
            'OG Title'
        )->addColumn(
            'magefan_og_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false, 'primary' => false],
            'OG Description'
        )->addColumn(
            'magefan_og_image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false, 'primary' => false],
            'OG Image'
        );

        $setup->getConnection()->createTable($table);
        $table = $setup->getConnection()->newTable(
            $setup->getTable('magefan_og_cms_page')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'page_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => false],
            'Page ID'
        )->addColumn(
            'magefan_og_title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false, 'primary' => false],
            'OG Title'
        )->addColumn(
            'magefan_og_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false, 'primary' => false],
            'OG Description'
        )->addColumn(
            'magefan_og_image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false, 'primary' => false],
            'OG Image'
        );

        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }
}
