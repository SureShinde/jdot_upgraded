<?php
/**
 * @category   Mean3
 * @package    Mean3_Stallionshipping
 * @author     info@mean3.com
 * @website    http://www.Mean3.com
 */

namespace Mean3\Stallionshipping\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Db\Select;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface as UninstallInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class Uninstall
 */
class Uninstall implements UninstallInterface
{
    
    
    private $_mDSetup;
    
    public function __construct(
        ModuleDataSetupInterface $mDSetup
    )
    {
        $this->_mDSetup = $mDSetup;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        
        $status = $this->statusFactory->create();
        $status->setStatus('booked_stallion')->delete();

        $installer->endSetup();
    }
}