<?php
/**
 * @category   Mean3
 * @package    Mean3_Stallionshipping
 * @author     info@mean3.com
 * @website    http://www.Mean3.com
 */

namespace Mean3\Stallionshipping\Setup;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;
use Magento\Eav\Setup\EavSetupFactory;
//use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @codeCoverageIgnore
 */

class UpgradeData implements UpgradeDataInterface
{
    
    const ORDER_STATUS_CUSTOM_READY_CODE = 'booked_stallion';

    const ORDER_STATUS_CUSTOM_READY_LABEL = 'Booked On Stallion';

    const ORDER_STATE_CUSTOM_READY_CODE = 'processing';

    /**
     * Status Factory
     *
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * Status Resource Factory
     *
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

    /**
     *
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        \Magento\Sales\Model\Order\StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     *
     * @throws Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->createNewOrderStatuses();
    }


    /**
     * Create new custom order status and states
     *
     * @return void
     *
     * @throws Exception
     */
    protected function createNewOrderStatuses()
    {
        /**
         *  Created statuses data
         */

        $status = $this->statusFactory->create();

        $status->setStatus('booked_stallion')->delete();
        
        $statusesData = [
            [
                'status' => self::ORDER_STATUS_CUSTOM_READY_CODE,
                'label' => self::ORDER_STATUS_CUSTOM_READY_LABEL,
                'state' => self::ORDER_STATE_CUSTOM_READY_CODE
            ]
        ];

        foreach ($statusesData as $wkstatus) { 
  
            if ($wkstatus['status'] != "") {  
   
                /** @var StatusResource $statusResource */
                $statusResource = $this->statusResourceFactory->create();
                /** @var Status $status */
                $status = $this->statusFactory->create();
                $status->setData([
                    'status' => $wkstatus['status'],
                    'label' => $wkstatus['label']
                ]);

                try {
                    $statusResource->save($status);
                    $status->assignState($wkstatus['state'], true, true);
                } catch (AlreadyExistsException $exception) {
                    // log some thing here...
                } catch(Exception $e) {
                    // log some thing here...
                }
            }
        }
    }
}