<?php
/**
 * @category   Mean3
 * @package    Mean3_Stallionshipping
 * @author     info@mean3.com
 * @website    http://www.Mean3.com
 */

namespace Mean3\Stallionshipping\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class InstallSchema implements InstallSchemaInterface
{

public function install (SchemaSetupInterface $setup, ModuleContextInterface $context)
{
    $installer = $setup;

    $installer->startSetup ();
/*
     * Sales Shipment Track
     */
    $result = $installer->getConnection()
        ->addColumn(
            $installer->getTable('sales_shipment_track'),
            'track_url_stallion',
            array(
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Stallion Tracking URL',
                'after' => 'title'
            )
        );
    /*
     * Charge Type Shipment
     */
    $result = $installer->getConnection()
        ->addColumn(
            $installer->getTable('sales_shipment_track'),
            'charge_type_stallion',
            array(
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Stallion book order Charge Type',
                'after' => 'track_number'
            )
        );

    $installer->endSetup();
}

}

