<?php
namespace Aalogics\Lcs\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Sales\Model\Order\Status;
use \Aalogics\Lcs\Mode\Api\Lcs\Api\Request;
use \Aalogics\Lcs\Helper\Data;

class InstallData implements InstallDataInterface
{
	protected $orderStatus;
	/**
	 * 
	 * @var \Aalogics\Lcs\Mode\Api\Lcs\Api\Request
	 */
	protected $_apiRequest;
	
	protected $_helper;

	/**
	 * @param ConfigBasedIntegrationManager $integrationManager
	 */

	public function __construct(
			Status $orderStatus,
			\Aalogics\Lcs\Model\Api\Lcs\Api\Request $apiRequest,
			\Aalogics\Lcs\Helper\Data $helper
)
	{
		$this->orderStatus = $orderStatus;  
		$this->_apiRequest = $apiRequest;
		$this->_helper = $helper;
	}

	/**
	 * {@inheritdoc}
	 */

	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();
		$setup->endSetup();
	}
}