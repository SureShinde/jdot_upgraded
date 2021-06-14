<?php

namespace RLTSquare\SMS\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package RLTSquare\SMS\Model\ResourceModel\Order\Grid
 */
class Collection extends OriginalCollection
{
    /**
     * Collection constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order_grid',
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Show phone number status value in order gird at admin panel.
     */
    protected function _renderFiltersBefore()
    {
        try {
            $joinTable = $this->getTable('sales_order');
            $this->getSelect()->joinLeft(
                $joinTable,
                'main_table.entity_id = sales_order.entity_id',
                ['ecs_phone_number_status']
            );
            parent::_renderFiltersBefore();
        } catch (\Exception $exception) {
        }
    }
}
