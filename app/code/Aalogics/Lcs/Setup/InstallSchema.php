<?php

/**
* Copyright 2018 AALOGICS. All rights reserved.
* See LICENSE.txt for license details.
*/
namespace Aalogics\Lcs\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * 
 * @package Aalogics\Lcs\Setup
 */
class InstallSchema implements InstallSchemaInterface {
	protected $status;
	public function __construct(\Magento\Sales\Model\Order\Status $status) {
		$this->status = $status;
	}
	
	/**
	 *
	 * @ERROR!!! @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @throws \Zend_Db_Exception
	 */
	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
		$installer = $setup;
		$installer->startSetup ();
		/**
		 * Create table 'catalog_product_entity'
		 */
		if($installer->getConnection()->isTableExists($installer->getTable('pakistan_cities_lcs')) != true) {
			
			$table = $installer->getConnection()
			->newTable($installer->getTable('pakistan_cities_lcs'))
			->addColumn(
					'city_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
					'City ID'
			)
			->addColumn(
					'code',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					32,
					['nullable' => false, 'default' => 'simple'],
					'Code'
			)
			->addColumn(
					'area',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					64,
					[],
					'Area'
			)
			->addColumn(
					'default_name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					64,
					[],
					'Name'
			)->setComment('Pakistan Cities Table');
											
			$installer->getConnection()->createTable($table);
		}
		$installer->endSetup ();
	}
}
